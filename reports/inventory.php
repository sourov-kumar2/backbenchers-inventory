<?php
require '../auth.php';
require '../config.php';

$pageTitle = 'Inventory Valuation Report';
$base_url = '../';

// Inventory Valuation Data
$stmt = $pdo->query("SELECT SUM(quantity * price) as total_valuation, SUM(quantity) as total_stock, COUNT(id) as total_products FROM products");
$summary = $stmt->fetch();

$stmt = $pdo->query("SELECT * FROM products ORDER BY (quantity * price) DESC");
$products = $stmt->fetchAll();

$out_of_stock = 0;
$low_stock = 0;
foreach($products as $p) {
    if($p['quantity'] <= 0) $out_of_stock++;
    elseif($p['quantity'] < 10) $low_stock++;
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
                        <h1 class="view-title">Asset Valuation & Health</h1>
                        <p class="view-subtitle">Monitor capital distribution and stock replenishment requirements</p>
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

                <div class="metrics-grid">
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Net Asset Value</span>
                            <h2 class="m-value">৳<?= number_format($summary['total_valuation'], 2) ?></h2>
                        </div>
                        <div class="metric-icon rev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">On-Hand Stock</span>
                            <h2 class="m-value"><?= number_format($summary['total_stock']) ?> Units</h2>
                        </div>
                        <div class="metric-icon box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Critical Alerts</span>
                            <h2 class="m-value" style="color: #f87171;"><?= $out_of_stock ?> Out</h2>
                        </div>
                        <div class="metric-icon due"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Low Stock</span>
                            <h2 class="m-value" style="color: #f59e0b;"><?= $low_stock ?> Items</h2>
                        </div>
                        <div class="metric-icon profit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></div>
                    </div>
                </div>

                <div class="table-card glass">
                    <div class="card-header">
                        <h3 class="chart-title">Asset Inventory Ledger</h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product Identity</th>
                                    <th>Current Stock</th>
                                    <th>Unit Valuation</th>
                                    <th>Total Asset Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $p): 
                                    $val = $p['quantity'] * $p['price'];
                                    $statusClass = $p['quantity'] <= 0 ? 'due' : ($p['quantity'] < 10 ? 'low' : 'paid');
                                    $statusText = $p['quantity'] <= 0 ? 'OUT' : ($p['quantity'] < 10 ? 'LOW' : 'HEALTHY');
                                ?>
                                <tr>
                                    <td class="font-bold"><?= htmlspecialchars($p['item_name']) ?></td>
                                    <td><?= number_format($p['quantity']) ?> Units</td>
                                    <td>৳<?= number_format($p['price'], 2) ?></td>
                                    <td class="font-bold text-premium">৳<?= number_format($val, 2) ?></td>
                                    <td>
                                        <span class="st-tag <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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

    .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .metric-card { padding: 1.5rem; border-radius: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); }
    .m-label { display: block; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; margin-bottom: 0.5rem; }
    .m-value { font-size: 1.4rem; font-weight: 800; color: var(--text-primary); margin: 0; }
    .metric-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); color: var(--text-dim); }

    .table-card { border-radius: 24px; overflow: hidden; border: 1px solid var(--border-color); }
    .card-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02); }
    .chart-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0; }
    
    table { width: 100%; border-collapse: collapse; }
    th { padding: 1.25rem 2rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border-color); letter-spacing: 0.1em; }
    td { padding: 1.25rem 2rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; color: var(--text-secondary); }
    
    .st-tag { font-size: 0.65rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 6px; }
    .st-tag.due { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .st-tag.low { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .st-tag.paid { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    .text-premium { color: var(--accent-primary); }
    .font-bold { font-weight: 700; }

    @media print {
        .sidebar, .navbar, .header-actions { display: none !important; }
        .main-content { left: 0 !important; width: 100% !important; padding: 0 !important; }
        .glass { background: white !important; border: 1px solid #eee !important; color: black !important; }
    }
    </style>
</body>
</html>
