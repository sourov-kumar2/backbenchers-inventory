<?php
require 'auth.php';
require 'config.php';

// Intelligence Engine & Statistics
try {
    // 1. Core KPIs
    $stmt = $pdo->query('SELECT COUNT(*) FROM products');
    $total_items = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM customers');
    $total_customers = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT SUM(total_amount) FROM sales');
    $total_revenue = $stmt->fetchColumn() ?: 0;

    // 2. Intelligence: Sales Velocity & Inventory Forecasting (Last 14 days baseline)
    $stmt = $pdo->query('SELECT 
                            p.id, 
                            p.item_name, 
                            p.quantity as current_stock,
                            p.image,
                            IFNULL(SUM(si.quantity), 0) as total_sold,
                            IFNULL(SUM(si.quantity) / 14, 0) as daily_velocity
                         FROM products p
                         LEFT JOIN sale_items si ON p.id = si.product_id
                         LEFT JOIN sales s ON si.sale_id = s.id AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                         GROUP BY p.id');
    $inventory_intelligence = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter and Process Forecasting
    $forecasting = [];
    $restock_pulse = [];
    foreach ($inventory_intelligence as $item) {
        $runway = ($item['daily_velocity'] > 0) ? floor($item['current_stock'] / $item['daily_velocity']) : 999;
        
        $item['runway'] = $runway;
        
        // Actionable Restock Pulse (Sold at least something and runway < 7 days OR stock is 0 and it has velocity)
        if ($item['daily_velocity'] > 0 && ($runway < 7 || $item['current_stock'] <= 0)) {
            $restock_pulse[] = $item;
        }
        
        if ($item['daily_velocity'] > 0) {
            $forecasting[] = $item;
        }
    }
    
    // Sort pulse by urgency (runway ascending)
    usort($restock_pulse, fn($a, $b) => $a['runway'] <=> $b['runway']);
    // Sort forecasting by velocity to show top items
    usort($forecasting, fn($a, $b) => $b['daily_velocity'] <=> $a['daily_velocity']);

    // 3. Leaderboard Intel
    // Star Product
    $stmt = $pdo->query('SELECT p.item_name, SUM(si.quantity) as sold 
                         FROM sale_items si 
                         JOIN products p ON si.product_id = p.id 
                         GROUP BY p.id 
                         ORDER BY sold DESC LIMIT 1');
    $star_product = $stmt->fetch();

    // VIP Client
    $stmt = $pdo->query('SELECT c.name, SUM(s.total_amount) as spent 
                         FROM sales s 
                         JOIN customers c ON s.customer_id = c.id 
                         GROUP BY c.id 
                         ORDER BY spent DESC LIMIT 1');
    $vip_client = $stmt->fetch();

    // 4. Sales Trend (Last 7 Days)
    $stmt = $pdo->query('SELECT DATE(sale_date) as date, SUM(total_amount) as amount 
                         FROM sales 
                         WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                         GROUP BY DATE(sale_date)');
    $raw_trend = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $trend_labels = [];
    $trend_values = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $trend_labels[] = date('M d', strtotime($date));
        $trend_values[] = isset($raw_trend[$date]) ? (float)$raw_trend[$date] : 0;
    }

    // 5. Recent Sales
    $stmt = $pdo->query('SELECT s.*, c.name as customer_name 
                         FROM sales s 
                         LEFT JOIN customers c ON s.customer_id = c.id 
                         ORDER BY s.sale_date DESC 
                         LIMIT 5');
    $recent_sales = $stmt->fetchAll();

} catch (PDOException $e) {
    $total_items = 0; $total_customers = 0; $total_revenue = 0;
    $forecasting = []; $restock_pulse = []; $recent_sales = [];
    $star_product = null; $vip_client = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Predictive Dashboard';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Intelligence Briefing</h1>
                    <p class="text-muted">Predictive stock depletion monitoring and business velocity analytics.</p>
                </div>
                <div class="header-actions">
                    <a href="pos.php" class="btn btn-primary">Terminal Interface</a>
                </div>
            </header>


            <!-- Enhanced KPI Row -->
            <div class="stats-grid animate-fade-in" style="animation-delay: 0.2s">
                <div class="kpi-card glass">
                    <div class="kpi-label">Revenue Engine</div>
                    <div class="kpi-value">৳<?= number_format($total_revenue, 2) ?></div>
                </div>
                <div class="kpi-card glass">
                    <div class="kpi-label">Active Database</div>
                    <div class="kpi-value"><?= number_format($total_customers) ?> Clients</div>
                </div>
                <div class="kpi-card glass">
                    <div class="kpi-label">Asset Worth</div>
                    <div class="kpi-value"><?= number_format($total_items) ?> SKUs</div>
                </div>
            </div>

            <!-- Forecasting Grid -->
            <div class="dashboard-secondary animate-fade-in" style="animation-delay: 0.3s">
                <div class="table-card glass">
                    <div class="card-header-flex">
                        <h2 class="section-title">Inventory Forecasting</h2>
                        <span class="badge">14-Day Velocity Baseline</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Velocity</th>
                                    <th>Estimated Runway</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($forecasting)): ?>
                                    <tr><td colspan="5" class="empty-row">Predictive data will appear as sales occur.</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($forecasting, 0, 6) as $f): 
                                        $rClass = ($f['runway'] < 3) ? 'danger-text' : (($f['runway'] < 7) ? 'warning-text' : 'success-text');
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="p-cell">
                                                    <?php if ($f['image']): ?>
                                                        <img src="<?= htmlspecialchars($f['image']) ?>" alt="" class="p-mini-thumb">
                                                    <?php endif; ?>
                                                    <strong><?= htmlspecialchars($f['item_name']) ?></strong>
                                                </div>
                                            </td>
                                            <td><?= $f['current_stock'] ?> Units</td>
                                            <td><?= round($f['daily_velocity'], 2) ?>/day</td>
                                            <td class="<?= $rClass ?> font-bold">
                                                <?= $f['runway'] >= 999 ? 'Sustainable' : $f['runway'] . ' Days' ?>
                                            </td>
                                            <td>
                                                <?php if ($f['runway'] < 5): ?>
                                                    <span class="pulse-tag red">URGENT</span>
                                                <?php else: ?>
                                                    <span class="pulse-tag green">HEALTHY</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; }
    .header-main { font-size: 2.2rem; font-weight: 800; tracking: -0.02em; }

    .intel-hero-grid { display: grid; grid-template-columns: 3fr 2fr; gap: 1.5rem; margin-bottom: 2rem; }
    .intel-card { padding: 2rem; border-radius: 28px; display: flex; flex-direction: column; }
    .pulse-bg { background: radial-gradient(circle at top right, rgba(139, 92, 246, 0.1), transparent); }
    .perf-bg { background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.05), transparent); }
    
    .intel-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; }
    .pulse-dot { width: 10px; height: 10px; background: #ef4444; border-radius: 50%; box-shadow: 0 0 10px #ef4444; animation: blink 1.5s infinite; }
    @keyframes blink { 0% { opacity: 0.3; transform: scale(0.9); } 50% { opacity: 1; transform: scale(1.1); } 100% { opacity: 0.3; transform: scale(0.9); } }
    
    .pulse-list { display: flex; flex-direction: column; gap: 1rem; }
    .pulse-item { border-left: 3px solid rgba(239, 68, 68, 0.5); padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.02); display: flex; justify-content: space-between; align-items: center; border-radius: 0 12px 12px 0; }
    .p-warning { display: block; font-size: 0.75rem; color: #ef4444; font-weight: 700; margin-top: 0.25rem; }
    .restock-btn { padding: 0.4rem 0.8rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; font-size: 0.75rem; border-radius: 6px; text-decoration: none; font-weight: 700; transition: 0.2s; }
    .restock-btn:hover { background: #ef4444; color: white; }

    .leader-stats { display: flex; flex-direction: column; gap: 1.5rem; }
    .leader-item { border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; }
    .l-label { display: block; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; margin-bottom: 0.5rem; }
    .l-val { font-size: 1.1rem; font-weight: 800; color: #f59e0b; }

    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .kpi-card { padding: 1.5rem; border-radius: 20px; text-align: center; }
    .kpi-label { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; margin-bottom: 0.5rem; }
    .kpi-value { font-size: 1.5rem; font-weight: 800; color: var(--accent-primary); }

    .card-header-flex { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); }
    .badge { padding: 0.3rem 0.6rem; background: rgba(255, 255, 255, 0.05); border-radius: 6px; font-size: 0.7rem; color: var(--text-dim); font-weight: 700; }
    .p-cell { display: flex; align-items: center; gap: 0.75rem; }
    .p-mini-thumb { width: 32px; height: 32px; border-radius: 6px; object-fit: cover; }
    
    .danger-text { color: #f87171; }
    .warning-text { color: #fbbf24; }
    .success-text { color: #34d399; }
    .pulse-tag { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.65rem; font-weight: 800; }
    .pulse-tag.red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .pulse-tag.green { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    @media (max-width: 1000px) { .intel-hero-grid { grid-template-columns: 1fr; } .stats-grid { grid-template-columns: 1fr; } }
    </style>
</body>
</html>