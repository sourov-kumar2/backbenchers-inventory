<?php
require '../auth.php';
require '../config.php';

$pageTitle = 'Profit & Loss Analysis';
$base_url = '../';

// Date filters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Profit/Loss Data
$stmt = $pdo->prepare("SELECT SUM(total_amount) as revenue, SUM(discount_amount) as total_discount, SUM(tax_amount) as total_tax 
                       FROM sales 
                       WHERE DATE(sale_date) BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();

$revenue = $summary['revenue'] ?: 0;
$discounts = $summary['total_discount'] ?: 0;
$tax = $summary['total_tax'] ?: 0;

// COGS Estimation (Using the 15% markup rule seen in purchase.php)
$cogs = $revenue / 1.15;
$gross_profit = $revenue - $cogs;
$net_profit = $gross_profit - $discounts; // Simple logic: revenue includes tax, so net is after discounts.

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
                        <h1 class="view-title">Financial Performance (P&L)</h1>
                        <p class="view-subtitle">Profitability analysis based on system markup and transaction volume</p>
                    </div>
                    <div class="header-actions">
                        <button onclick="window.print()" class="btn btn-outline">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            Export PDF
                        </button>
                    </div>
                </div>

                <div class="report-filters glass">
                    <form method="GET" class="filter-form">
                        <div class="filter-group">
                            <label>Analysis Start</label>
                            <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
                        </div>
                        <div class="filter-group">
                            <label>Analysis End</label>
                            <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Analyze Period</button>
                    </form>
                </div>

                <div class="metrics-grid">
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Gross Revenue</span>
                            <h2 class="m-value">৳<?= number_format($revenue, 2) ?></h2>
                        </div>
                        <div class="metric-icon rev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Est. COGS (Cost)</span>
                            <h2 class="m-value" style="color: #94a3b8;">৳<?= number_format($cogs, 2) ?></h2>
                        </div>
                        <div class="metric-icon box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Gross Profit</span>
                            <h2 class="m-value" style="color: #10b981;">৳<?= number_format($gross_profit, 2) ?></h2>
                        </div>
                        <div class="metric-icon profit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Net Operating Result</span>
                            <h2 class="m-value" style="color: #8b5cf6;">৳<?= number_format($net_profit, 2) ?></h2>
                        </div>
                        <div class="metric-icon rev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M16 12l-4-4-4 4m4 4V8"></path></svg></div>
                    </div>
                </div>

                <div class="profit-breakdown glass animate-fade-in">
                    <h3 class="breakdown-title">Intelligence Breakdown</h3>
                    <div class="breakdown-table">
                        <div class="breakdown-row">
                            <span>(+) Total Sales Revenue</span>
                            <span class="text-green">৳<?= number_format($revenue, 2) ?></span>
                        </div>
                        <div class="breakdown-row">
                            <span>(-) Cost of Goods Sold (Based on 15% Markup)</span>
                            <span class="text-red">৳<?= number_format($cogs, 2) ?></span>
                        </div>
                        <div class="breakdown-divider"></div>
                        <div class="breakdown-row font-bold">
                            <span>(=) Gross Profit Margin</span>
                            <span class="text-premium">৳<?= number_format($gross_profit, 2) ?></span>
                        </div>
                        <div class="breakdown-row">
                            <span>(-) Customer Discounts Granted</span>
                            <span class="text-red">৳<?= number_format($discounts, 2) ?></span>
                        </div>
                        <div class="breakdown-row">
                            <span>(+) Collected Sales Tax</span>
                            <span class="text-green">৳<?= number_format($tax, 2) ?></span>
                        </div>
                        <div class="breakdown-divider"></div>
                        <div class="breakdown-row total-row">
                            <span>Final Intelligence Result</span>
                            <span class="m-value">৳<?= number_format($net_profit + $tax, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .reports-container { padding-bottom: 4rem; }
    .view-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .view-title { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem; }
    .view-subtitle { color: var(--text-dim); font-size: 0.9rem; }

    .report-filters { padding: 1.5rem; border-radius: 20px; margin-bottom: 2rem; border: 1px solid var(--border-color); }
    .filter-form { display: flex; gap: 1.5rem; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
    .filter-group label { font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; }
    .filter-form .btn { height: 50px; padding: 0 2rem; }

    .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .metric-card { padding: 1.5rem; border-radius: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); }
    .m-label { display: block; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; margin-bottom: 0.5rem; }
    .m-value { font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0; }
    .metric-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); color: var(--text-dim); }

    .profit-breakdown { padding: 2.5rem; border-radius: 28px; border: 1px solid var(--border-color); max-width: 800px; margin: 0 auto; }
    .breakdown-title { font-size: 1.2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 2rem; text-align: center; }
    .breakdown-row { display: flex; justify-content: space-between; padding: 1rem 0; font-size: 1rem; color: var(--text-secondary); }
    .breakdown-divider { height: 1px; background: var(--border-color); margin: 0.5rem 0; }
    .total-row { margin-top: 1.5rem; padding: 1.5rem; background: rgba(139, 92, 246, 0.05); border-radius: 16px; border: 1px solid rgba(139, 92, 246, 0.1); align-items: center; }
    .total-row span:first-child { font-weight: 800; color: var(--text-primary); font-size: 1.1rem; }

    .text-red { color: #f87171; }
    .text-green { color: #10b981; }
    .text-premium { color: var(--accent-primary); font-weight: 800; }
    .font-bold { font-weight: 700; }

    @media print {
        .sidebar, .navbar, .report-filters, .header-actions { display: none !important; }
        .main-content { left: 0 !important; width: 100% !important; padding: 0 !important; }
        .glass { background: white !important; border: 1px solid #eee !important; color: black !important; }
        .profit-breakdown { border: 1px solid #eee !important; max-width: 100% !important; }
    }
    </style>
</body>
</html>
