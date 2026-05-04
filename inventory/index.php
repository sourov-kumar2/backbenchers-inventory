<?php
$base_url = '../';
require '../auth.php';
require '../config.php';

// Initial fetch logic (Search fallback)
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
$pageTitle = 'Stock Explorer';
include '../partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include '../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../partials/navbar.php'; ?>
            
            <div class="inventory-view animate-fade-in">
                <!-- Page Header Actions -->
                <div class="view-header">
                    <div class="header-content">
                        <h1 class="view-title">Product Inventory</h1>
                        <p class="view-subtitle">Manage and monitor your stock levels</p>
                    </div>
                    <div class="header-actions">
                        <a href="add.php" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Register New Product
                        </a>
                    </div>
                </div>

                <div class="inventory-controls glass">
                    <div class="search-engine">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" id="inventorySearch" class="search-input" placeholder="Quick find by name, description or ID..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        </div>
                    </div>
                    <div class="stats-badge" id="productCount">
                        <span class="count-label">Displaying:</span>
                        <span class="count-value"><?= count($items) ?> Products</span>
                    </div>
                </div>

                <div class="table-card glass">
                    <div class="table-container">
                        <table id="inventoryTable">
                            <thead>
                                <tr>
                                    <th style="width: 80px">#ID</th>
                                    <th>Product Details</th>
                                    <th style="width: 100px">Stock</th>
                                    <th style="width: 150px">Price Value</th>
                                    <th style="width: 100px">Status</th>
                                    <th style="width: 120px" class="text-center">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr class="no-data">
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-icon">
                                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                    </svg>
                                                </div>
                                                <p>No inventory records found.</p>
                                                <a href="add.php" class="btn btn-outline btn-sm">Add First Product</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): 
                                        $qty = (int)$item['quantity'];
                                        $status = ($qty === 0) ? 'Out' : (($qty < 10) ? 'Low' : 'OK');
                                        $statusClass = ($qty === 0) ? 'status-red' : (($qty < 10) ? 'status-orange' : 'status-green');
                                        $searchString = strtolower($item['item_name'] . ' ' . $item['description'] . ' #' . $item['id']);
                                    ?>
                                        <tr class="product-row" data-search="<?= htmlspecialchars($searchString) ?>">
                                            <td class="id-col">#<?= str_pad($item['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                            <td>
                                                <div class="product-cell">
                                                    <div class="product-media-info">
                                                        <?php 
                                                            $img_src = '';
                                                            if (!empty($item['image'])) {
                                                                $img_src = strpos($item['image'], 'http') === 0 ? $item['image'] : $base_url . ltrim(str_replace('../', '', $item['image']), '/');
                                                            }
                                                        ?>
                                                        <?php if ($img_src): ?>
                                                            <div class="p-thumb-wrapper">
                                                                <img src="<?= htmlspecialchars($img_src) ?>" alt="" class="p-thumb">
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="p-thumb p-placeholder">
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                                            <td>
                                                <span class="stock-value font-bold"><?= $qty ?></span>
                                                <span class="stock-unit">Units</span>
                                            </td>
                                            <td class="price-col">
                                                <span class="price-symbol">৳</span>
                                                <span class="price-value"><?= number_format($item['price'], 2) ?></span>
                                            </td>
                                            <td>
                                                <span class="status-pill <?= $statusClass ?>"><?= $status ?></span>
                                            </td>
                                            <td>
                                                <div class="action-cell">
                                                    <a href="edit.php?id=<?= $item['id'] ?>" class="act-btn edit" title="Edit Product">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="delete.php?id=<?= $item['id'] ?>" class="act-btn del" onclick="return confirm('Secure Delete?')">
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
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .inventory-view { padding-bottom: 4rem; }
    
    .view-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .view-title { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem; }
    .view-subtitle { color: var(--text-dim); font-size: 0.9rem; }

    .inventory-controls { 
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        background: var(--bg-card);
    }

    .search-engine { flex: 1; max-width: 500px; }
    .search-input-wrapper { display: flex; align-items: center; gap: 0.75rem; color: var(--text-dim); }
    .search-input { 
        background: transparent; border: none; color: var(--text-primary); font-size: 0.95rem; width: 100%; outline: none; 
        padding: 0.5rem 0;
    }
    .search-input::placeholder { color: var(--text-dim); opacity: 0.6; }

    .stats-badge { background: rgba(255, 255, 255, 0.05); padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; border: 1px solid var(--border-color); }
    .count-label { color: var(--text-dim); margin-right: 0.4rem; }
    .count-value { color: var(--accent-primary); font-weight: 700; }

    .table-card { padding: 0; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .table-container { overflow-x: auto; }
    
    table { width: 100%; border-collapse: collapse; min-width: 900px; }
    th { padding: 1.25rem 1.5rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border-color); letter-spacing: 0.1em; }
    td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }

    .product-row { transition: 0.2s; }
    .product-row:hover { background: rgba(255, 255, 255, 0.02); }

    .id-col { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: var(--text-dim); }
    
    .product-media-info { display: flex; align-items: center; gap: 1.25rem; }
    .p-thumb-wrapper { width: 48px; height: 48px; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-color); }
    .p-thumb { width: 100%; height: 100%; object-fit: cover; }
    
    .p-placeholder { 
        width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); 
        display: flex; align-items: center; justify-content: center; color: var(--text-dim); border: 1px dashed var(--border-color);
    }

    .p-text-info { display: flex; flex-direction: column; gap: 0.15rem; }
    .p-name { color: var(--text-primary); font-weight: 700; font-size: 1rem; }
    .p-desc { color: var(--text-dim); font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px; }

    .stock-value { font-size: 1rem; color: var(--text-primary); }
    .stock-unit { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; display: block; }

    .price-col { vertical-align: middle; }
    .price-symbol { color: var(--accent-primary); font-weight: 600; margin-right: 2px; }
    .price-value { color: var(--text-primary); font-weight: 700; font-size: 1.05rem; }

    .status-pill { padding: 0.3rem 0.75rem; border-radius: 10px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border: 1px solid transparent; }
    .status-green { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
    .status-orange { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
    .status-red { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    .action-cell { display: flex; gap: 0.6rem; justify-content: center; }
    .act-btn { 
        width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; 
        color: var(--text-dim); transition: 0.2s; border: 1px solid var(--border-color); background: rgba(255,255,255,0.03);
    }
    .act-btn.edit:hover { background: rgba(139, 92, 246, 0.15); color: var(--accent-primary); border-color: var(--accent-primary); }
    .act-btn.del:hover { background: rgba(239, 68, 68, 0.15); color: var(--danger); border-color: var(--danger); }

    .empty-state { padding: 5rem; text-align: center; color: var(--text-muted); }
    .empty-icon { opacity: 0.3; margin-bottom: 1.5rem; }
    
    @media (max-width: 1100px) {
        .p-desc { display: none; }
        .view-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('inventorySearch');
        const productRows = document.querySelectorAll('.product-row');
        const countDisplay = document.querySelector('.count-value');

        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            let visibleCount = 0;

            productRows.forEach(row => {
                const searchContent = row.getAttribute('data-search');
                if (searchContent.includes(term)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            countDisplay.textContent = `${visibleCount} Products`;
        });
    });
    </script>
</body>
</html>