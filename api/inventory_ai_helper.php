<?php
/**
 * api/inventory_ai_helper.php
 *
 * Generates product descriptions, tags, and a suggested price from a product name.
 * Uses Groq API with an extremely strict prompt to guarantee clean JSON output.
 * Multi-layer parsing handles every known malformed-response pattern.
 */

header('Content-Type: application/json');

require '../auth.php';
require '../config.php';

// ── Guard ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty(trim($_POST['product_name'] ?? ''))) {
    echo json_encode(['success' => false, 'error' => 'Product name is required.']);
    exit();
}

$productName = trim($_POST['product_name']);
if (strlen($productName) > 250) {
    echo json_encode(['success' => false, 'error' => 'Product name is too long.']);
    exit();
}

// ── System prompt: maximum strictness ─────────────────────────────────────────
//
// Key techniques to prevent malformed output:
//  1. Single explicit output format with no alternatives
//  2. Hard-bans on prose, markdown, and code fences
//  3. Newline escaping rule stated twice (common failure point)
//  4. Example showing exact expected format
//  5. Temperature set to 0.05 (near-deterministic)
//  6. response_format forced to JSON via Groq parameter
//
$systemPrompt = <<<'PROMPT'
You are a JSON-only API endpoint. You NEVER output text outside a JSON object.

YOUR ONLY OUTPUT MUST BE A SINGLE JSON OBJECT MATCHING THIS EXACT SCHEMA:
{
  "description": "<string: 100-180 words, professional product copy. NO line breaks — all text on one line. Use semicolons to separate feature points instead of newlines or bullet symbols.>",
  "suggested_price": <number: integer or float, selling price in BDT (Bangladeshi Taka). No currency symbol, no quotes — raw number only.>,
  "tags": "<string: 5-10 relevant lowercase tags separated by commas>"
}

ABSOLUTE RULES (violating any rule makes your output invalid):
1. Output ONLY the JSON object — no preamble, no explanation, no markdown, no code fences.
2. The description value must be a single continuous string — NO \n, NO line breaks, NO bullet characters (•, -, *).
3. suggested_price must be a raw number (e.g. 45000) — never a string like "45000" or "৳45,000".
4. All JSON string values must use properly escaped double-quotes if quotes appear inside.
5. The JSON must be parseable by PHP's json_decode() with zero post-processing.

EXAMPLE (for a product named "Gaming Mouse"):
{"description":"The Gaming Mouse is a high-precision optical pointing device engineered for competitive gaming; it features a 16000 DPI adjustable sensor for pixel-accurate tracking; an ergonomic contoured shell reduces wrist fatigue during extended sessions; 7 programmable buttons with onboard memory store custom macros; a durable braided cable and gold-plated USB connector ensure signal stability; compatible with all major operating systems; RGB lighting with 16.8 million colors adds aesthetic customization; polling rate up to 1000Hz delivers near-zero input lag; ideal for FPS, MOBA, and RTS genres.","suggested_price":3500,"tags":"gaming, mouse, optical, rgb, ergonomic, peripheral, computer accessories, high dpi, programmable"}

Generate for the product the user names. Output JSON only.
PROMPT;

// ── Build request payload ──────────────────────────────────────────────────────
$payload = json_encode([
    'model'       => defined('GROQ_MODEL') ? GROQ_MODEL : 'llama3-8b-8192',
    'messages'    => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => 'Product: ' . $productName],
    ],
    'temperature' => 0.05,   // near-deterministic
    'max_tokens'  => 800,
    // Force JSON mode on Groq (supported on llama3 and mixtral models)
    'response_format' => ['type' => 'json_object'],
]);

// ── Call Groq ──────────────────────────────────────────────────────────────────
$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
    ],
    CURLOPT_POSTFIELDS => $payload,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr   = curl_error($ch);
curl_close($ch);

// ── Network / HTTP errors ──────────────────────────────────────────────────────
if ($curlErr) {
    error_log("[inventory_ai_helper] cURL error: $curlErr");
    echo json_encode(['success' => false, 'error' => 'Could not reach AI service. Check server connectivity.']);
    exit();
}

if ($httpCode !== 200) {
    $errBody = json_decode($response, true);
    $groqMsg = $errBody['error']['message'] ?? "HTTP $httpCode";
    error_log("[inventory_ai_helper] Groq HTTP $httpCode: $response");
    echo json_encode(['success' => false, 'error' => "AI service error: $groqMsg"]);
    exit();
}

// ── Extract raw AI text ────────────────────────────────────────────────────────
$groqResult = json_decode($response, true);
$rawContent = trim($groqResult['choices'][0]['message']['content'] ?? '');

if ($rawContent === '') {
    error_log("[inventory_ai_helper] Empty content for '$productName'");
    echo json_encode(['success' => false, 'error' => 'AI returned an empty response.']);
    exit();
}

// ── Multi-layer JSON extraction ────────────────────────────────────────────────
//
// Layer 1: Direct parse — works when model follows instructions perfectly.
// Layer 2: Strip markdown fences (```json ... ``` or ``` ... ```).
// Layer 3: Extract first {...} block — handles prose before/after JSON.
// Layer 4: Aggressive control-character scrub + re-parse.
// Layer 5: Regex-based field extraction — last resort for heavily malformed output.
//
$suggestion = null;

// Layer 1 — direct parse
$suggestion = json_decode($rawContent, true);

// Layer 2 — strip code fences
if (!is_array($suggestion)) {
    $stripped = preg_replace('/^```(?:json)?\s*/i', '', $rawContent);
    $stripped = preg_replace('/\s*```\s*$/i', '', $stripped);
    $suggestion = json_decode(trim($stripped), true);
}

// Layer 3 — extract first { ... } block
if (!is_array($suggestion)) {
    $start = strpos($rawContent, '{');
    $end   = strrpos($rawContent, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $jsonSlice  = substr($rawContent, $start, $end - $start + 1);
        $suggestion = json_decode($jsonSlice, true);
    }
}

// Layer 4 — strip control characters and retry
if (!is_array($suggestion)) {
    // Remove all control chars except tab (\x09)
    $scrubbed   = preg_replace('/[\x00-\x08\x0A-\x1F\x7F]/', '', $rawContent);
    $scrubbed   = preg_replace('/[\x80-\x9F]/', '', $scrubbed); // invalid win-1252 range
    $suggestion = json_decode($scrubbed, true);

    // Also try extracting {...} from scrubbed
    if (!is_array($suggestion)) {
        $start = strpos($scrubbed, '{');
        $end   = strrpos($scrubbed, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $suggestion = json_decode(substr($scrubbed, $start, $end - $start + 1), true);
        }
    }
}

// Layer 5 — regex field extraction (last resort)
if (!is_array($suggestion)) {
    $fallback = [];

    // Extract "description": "..."
    if (preg_match('/"description"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $rawContent, $m)) {
        $fallback['description'] = stripcslashes($m[1]);
    }

    // Extract "suggested_price": number
    if (preg_match('/"suggested_price"\s*:\s*"?(\d+(?:\.\d+)?)"?/', $rawContent, $m)) {
        $fallback['suggested_price'] = (float)$m[1];
    }

    // Extract "tags": "..."
    if (preg_match('/"tags"\s*:\s*"([^"]+)"/', $rawContent, $m)) {
        $fallback['tags'] = $m[1];
    }

    if (!empty($fallback['description']) && !empty($fallback['tags'])) {
        $suggestion = $fallback;
    }
}

// ── Validate extracted data ────────────────────────────────────────────────────
if (!is_array($suggestion)) {
    error_log("[inventory_ai_helper] All parsing layers failed for '$productName'. Raw:\n$rawContent");
    echo json_encode([
        'success'   => false,
        'error'     => 'AI returned unrecognizable output. Please try again or rephrase the product name.',
        'debug_raw' => $rawContent,
    ]);
    exit();
}

// ── Sanitize & normalize fields ────────────────────────────────────────────────
$description = isset($suggestion['description']) ? (string)$suggestion['description'] : '';
$price       = isset($suggestion['suggested_price']) ? (float)$suggestion['suggested_price'] : 0.0;
$tags        = isset($suggestion['tags']) ? (string)$suggestion['tags'] : '';

// Remove literal \n sequences the model sometimes emits (not actual newlines)
$description = str_replace(['\\n', '\n'], ' ', $description);
// Collapse multiple spaces
$description = preg_replace('/\s{2,}/', ' ', $description);
// Remove bullet-like characters the model may inject
$description = preg_replace('/\s*[•\-\*→✓]\s+/', '; ', $description);
$description = trim($description);

// Normalize tags: lowercase, deduplicate, max 12
$tagArray = array_unique(array_filter(array_map('trim', explode(',', strtolower($tags)))));
$tagArray = array_slice($tagArray, 0, 12);
$tags     = implode(', ', $tagArray);

// Price sanity: must be a positive number
if ($price <= 0) {
    // Try to extract from description as absolute fallback (rare)
    $price = 0;
}

// ── Return clean result ────────────────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'suggestion' => [
        'description'     => $description,
        'suggested_price' => $price,
        'tags'            => $tags,
    ],
]);