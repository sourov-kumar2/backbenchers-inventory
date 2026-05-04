<?php
require '../auth.php';
require '../config.php';

$pageTitle = 'AI Business Intelligence';
$base_url = '../';

// AJAX Request Handler
if (isset($_GET['action']) && $_GET['action'] === 'analyze') {
    header('Content-Type: application/json');

    try {
        // 1. Fetch Business Context Data
        
        // Stock Health
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total_items,
            SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN quantity > 0 AND quantity < 10 THEN 1 ELSE 0 END) as low_stock,
            SUM(quantity * price) as total_valuation
            FROM products");
        $inventory = $stmt->fetch();

        // Sales Metrics
        $stmt = $pdo->query("SELECT 
            COUNT(*) as sales_count,
            SUM(total_amount) as total_revenue,
            SUM(amount_due) as total_receivables,
            SUM(discount_amount) as total_discounts
            FROM sales");
        $sales = $stmt->fetch();

        // Top Products
        $stmt = $pdo->query("SELECT p.item_name, SUM(si.quantity) as sold_qty
                             FROM products p
                             JOIN sale_items si ON p.id = si.product_id
                             GROUP BY p.id
                             ORDER BY sold_qty DESC
                             LIMIT 5");
        $top_products = $stmt->fetchAll();

        // Top Customers
        $stmt = $pdo->query("SELECT name, total_due 
                             FROM customers 
                             ORDER BY total_due DESC 
                             LIMIT 5");
        $risky_customers = $stmt->fetchAll();

        // Construct Data Payload
        $business_data = [
            'inventory' => $inventory,
            'sales' => $sales,
            'top_performing_products' => $top_products,
            'customers_with_highest_debt' => $risky_customers,
            'currency' => 'BDT (৳)',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // 2. Call Groq API
        $prompt = "You are a Senior Strategic Business Analyst. Analyze this raw data from my Inventory & Sales system and provide a high-impact strategic report. 
        Data: " . json_encode($business_data) . "
        
        Requirements:
        - Output ONLY a single <div> container with the class 'ai-report-wrapper'.
        - DO NOT include markdown code blocks (like ```html). Just return the raw HTML.
        - Use these specific CSS classes for styling:
            - 'ai-stat-grid' for the container of metric cards.
            - 'ai-stat-card' for individual metric boxes.
            - 'ai-badge-emerald', 'ai-badge-rose', 'ai-badge-violet' for status tags.
            - 'ai-insight-box' for recommendation paragraphs.
            - 'ai-table-premium' for tabular data.
        - Structure:
            1. 'Performance Snapshot': Use 4 cards showing key KPIs.
            2. 'Critical Observations': A section with badges showing Risks vs Opportunities.
            3. 'Actionable Intelligence': 3 high-level strategic recommendations with icons (represented by SVG or simple emoji).
        - Use professional, analytical language.";

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => GROQ_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a Senior Business Analyst specialized in ERP systems.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000
        ]));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception("Groq API Error: Status Code " . $http_code);
        }

        $result = json_decode($response, true);
        $analysis_html = $result['choices'][0]['message']['content'] ?? 'Analysis failed to generate.';

        echo json_encode(['success' => true, 'report' => $analysis_html]);
        exit();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include '../partials/head.php'; ?>
<body>
    <div class="app-container">
        <?php include '../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../partials/navbar.php'; ?>
            
            <div class="reports-container animate-fade-in">
                <div class="view-header">
                    <div class="header-content">
                        <h1 class="view-title">AI Strategic Analyst</h1>
                        <p class="view-subtitle">Generative Intelligence engine processing real-time business nodes</p>
                    </div>
                    <div class="header-actions">
                        <button id="exportPDF" class="btn btn-outline" style="display: none; margin-right: 10px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            Export PDF
                        </button>
                        <button id="runAnalysis" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                            </svg>
                            Generate New Insight
                        </button>
                    </div>
                </div>

                <!-- Analysis Display Area -->
                <div id="analysisOutput" class="analysis-viewport glass">
                    <div class="initial-state">
                        <div class="ai-icon-pulse">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="1.5">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <h2>Intelligence Engine Standby</h2>
                        <p>Click "Generate New Insight" to trigger the neural analysis of your inventory, sales, and customer behavior patterns.</p>
                    </div>
                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .reports-container { padding-bottom: 4rem; }
    .view-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
    .view-title { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem; }
    .view-subtitle { color: var(--text-dim); font-size: 0.9rem; }

    .analysis-viewport { min-height: 600px; border-radius: 28px; padding: 2.5rem; border: 1px solid var(--border-color); position: relative; overflow: hidden; }
    
    .initial-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 500px; text-align: center; gap: 1.5rem; }
    .initial-state h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
    .initial-state p { max-width: 500px; color: var(--text-dim); line-height: 1.6; }
    
    .ai-icon-pulse { animation: pulse-ai 2s infinite ease-in-out; }
    @keyframes pulse-ai { 
        0% { transform: scale(1); opacity: 0.5; filter: drop-shadow(0 0 0 rgba(139, 92, 246, 0)); }
        50% { transform: scale(1.1); opacity: 1; filter: drop-shadow(0 0 30px rgba(139, 92, 246, 0.4)); }
        100% { transform: scale(1); opacity: 0.5; filter: drop-shadow(0 0 0 rgba(139, 92, 246, 0)); }
    }

    /* AI Generated Content Styling - Premium Overrides */
    .ai-report-wrapper { animation: slideUp 0.6s ease-out; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    .ai-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
    .ai-stat-card { background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.1); padding: 1.5rem; border-radius: 20px; text-align: center; }
    .ai-stat-card h4 { font-size: 0.75rem; text-transform: uppercase; color: var(--text-dim); margin-bottom: 0.5rem; letter-spacing: 0.05em; }
    .ai-stat-card .val { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }

    .ai-section-head { font-size: 1.1rem; font-weight: 800; color: var(--accent-primary); margin: 2.5rem 0 1.5rem; display: flex; align-items: center; gap: 10px; }
    .ai-section-head::before { content: ''; width: 4px; height: 20px; background: var(--accent-primary); border-radius: 10px; }

    .ai-table-premium { width: 100%; border-collapse: separate; border-spacing: 0 8px; margin: 1rem 0; }
    .ai-table-premium th { padding: 1rem; text-align: left; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; }
    .ai-table-premium td { padding: 1.25rem 1rem; background: rgba(255, 255, 255, 0.02); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
    .ai-table-premium tr td:first-child { border-left: 1px solid var(--border-color); border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    .ai-table-premium tr td:last-child { border-right: 1px solid var(--border-color); border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

    .ai-insight-box { background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(217, 70, 239, 0.05) 100%); border-left: 4px solid var(--accent-primary); padding: 1.5rem; border-radius: 0 16px 16px 0; margin-bottom: 1.5rem; }
    .ai-insight-box strong { color: var(--text-primary); display: block; margin-bottom: 0.4rem; }
    .ai-insight-box p { font-size: 0.95rem; line-height: 1.6; color: var(--text-secondary); margin: 0; }

    .ai-badge-emerald { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
    .ai-badge-rose { background: rgba(244, 63, 94, 0.1); color: #f43f5e; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
    .ai-badge-violet { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }

    @media print {
        .sidebar, .navbar, .header-actions, .ai-icon-pulse { display: none !important; }
        .main-content { left: 0 !important; width: 100% !important; padding: 0 !important; }
        .glass { background: white !important; color: black !important; border: 1px solid #eee !important; box-shadow: none !important; }
        .ai-stat-card { border: 1px solid #eee !important; background: #f9fafb !important; color: black !important; }
        .ai-stat-card .val, .ai-section-head, .ai-report-title { color: black !important; }
        .ai-table-premium td { background: white !important; color: black !important; border: 1px solid #eee !important; }
        .ai-insight-box { background: #f3f4f6 !important; border-left-color: #000 !important; color: black !important; }
    }

    @media (max-width: 768px) { .view-header { flex-direction: column; gap: 1.5rem; text-align: center; } .header-actions { width: 100%; display: flex; flex-direction: column; gap: 10px; } .btn { width: 100%; justify-content: center; } }
    </style>

    <script>
    document.getElementById('runAnalysis').addEventListener('click', function() {
        const btn = this;
        const output = document.getElementById('analysisOutput');
        
        // UI Loading State
        btn.disabled = true;
        btn.innerHTML = `<span class="loader-ring" style="width: 18px; height: 18px; border-width: 2px; margin-right: 10px"></span> Generating...`;
        
        output.innerHTML = `
            <div class="loading-state">
                <div class="loader-ring"></div>
                <div class="loading-text">
                    <h3 class="animate-pulse">Analyzing System Nodes...</h3>
                    <p class="text-dim">Consulting LLAMA-3 for strategic growth patterns</p>
                </div>
            </div>
        `;

        fetch('ai_analyst.php?action=analyze')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clean up markdown markers if AI adds them
                    let cleanReport = data.report.replace(/```html|```/g, '').trim();
                    output.innerHTML = `<div class="ai-report-container animate-fade-in">${cleanReport}</div>`;
                    document.getElementById('exportPDF').style.display = 'inline-flex';
                } else {
                    output.innerHTML = `
                        <div class="initial-state text-rose">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <h2>Intelligence Breach</h2>
                            <p>${data.error}</p>
                            <button onclick="location.reload()" class="btn btn-outline btn-sm">Reset System</button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                output.innerHTML = `<div class="initial-state text-rose"><h2>Network Outage</h2><p>${error.message}</p></div>`;
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg> Generate New Insight`;
            });
    });

    document.getElementById('exportPDF').addEventListener('click', function() {
        window.print();
    });
    </script>
</body>
</html>
