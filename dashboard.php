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

    $stmt = $pdo->query('SELECT SUM(amount_due) FROM sales');
    $total_receivables = $stmt->fetchColumn() ?: 0;

    // 2. Inventory Distribution for Chart
    $stmt = $pdo->query("SELECT 
        SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock,
        SUM(CASE WHEN quantity > 0 AND quantity < 10 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN quantity >= 10 THEN 1 ELSE 0 END) as healthy
        FROM products");
    $stock_status = $stmt->fetch();

    // 3. Sales Trend (Last 7 Days)
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

    // 4. Intelligence: Restock Pulse
    $stmt = $pdo->query('SELECT 
                            p.id, 
                            p.item_name, 
                            p.quantity as current_stock,
                            IFNULL(SUM(si.quantity) / 14, 0) as daily_velocity
                         FROM products p
                         LEFT JOIN sale_items si ON p.id = si.product_id
                         LEFT JOIN sales s ON si.sale_id = s.id AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                         GROUP BY p.id
                         HAVING daily_velocity > 0 AND (current_stock / daily_velocity < 7 OR current_stock <= 0)
                         ORDER BY (current_stock / daily_velocity) ASC
                         LIMIT 4');
    $restock_pulse = $stmt->fetchAll();

    // 5. Recent Activity
    $stmt = $pdo->query('SELECT s.*, c.name as customer_name 
                         FROM sales s 
                         LEFT JOIN customers c ON s.customer_id = c.id 
                         ORDER BY s.sale_date DESC 
                         LIMIT 5');
    $recent_sales = $stmt->fetchAll();

} catch (PDOException $e) {
    $total_items = 0; $total_customers = 0; $total_revenue = 0; $total_receivables = 0;
    $stock_status = ['out_of_stock' => 0, 'low_stock' => 0, 'healthy' => 0];
    $trend_labels = []; $trend_values = []; $restock_pulse = []; $recent_sales = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Terminal Intelligence';
include 'partials/head.php'; 
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <!-- Global Metrics -->
            <div class="metrics-grid animate-fade-in">
                <div class="metric-card glass">
                    <div class="metric-info">
                        <span class="m-label">Total Revenue</span>
                        <h2 class="m-value">৳<?= number_format($total_revenue, 0) ?></h2>
                    </div>
                    <div class="metric-icon rev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                </div>
                <div class="metric-card glass">
                    <div class="metric-info">
                        <span class="m-label">Receivables</span>
                        <h2 class="m-value" style="color: #f87171;">৳<?= number_format($total_receivables, 0) ?></h2>
                    </div>
                    <div class="metric-icon due"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div>
                </div>
                <div class="metric-card glass">
                    <div class="metric-info">
                        <span class="m-label">Fleet Size</span>
                        <h2 class="m-value"><?= number_format($total_items) ?> Items</h2>
                    </div>
                    <div class="metric-icon box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg></div>
                </div>
                <div class="metric-card glass">
                    <div class="metric-info">
                        <span class="m-label">User Base</span>
                        <h2 class="m-value"><?= number_format($total_customers) ?> Subs</h2>
                    </div>
                    <div class="metric-icon usr"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
                </div>
            </div>

            <!-- Analytics Hub -->
            <div class="analytics-grid animate-fade-in" style="animation-delay: 0.1s">
                <div class="chart-container glass">
                    <div class="chart-header">
                        <h3 class="chart-title">Revenue Trajectory</h3>
                        <span class="chart-subtitle">Rolling 7-Day Performance</span>
                    </div>
                    <canvas id="revenueChart"></canvas>
                </div>

                <div class="secondary-intel">
                    <div class="chart-container glass">
                        <h3 class="chart-title">Inventory Health</h3>
                        <canvas id="stockChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            <div class="bottom-grid animate-fade-in" style="animation-delay: 0.2s">
                <div class="table-card glass">
                    <div class="card-header">
                        <h3 class="chart-title">Live Transactions</h3>
                        <a href="sales_list.php" class="view-all">Full Log</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Method</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_sales as $rs): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rs['customer_name'] ?: 'Retail') ?></td>
                                    <td><?= $rs['payment_method'] ?></td>
                                    <td class="font-bold">৳<?= number_format($rs['total_amount'], 2) ?></td>
                                    <td>
                                        <?php if($rs['amount_due'] > 0): ?>
                                            <span class="st-tag due">DUE</span>
                                        <?php else: ?>
                                            <span class="st-tag paid">PAID</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .metric-card { padding: 1.5rem; border-radius: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); }
    .m-label { display: block; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; margin-bottom: 0.5rem; }
    .m-value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin: 0; }
    .metric-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--bg-card); color: var(--text-dim); }
    .metric-icon svg { width: 24px; height: 24px; }
    .metric-icon.rev { color: var(--success); background: rgba(16, 185, 129, 0.1); }
    .metric-icon.due { color: var(--danger); background: rgba(239, 68, 68, 0.1); }

    .analytics-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem; align-items: stretch; }
    .chart-container { padding: 1.75rem; border-radius: 28px; border: 1px solid var(--border-color); height: 450px; display: flex; flex-direction: column; background: var(--bg-card); }
    .chart-container canvas { flex: 1; min-height: 0; }
    .chart-header { margin-bottom: 1.5rem; }
    .chart-title { font-size: 1rem; font-weight: 700; margin: 0 0 0.25rem 0; color: var(--text-primary); }
    .chart-subtitle { font-size: 0.75rem; color: var(--text-dim); }

    .secondary-intel { display: flex; flex-direction: column; gap: 1.5rem; height: 450px; }
    .quick-intel { padding: 1.5rem; border-radius: 24px; border: 1px solid var(--border-color); flex: 1; background: var(--bg-card); }
    .pulse-list { margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
    .pulse-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); }
    .p-name { display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.2rem; }
    .p-stock { font-size: 0.7rem; color: var(--danger); font-weight: 600; }
    .pulse-btn { padding: 0.35rem 0.75rem; border-radius: 6px; background: var(--accent-glow); color: var(--accent-primary); font-size: 0.7rem; font-weight: 700; text-decoration: none; border: 1px solid var(--border-color); }
    .pulse-btn:hover { background: var(--accent-primary); color: white; }

    .bottom-grid { margin-bottom: 2rem; }
    .view-all { font-size: 0.75rem; color: var(--accent-primary); text-decoration: none; font-weight: 600; }
    .card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); }
    
    .st-tag { font-size: 0.65rem; font-weight: 800; padding: 0.25rem 0.5rem; border-radius: 6px; }
    .st-tag.due { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .st-tag.paid { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    .empty-msg { font-size: 0.8rem; color: var(--text-dim); text-align: center; margin-top: 1rem; }

    @media (max-width: 1200px) { .metrics-grid { grid-template-columns: 1fr 1fr; } .analytics-grid { grid-template-columns: 1fr; } }
    @media (max-width: 768px) { .metrics-grid { grid-template-columns: 1fr; } }
    </style>

    <script>
    const getThemeColors = () => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            grid: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
            text: isDark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)',
            accent: isDark ? '#7c6fe0' : '#6366f1'
        };
    };

    let revenueChart, stockChart;

    function initCharts() {
        const colors = getThemeColors();
        
        if(revenueChart) revenueChart.destroy();
        if(stockChart) stockChart.destroy();

        // 1. Revenue Pulse Chart
        revenueChart = new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($trend_labels) ?>,
                datasets: [{
                    label: 'Revenue (৳)',
                    data: <?= json_encode($trend_values) ?>,
                    borderColor: colors.accent,
                    backgroundColor: colors.accent + '1A', // 10% alpha
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: colors.accent
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { color: colors.text, font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { color: colors.text, font: { size: 10 } } }
                }
            }
        });

        // 2. Stock Health Chart
        stockChart = new Chart(document.getElementById('stockChart'), {
            type: 'doughnut',
            data: {
                labels: ['Healthy', 'Low', 'Out'],
                datasets: [{
                    data: [<?= $stock_status['healthy'] ?>, <?= $stock_status['low_stock'] ?>, <?= $stock_status['out_of_stock'] ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: colors.text, font: { size: 10 }, usePointStyle: true, padding: 15 } }
                }
            }
        });
    }

    initCharts();

    // Listen for theme changes to refresh charts
    window.addEventListener('themeChanged', initCharts);
    </script>
</body>
</html>