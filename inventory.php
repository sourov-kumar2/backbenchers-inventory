<?php
require 'auth.php';
require 'config.php';

// Search logic
$search = $_GET['search'] ?? '';
$query = 'SELECT * FROM products';
$params = [];

if ($search) {
    $query .= ' WHERE item_name LIKE ? OR description LIKE ?';
    $params = ["%$search%", "%$search%"];
}

$query .= ' ORDER BY id DESC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Inventory List';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Inventory Management</h1>
                    <p class="text-muted">Total of <?= count($items) ?> stock items detected.</p>
                </div>
                <div class="header-actions">
                    <a href="add_item.php" class="btn btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                        New Entry
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
                        <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                    </form>
                </div>
            </div>

            <div class="table-card glass animate-fade-in" style="animation-delay: 0.2s">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Product Details</th>
                                <th>Units</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="empty-row text-center">
                                        <p>No inventory records found matching your criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): 
                                    $qty = (int)$item['quantity'];
                                    $status = ($qty === 0) ? 'Out' : (($qty < 10) ? 'Low' : 'OK');
                                    $statusClass = ($qty === 0) ? 'status-red' : (($qty < 10) ? 'status-orange' : 'status-green');
                                ?>
                                    <tr>
                                        <td class="id-col">#<?= str_pad($item['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="product-cell">
                                                <div class="product-media-info">
                                                    <?php if ($item['image']): ?>
                                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="p-thumb">
                                                    <?php else: ?>
                                                        <div class="p-thumb p-placeholder">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                                <polyline points="21 15 16 10 5 21"></polyline>
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="p-text-info">
                                                        <span class="p-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                                        <span class="p-desc"><?= htmlspecialchars($item['description']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-bold"><?= $qty ?></td>
                                        <td class="price-col">৳<?= number_format($item['price'], 2) ?></td>
                                        <td>
                                            <span class="status-pill <?= $statusClass ?>"><?= $status ?></span>
                                        </td>
                                        <td>
                                            <div class="action-cell">
                                                <a href="edit_item.php?id=<?= $item['id'] ?>" class="act-btn edit">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </a>
                                                <a href="delete_item.php?id=<?= $item['id'] ?>" class="act-btn del" onclick="return confirm('Secure Delete?')">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
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
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 2.5rem;
    }

    .header-main {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .inventory-controls {
        margin-bottom: 2rem;
    }

    .search-box {
        max-width: 400px;
        padding: 0.5rem 1rem;
        border-radius: 12px;
    }

    .search-input {
        background: transparent;
        border: none;
        color: var(--text-primary);
        font-size: 0.9rem;
        width: 100%;
        outline: none;
    }

    .table-card { padding: 0; overflow: hidden; border-radius: 20px; }
    
    .id-col { font-family: monospace; color: var(--text-muted); }
    
    .product-cell { display: flex; flex-direction: column; }
    
    .product-media-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .p-thumb {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid var(--border-color);
    }
    
    .p-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-dim);
    }

    .p-text-info {
        display: flex;
        flex-direction: column;
    }

    .p-name { color: var(--text-primary); font-weight: 600; font-size: 0.95rem; }
    .p-desc { color: var(--text-dim); font-size: 0.8rem; }
    
    .price-col { color: var(--accent-primary); font-weight: 700; }
    
    .status-pill {
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .status-orange { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .status-red { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    
    .action-cell { display: flex; gap: 0.5rem; justify-content: center; }
    
    .act-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dim);
        transition: var(--transition);
        text-decoration: none;
    }
    
    .act-btn:hover { background: rgba(139, 92, 246, 0.1); color: var(--accent-primary); }
    
    .empty-row { padding: 4rem; color: var(--text-muted); }
    
    @media (max-width: 900px) {
        .search-box { max-width: 100%; }
        .p-thumb { display: none; }
    }
    </style>
</body>
</html>