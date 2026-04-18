<?php
$base_url = '../';
require '../auth.php';
require '../config.php';

// Fetch all inventory items
$stmt = $pdo->query('SELECT * FROM products ORDER BY quantity ASC');
$items = $stmt->fetchAll();

// Advanced Stats Engine
$total_low = 0;
$total_out = 0;
$healthy = 0;
foreach ($items as $item) {
    if ($item['quantity'] <= 0) $total_out++;
    elseif ($item['quantity'] < 10) $total_low++;
    else $healthy++;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Stock Health Analytics';
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
                        <h1 class="view-title">Stock Health & Alerts</h1>
                        <p class="view-subtitle">Monitor inventory velocity and replenishment status</p>
                    </div>
                    <div class="header-actions">
                        <a href="index.php" class="btn btn-outline">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            View All Inventory
                        </a>
                    </div>
                </div>

                <!-- Snapshot Cards -->
                <div class="stats-grid">
                    <div class="stat-box glass">
                        <div class="stat-info">
                            <span class="stat-label">Critical Outage</span>
                            <h2 class="stat-val text-red"><?= $total_out ?></h2>
                        </div>
                        <div class="stat-icon red-glow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                    </div>
                    <div class="stat-box glass">
                        <div class="stat-info">
                            <span class="stat-label">Low Stock Alerts</span>
                            <h2 class="stat-val text-orange"><?= $total_low ?></h2>
                        </div>
                        <div class="stat-icon orange-glow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                    </div>
                    <div class="stat-box glass">
                        <div class="stat-info">
                            <span class="stat-label">Healthy Stock</span>
                            <h2 class="stat-val text-green"><?= $healthy ?></h2>
                        </div>
                        <div class="stat-icon green-glow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>
                </div>

                <div class="inventory-controls glass">
                    <div class="search-engine">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" id="healthSearch" class="search-input" placeholder="Search by name or category..." autocomplete="off">
                        </div>
                    </div>
                    <div class="filter-tabs">
                        <button class="tab-btn active" data-filter="all">All</button>
                        <button class="tab-btn" data-filter="out">Out</button>
                        <button class="tab-btn" data-filter="low">Low</button>
                        <button class="tab-btn" data-filter="ok">Healthy</button>
                    </div>
                </div>

                <div class="table-card glass">
                    <div class="table-container">
                        <table id="healthTable">
                            <thead>
                                <tr>
                                    <th>Product Identity</th>
                                    <th style="width: 150px">Stock Volume</th>
                                    <th style="width: 150px">Status</th>
                                    <th style="width: 140px" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): 
                                    $qty = (int)$item['quantity'];
                                    $status = ($qty <= 0) ? 'out' : (($qty < 10) ? 'low' : 'ok');
                                    $statusText = ($status == 'out') ? 'OUT OF STOCK' : (($status == 'low') ? 'LOW' : 'HEALTHY');
                                    $pillClass = 'status-' . (($status == 'out') ? 'red' : (($status == 'low') ? 'orange' : 'green'));
                                    $searchString = strtolower($item['item_name'] . ' ' . $item['description']);
                                ?>
                                    <tr class="stock-row" data-status="<?= $status ?>" data-search="<?= htmlspecialchars($searchString) ?>">
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
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="p-text-info">
                                                        <span class="p-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                                        <span class="p-desc">Unit Value: ৳<?= number_format($item['price'], 2) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="stock-info">
                                                <span class="stock-val font-bold"><?= $qty ?></span>
                                                <span class="stock-unit">Units Left</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-pill <?= $pillClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td>
                                            <div class="action-cell">
                                                <a href="edit.php?id=<?= $item['id'] ?>" class="act-btn restock-btn">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                                    Restock
                                                </a>
                                            </div>
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
    .inventory-view { padding-bottom: 4rem; }
    .view-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .view-title { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem; }
    .view-subtitle { color: var(--text-dim); font-size: 0.9rem; }

    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-box { padding: 1.5rem; border-radius: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); position: relative; overflow: hidden; }
    .stat-val { font-size: 2.22rem; font-weight: 800; margin: 0; }
    .stat-label { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 0.25rem; }
    
    .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); }
    .stat-icon svg { width: 22px; height: 22px; }
    
    .red-glow { color: #f87171; background: rgba(239, 68, 68, 0.1); }
    .orange-glow { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
    .green-glow { color: #10b981; background: rgba(16, 185, 129, 0.1); }

    .inventory-controls { 
        display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; background: rgba(30, 41, 59, 0.4); 
    }

    .search-engine { flex: 1; max-width: 400px; }
    .search-input-wrapper { display: flex; align-items: center; gap: 0.75rem; color: var(--text-dim); }
    .search-input { background: transparent; border: none; color: var(--text-primary); font-size: 0.95rem; width: 100%; outline: none; padding: 0.5rem 0; }
    
    .filter-tabs { display: flex; gap: 0.5rem; }
    .tab-btn { background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.5rem 1rem; border-radius: 10px; cursor: pointer; font-size: 0.8rem; font-weight: 700; transition: 0.2s; }
    .tab-btn:hover { background: rgba(255, 255, 255, 0.1); color: var(--text-primary); }
    .tab-btn.active { background: var(--accent-primary); color: white; border-color: var(--accent-primary); }

    .table-card { padding: 0; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    table { width: 100%; border-collapse: collapse; min-width: 800px; }
    th { padding: 1.25rem 1.5rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border-color); letter-spacing: 0.1em; }
    td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }

    .product-row { transition: 0.2s; }
    .product-row:hover { background: rgba(255, 255, 255, 0.02); }

    .product-media-info { display: flex; align-items: center; gap: 1.25rem; }
    .p-thumb-wrapper { width: 44px; height: 44px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
    .p-thumb { width: 100%; height: 100%; object-fit: cover; }
    .p-placeholder { width: 44px; height: 44px; border-radius: 10px; background: rgba(255, 255, 255, 0.05); display: flex; align-items: center; justify-content: center; color: var(--text-dim); border: 1px dashed var(--border-color); }

    .p-text-info { display: flex; flex-direction: column; gap: 0.1rem; }
    .p-name { color: var(--text-primary); font-weight: 700; font-size: 0.95rem; }
    .p-desc { color: var(--text-dim); font-size: 0.75rem; }

    .stock-info { display: flex; flex-direction: column; }
    .stock-val { font-size: 1rem; color: var(--text-primary); }
    .stock-unit { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; }

    .status-pill { padding: 0.35rem 0.8rem; border-radius: 10px; font-size: 0.7rem; font-weight: 800; border: 1px solid transparent; }
    .status-green { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
    .status-orange { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
    .status-red { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    .restock-btn { 
        display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 10px; background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--border-color); font-size: 0.8rem; font-weight: 700; text-decoration: none; transition: 0.2s; 
    }
    .restock-btn:hover { background: var(--accent-primary); color: white; border-color: var(--accent-primary); transform: translateY(-1px); }

    @media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr; } .inventory-controls { flex-direction: column; gap: 1rem; align-items: stretch; } }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('healthSearch');
        const tabBtns = document.querySelectorAll('.tab-btn');
        const rows = document.querySelectorAll('.stock-row');

        let currentFilter = 'all';
        let currentSearch = '';

        function applyFilters() {
            rows.forEach(row => {
                const rowStatus = row.dataset.status;
                const rowSearch = row.dataset.search;

                const statusMatch = currentFilter === 'all' || rowStatus === currentFilter;
                const searchMatch = rowSearch.includes(currentSearch);

                if (statusMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', function() {
            currentSearch = this.value.toLowerCase().trim();
            applyFilters();
        });

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                tabBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                applyFilters();
            });
        });
    });
    </script>
</body>
</html>
