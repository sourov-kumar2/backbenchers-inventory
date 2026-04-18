<?php
require 'auth.php';
require 'config.php';

// Fetch all inventory items
$stmt = $pdo->query('SELECT * FROM products ORDER BY quantity ASC');
$items = $stmt->fetchAll();

// Statistics
$total_low = 0;
$total_out = 0;
foreach ($items as $item) {
    if ($item['quantity'] <= 0) $total_out++;
    elseif ($item['quantity'] < 10) $total_low++;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Inventory Health Report';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Inventory Health</h1>
                    <p class="text-muted">Proactive monitoring for low-stock and out-of-stock items.</p>
                </div>
            </header>

            <div class="stats-grid animate-fade-in" style="animation-delay: 0.1s">
                <div class="stat-box glass">
                    <div class="stat-main">
                        <h3 class="stat-val text-red"><?= $total_out ?></h3>
                        <p class="stat-desc">Out of Stock</p>
                    </div>
                </div>
                <div class="stat-box glass">
                    <div class="stat-main">
                        <h3 class="stat-val text-orange"><?= $total_low ?></h3>
                        <p class="stat-desc">Low Stock Alerts</p>
                    </div>
                </div>
                <div class="stat-box glass">
                    <div class="stat-main">
                        <h3 class="stat-val"><?= count($items) ?></h3>
                        <p class="stat-desc">Total Unique SKUs</p>
                    </div>
                </div>
            </div>

            <div class="filter-tabs animate-fade-in" style="animation-delay: 0.2s">
                <button class="tab-btn active" onclick="filterTable('all', this)">All Items</button>
                <button class="tab-btn" onclick="filterTable('low', this)">Low Stock Only</button>
                <button class="tab-btn" onclick="filterTable('out', this)">Out of Stock Only</button>
            </div>

            <div class="table-card glass animate-fade-in" style="animation-delay: 0.3s">
                <div class="table-container">
                    <table id="stockTable">
                        <thead>
                            <tr>
                                <th>Product Details</th>
                                <th>On Hand</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $status = ($item['quantity'] <= 0) ? 'out' : (($item['quantity'] < 10) ? 'low' : 'ok');
                                $statusText = ($status == 'out') ? 'Out of Stock' : (($status == 'low') ? 'Critical Low' : 'Healthy');
                                $pillClass = 'status-' . (($status == 'out') ? 'red' : (($status == 'low') ? 'orange' : 'green'));
                            ?>
                                <tr class="stock-row" data-status="<?= $status ?>">
                                    <td>
                                        <div class="product-media-info">
                                            <?php if ($item['image']): ?>
                                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="p-thumb">
                                            <?php else: ?>
                                                <div class="p-thumb p-placeholder">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                                                </div>
                                            <?php endif; ?>
                                            <div class="p-text-info">
                                                <span class="p-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                                <span class="p-desc">৳<?= number_format($item['price'], 2) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-bold"><?= $item['quantity'] ?> units</td>
                                    <td>
                                        <span class="status-pill <?= $pillClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td>
                                        <div class="action-cell">
                                            <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-outline btn-sm">Restock</a>
                                        </div>
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

    <script>
    function filterTable(status, btn) {
        // Update tabs
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Filter rows
        const rows = document.querySelectorAll('.stock-row');
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = 'table-row';
            } else {
                row.style.display = (row.dataset.status === status) ? 'table-row' : 'none';
            }
        });
    }
    </script>

    <style>
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-box { padding: 1.5rem; border-radius: 20px; }
    .stat-val { font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem; }
    .text-red { color: #f87171; }
    .text-orange { color: #fbbf24; }

    .filter-tabs { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; }
    .tab-btn { background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.6rem 1.25rem; border-radius: 12px; cursor: pointer; font-size: 0.85rem; font-weight: 600; transition: 0.2s; }
    .tab-btn:hover { background: rgba(255, 255, 255, 0.08); color: var(--text-primary); }
    .tab-btn.active { background: var(--accent-primary); color: white; border-color: var(--accent-primary); }

    .table-card { padding: 0; border-radius: 20px; overflow: hidden; }
    .product-media-info { display: flex; align-items: center; gap: 1rem; }
    .p-thumb { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; }
    .p-placeholder { display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); color: var(--text-dim); }
    .p-text-info { display: flex; flex-direction: column; }
    .p-name { color: var(--text-primary); font-weight: 600; font-size: 0.9rem; }
    .p-desc { color: var(--text-dim); font-size: 0.75rem; }

    .status-pill { padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
    .status-green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .status-orange { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .status-red { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    
    .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
    </style>
</body>
</html>
