<?php
require 'auth.php';
require 'config.php';

// Search/Filter logic
$search = $_GET['search'] ?? '';
$query = 'SELECT s.*, c.name as customer_name 
          FROM sales s 
          LEFT JOIN customers c ON s.customer_id = c.id';
$params = [];

if ($search) {
    $query .= ' WHERE c.name LIKE ? OR s.payment_method LIKE ?';
    $params = ["%$search%", "%$search%"];
}

$query .= ' ORDER BY s.sale_date DESC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Sales History';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Sales History</h1>
                    <p class="text-muted">A comprehensive log of all transactions and customer purchases.</p>
                </div>
                <div class="header-actions">
                    <a href="pos.php" class="btn btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <path d="M7 15h.01"></path>
                            <path d="M11 15h.01"></path>
                        </svg>
                        New Sale
                    </a>
                </div>
            </header>

            <div class="inventory-controls animate-fade-in" style="animation-delay: 0.1s">
                <div class="search-box glass">
                    <form method="GET" action="" class="search-form">
                        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" name="search" class="search-input" placeholder="Filter by customer or payment..." value="<?= htmlspecialchars($search) ?>">
                    </form>
                </div>
            </div>

            <div class="table-card glass animate-fade-in" style="animation-delay: 0.2s">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#INV</th>
                                <th>Transaction Date</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Payment</th>
                                <th class="text-center">Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sales)): ?>
                                <tr>
                                    <td colspan="6" class="empty-row text-center">
                                        <p>Projected sales data will appear here once transactions occur.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td class="id-col">INV-<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="product-cell">
                                                <span class="p-name"><?= date('M d, Y', strtotime($sale['sale_date'])) ?></span>
                                                <span class="p-desc"><?= date('h:i A', strtotime($sale['sale_date'])) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-cell">
                                                <span class="p-name"><?= htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer') ?></span>
                                                <?php if (!$sale['customer_name']): ?>
                                                    <span class="p-desc">Retail Sale</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="price-col">৳<?= number_format($sale['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-pill status-dim"><?= htmlspecialchars($sale['payment_method']) ?></span>
                                        </td>
                                        <td>
                                            <div class="action-cell">
                                                <a href="view_invoice.php?id=<?= $sale['id'] ?>" class="act-btn edit" title="View/Print Invoice">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M6 9V2h12v7"></path>
                                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                        <rect x="6" y="14" width="12" height="8"></rect>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; }
    .header-main { font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem; }
    .inventory-controls { margin-bottom: 2rem; }
    .search-box { max-width: 400px; padding: 0.5rem 1rem; border-radius: 12px; }
    .search-input { background: transparent; border: none; color: var(--text-primary); font-size: 0.9rem; width: 100%; outline: none; }
    .table-card { padding: 0; overflow: hidden; border-radius: 20px; }
    .id-col { font-family: monospace; color: var(--text-muted); }
    .product-cell { display: flex; flex-direction: column; }
    .p-name { color: var(--text-primary); font-weight: 600; font-size: 0.95rem; }
    .p-desc { color: var(--text-dim); font-size: 0.8rem; }
    .price-col { color: var(--accent-primary); font-weight: 700; }
    .status-pill { padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
    .status-dim { background: rgba(255, 255, 255, 0.05); color: var(--text-muted); }
    .action-cell { display: flex; gap: 0.5rem; justify-content: center; }
    .act-btn { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-dim); transition: var(--transition); text-decoration: none; }
    .act-btn:hover { background: rgba(139, 92, 246, 0.1); color: var(--accent-primary); }
    .empty-row { padding: 4rem; color: var(--text-muted); }
    </style>
</body>
</html>
