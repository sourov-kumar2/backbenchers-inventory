<?php
/**
 * api/pos_ai_helper.php
 * Converts natural language POS commands into structured JSON actions
 * using the Groq API with live product & customer context from the database.
 */

header('Content-Type: application/json');

require '../auth.php';
require '../config.php';

// ── Guard ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['command'])) {
    echo json_encode(['success' => false, 'error' => 'No command provided.']);
    exit();
}

$commandText = trim($_POST['command']);

if (strlen($commandText) > 500) {
    echo json_encode(['success' => false, 'error' => 'Command too long.']);
    exit();
}

// ── Build context from database ────────────────────────────
try {
    $stmt = $pdo->query("SELECT id, item_name AS name, price, quantity AS stock FROM products ORDER BY item_name ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id, name, phone FROM customers ORDER BY name ASC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('POS AI Helper DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error fetching context.']);
    exit();
}

// ── System prompt ──────────────────────────────────────────
$systemPrompt = <<<PROMPT
You are a POS Command Parser for a retail terminal. Convert natural language into a JSON array of actions.

AVAILABLE ACTIONS (return ONLY these, no other fields):
- { "action": "add_item", "id": <product_id_int>, "qty": <quantity_int> }
- { "action": "set_customer", "id": "<customer_id_string>" }
- { "action": "set_discount", "percent": <number> }
- { "action": "set_tax", "percent": <number> }

AVAILABLE PRODUCTS:
PRODUCT_LIST

AVAILABLE CUSTOMERS:
CUSTOMER_LIST

RULES:
1. Return ONLY a valid JSON array — no prose, no markdown, no code fences.
2. Match product and customer names case-insensitively; pick the closest match.
3. If quantity is not mentioned, assume 1.
4. Multiple actions can be in the same array (e.g. add item AND set customer AND set discount).
5. If nothing is understood, return an empty array: []
PROMPT;

$systemPrompt = str_replace('PRODUCT_LIST', json_encode($products, JSON_PRETTY_PRINT), $systemPrompt);
$systemPrompt = str_replace('CUSTOMER_LIST', json_encode($customers, JSON_PRETTY_PRINT), $systemPrompt);

// ── Call Groq API ──────────────────────────────────────────
$payload = json_encode([
    'model'    => defined('GROQ_MODEL') ? GROQ_MODEL : 'llama3-8b-8192',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $commandText],
    ],
    'temperature' => 0.05,
    'max_tokens'  => 512,
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT    => 15,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ── cURL / network error ───────────────────────────────────
if ($curlError) {
    error_log('POS AI Helper cURL error: ' . $curlError);
    echo json_encode(['success' => false, 'error' => 'Could not reach AI service.']);
    exit();
}

// ── HTTP error from Groq ───────────────────────────────────
if ($httpCode !== 200) {
    error_log('POS AI Helper Groq HTTP error: ' . $httpCode . ' | ' . $response);
    echo json_encode(['success' => false, 'error' => "AI service error (HTTP $httpCode). Check your API key or quota."]);
    exit();
}

// ── Parse Groq response ────────────────────────────────────
$result = json_decode($response, true);
$raw    = $result['choices'][0]['message']['content'] ?? '[]';

// Strip any accidental markdown code fences
$cleaned = preg_replace('/```(?:json)?|```/i', '', $raw);
$actions = json_decode(trim($cleaned), true);

// ── Validate actions array ─────────────────────────────────
if (!is_array($actions)) {
    error_log('POS AI Helper: invalid actions output: ' . $raw);
    echo json_encode(['success' => false, 'error' => 'AI returned an unexpected format. Try rephrasing your command.']);
    exit();
}

// Sanitize each action to only allow known fields & types
$validActions = [];
foreach ($actions as $action) {
    if (!isset($action['action'])) continue;

    switch ($action['action']) {
        case 'add_item':
            if (isset($action['id']) && isset($action['qty'])) {
                $validActions[] = [
                    'action' => 'add_item',
                    'id'     => (int)$action['id'],
                    'qty'    => max(1, (int)$action['qty']),
                ];
            }
            break;
        case 'set_customer':
            if (isset($action['id'])) {
                $validActions[] = ['action' => 'set_customer', 'id' => (string)$action['id']];
            }
            break;
        case 'set_discount':
            if (isset($action['percent'])) {
                $validActions[] = ['action' => 'set_discount', 'percent' => min(100, max(0, (float)$action['percent']))];
            }
            break;
        case 'set_tax':
            if (isset($action['percent'])) {
                $validActions[] = ['action' => 'set_tax', 'percent' => min(100, max(0, (float)$action['percent']))];
            }
            break;
    }
}

echo json_encode(['success' => true, 'actions' => $validActions]);