<?php
require '../auth.php';
require '../config.php';

$pageTitle = 'Sales Performance Report';
$base_url = '../';

// Date filters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Sales Report Data
$stmt = $pdo->prepare("SELECT s.*, c.name as customer_name 
                       FROM sales s 
                       LEFT JOIN customers c ON s.customer_id = c.id 
                       WHERE DATE(s.sale_date) BETWEEN ? AND ? 
                       ORDER BY s.sale_date DESC");
$stmt->execute([$start_date, $end_date]);
$sales_report = $stmt->fetchAll();

$total_sales_val = 0;
$total_paid_val = 0;
$total_due_val = 0;
foreach($sales_report as $sr) {
    $total_sales_val += $sr['total_amount'];
    $total_paid_val += $sr['amount_paid'];
    $total_due_val += $sr['amount_due'];
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
                        <h1 class="view-title">Sales Performance Ledger</h1>
                        <p class="view-subtitle">Detailed audit of transaction volume and revenue streams</p>
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

                <!-- Date Range Selector -->
                <div class="report-filters glass">
                    <form method="GET" class="filter-form">
                        <div class="filter-group">
                            <label>From Date</label>
                            <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
                        </div>
                        <div class="filter-group">
                            <label>To Date</label>
                            <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="metrics-grid">
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Total Revenue</span>
                            <h2 class="m-value">৳<?= number_format($total_sales_val, 2) ?></h2>
                        </div>
                        <div class="metric-icon rev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Collection Rate</span>
                            <h2 class="m-value" style="color: #10b981;"><?= $total_sales_val > 0 ? round(($total_paid_val / $total_sales_val) * 100, 1) : 0 ?>%</h2>
                        </div>
                        <div class="metric-icon profit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Net Paid</span>
                            <h2 class="m-value">৳<?= number_format($total_paid_val, 2) ?></h2>
                        </div>
                        <div class="metric-icon box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                    </div>
                    <div class="metric-card glass">
                        <div class="metric-info">
                            <span class="m-label">Outstanding Dues</span>
                            <h2 class="m-value" style="color: #f87171;">৳<?= number_format($total_due_val, 2) ?></h2>
                        </div>
                        <div class="metric-icon due"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div>
                    </div>
                </div>

                <div class="table-card glass">
                    <div class="card-header">
                        <h3 class="chart-title">Detailed Sales Log</h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date & Time</th>
                                    <th>Customer</th>
                                    <th>Subtotal</th>
                                    <th>Discount</th>
                                    <th>Tax</th>
                                    <th>Total Payable</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sales_report)): ?>
                                    <tr><td colspan="10" class="text-center">No sales found for the selected period.</td></tr>
                                <?php else: ?>
                                    <?php foreach($sales_report as $sale): ?>
                                    <tr>
                                        <td class="font-bold">INV-<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td><?= date('M d, Y h:i A', strtotime($sale['sale_date'])) ?></td>
                                        <td><?= htmlspecialchars($sale['customer_name'] ?: 'Retail Client') ?></td>
                                        <td>৳<?= number_format($sale['subtotal_amount'], 2) ?></td>
                                        <td class="text-red">-৳<?= number_format($sale['discount_amount'], 2) ?></td>
                                        <td>৳<?= number_format($sale['tax_amount'], 2) ?></td>
                                        <td class="font-bold">৳<?= number_format($sale['total_amount'], 2) ?></td>
                                        <td class="text-green">৳<?= number_format($sale['amount_paid'], 2) ?></td>
                                        <td class="<?= $sale['amount_due'] > 0 ? 'text-red' : '' ?>">৳<?= number_format($sale['amount_due'], 2) ?></td>
                                        <td>
                                            <?php if($sale['amount_due'] > 0): ?>
                                                <span class="st-tag due">DUE</span>
                                            <?php else: ?>
                                                <span class="st-tag paid">PAID</span>
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
    .metric-icon.rev { color: #8b5cf6; background: rgba(139, 92, 246, 0.1); }
    .metric-icon.profit { color: #10b981; background: rgba(16, 185, 129, 0.1); }
    .metric-icon.box { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
    .metric-icon.due { color: #f87171; background: rgba(239, 68, 68, 0.1); }

    .table-card { border-radius: 24px; overflow: hidden; border: 1px solid var(--border-color); }
    .card-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02); }
    .chart-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0; }
    
    table { width: 100%; border-collapse: collapse; }
    th { padding: 1.25rem 1.5rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border-color); letter-spacing: 0.1em; }
    td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-secondary); }
    
    .st-tag { font-size: 0.6rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 6px; }
    .st-tag.due { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .st-tag.paid { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    .text-red { color: #f87171; }
    .text-green { color: #10b981; }
    .font-bold { font-weight: 700; }

    @media print {
        .sidebar, .navbar, .report-filters, .header-actions { display: none !important; }
        .main-content { left: 0 !important; width: 100% !important; padding: 0 !important; }
        .glass { background: white !important; border: 1px solid #eee !important; color: black !important; }
        .m-value, .view-title, .chart-title { color: black !important; }
        .metric-card { border: 1px solid #eee !important; }
    }
    </style>
</body>
</html>
