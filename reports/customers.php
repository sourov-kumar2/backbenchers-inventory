<?php
require '../auth.php';
require '../config.php';

$pageTitle = 'Customer Intelligence Report';
$base_url = '../';

// Date filters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Customer Performance
$stmt = $pdo->prepare("SELECT c.*, COUNT(s.id) as total_orders, SUM(s.total_amount) as total_spent, SUM(s.amount_due) as current_due
                       FROM customers c
                       LEFT JOIN sales s ON c.id = s.customer_id
                       WHERE DATE(s.sale_date) BETWEEN ? AND ? OR s.id IS NULL
                       GROUP BY c.id
                       ORDER BY total_spent DESC");
$stmt->execute([$start_date, $end_date]);
$customers = $stmt->fetchAll();

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
                        <h1 class="view-title">Customer Behavioral Intelligence</h1>
                        <p class="view-subtitle">Analyze acquisition value and collection health per client</p>
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
                        <button type="submit" class="btn btn-primary">Refresh Intelligence</button>
                    </form>
                </div>

                <div class="table-card glass">
                    <div class="card-header">
                        <h3 class="chart-title">Customer Performance Matrix</h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer Details</th>
                                    <th>Total Orders</th>
                                    <th>Acquisition Value</th>
                                    <th>Total Dues</th>
                                    <th>Health Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($customers as $c): 
                                    $spent = $c['total_spent'] ?: 0;
                                    $due = $c['current_due'] ?: 0;
                                    $orders = $c['total_orders'] ?: 0;
                                    $statusClass = $due > 5000 ? 'due' : ($due > 0 ? 'low' : 'paid');
                                    $statusText = $due > 5000 ? 'RISKY' : ($due > 0 ? 'PENDING' : 'CLEAR');
                                ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <span class="font-bold"><?= htmlspecialchars($c['name']) ?></span>
                                            <span class="text-dim" style="font-size: 0.75rem;"><?= htmlspecialchars($c['phone'] ?: 'No Phone') ?></span>
                                        </div>
                                    </td>
                                    <td><?= $orders ?> Transactions</td>
                                    <td class="font-bold">৳<?= number_format($spent, 2) ?></td>
                                    <td class="<?= $due > 0 ? 'text-red' : 'text-green' ?> font-bold">৳<?= number_format($due, 2) ?></td>
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

    .report-filters { padding: 1.5rem; border-radius: 20px; margin-bottom: 2rem; border: 1px solid var(--border-color); }
    .filter-form { display: flex; gap: 1.5rem; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
    .filter-group label { font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; }
    .filter-form .btn { height: 50px; padding: 0 2rem; }

    .table-card { border-radius: 24px; overflow: hidden; border: 1px solid var(--border-color); }
    .card-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02); }
    .chart-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0; }
    
    .customer-info { display: flex; flex-direction: column; }
    table { width: 100%; border-collapse: collapse; }
    th { padding: 1.25rem 2rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border-color); letter-spacing: 0.1em; }
    td { padding: 1.25rem 2rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; color: var(--text-secondary); }
    
    .st-tag { font-size: 0.65rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 6px; }
    .st-tag.due { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .st-tag.low { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .st-tag.paid { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    .text-red { color: #f87171; }
    .text-green { color: #10b981; }
    .text-dim { color: var(--text-dim); }
    .font-bold { font-weight: 700; }

    @media print {
        .sidebar, .navbar, .report-filters, .header-actions { display: none !important; }
        .main-content { left: 0 !important; width: 100% !important; padding: 0 !important; }
        .glass { background: white !important; border: 1px solid #eee !important; color: black !important; }
    }
    </style>
</body>
</html>
