<?php
require 'auth.php';
require 'config.php';

// Intelligence Engine: Fetching Sales with Customer Context
$query = 'SELECT s.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone 
          FROM sales s 
          LEFT JOIN customers c ON s.customer_id = c.id';

$query .= ' ORDER BY s.sale_date DESC';
$stmt = $pdo->prepare($query);
$stmt->execute();
$sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Transaction Intelligence';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <div class="inventory-view animate-fade-in">
                <!-- Page Header -->
                <div class="view-header">
                    <div class="header-content">
                        <h1 class="view-title">Sales Ledger</h1>
                        <p class="view-subtitle">Audit and explore your transaction history</p>
                    </div>
                    <div class="header-actions">
                        <a href="pos.php" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            New Sale Terminal
                        </a>
                    </div>
                </div>

                <!-- Control Bar -->
                <div class="inventory-controls glass">
                    <div class="search-engine">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" id="salesSearch" class="search-input" placeholder="Search by Invoice #, Name, Email or Phone..." autocomplete="off">
                        </div>
                    </div>
                    <div class="stats-badge">
                        <span class="count-label">Ledger Volume:</span>
                        <span class="count-value" id="saleCount"><?= count($sales) ?> Records</span>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="table-card glass">
                    <div class="table-container">
                        <table id="salesTable">
                            <thead>
                                <tr>
                                    <th style="width: 140px">Invoice #</th>
                                    <th style="width: 200px">Timestamp</th>
                                    <th>Customer Intelligence</th>
                                    <th style="width: 160px">Total Settlement</th>
                                    <th style="width: 140px">Method</th>
                                    <th style="width: 100px" class="text-center">Audit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sales)): ?>
                                    <tr class="no-data">
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-icon">
                                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                                    </svg>
                                                </div>
                                                <p>No transactions recorded in the ledger yet.</p>
                                                <a href="pos.php" class="btn btn-outline btn-sm">Launch POS</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sales as $sale): 
                                        $invoiceId = 'INV-' . str_pad($sale['id'], 5, '0', STR_PAD_LEFT);
                                        $cName = $sale['customer_name'] ?: 'Walk-in Customer';
                                        $cEmail = $sale['customer_email'] ?: '';
                                        $cPhone = $sale['customer_phone'] ?: '';
                                        $searchString = strtolower($invoiceId . ' ' . $cName . ' ' . $cEmail . ' ' . $cPhone . ' ' . $sale['payment_method']);
                                    ?>
                                        <tr class="sale-row" data-search="<?= htmlspecialchars($searchString) ?>">
                                            <td class="id-col"><?= $invoiceId ?></td>
                                            <td>
                                                <div class="timestamp-cell">
                                                    <span class="date-val"><?= date('M d, Y', strtotime($sale['sale_date'])) ?></span>
                                                    <span class="time-val"><?= date('h:i A', strtotime($sale['sale_date'])) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="customer-info-cell">
                                                    <span class="customer-name"><?= htmlspecialchars($cName) ?></span>
                                                    <div class="customer-sub-info">
                                                        <?php if ($cEmail): ?>
                                                            <span class="info-item"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg><?= htmlspecialchars($cEmail) ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($cPhone): ?>
                                                            <span class="info-item"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg><?= htmlspecialchars($cPhone) ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!$cEmail && !$cPhone && !$sale['customer_name']): ?>
                                                            <span class="info-item retail">RETAIL SALE</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price-col">
                                                <span class="price-symbol">৳</span>
                                                <span class="price-value"><?= number_format($sale['total_amount'], 2) ?></span>
                                                <?php if($sale['amount_due'] > 0): ?>
                                                    <span class="due-tag">৳<?= number_format($sale['amount_due'], 1) ?> Due</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="method-pill"><?= htmlspecialchars($sale['payment_method']) ?></span>
                                            </td>
                                            <td>
                                                <div class="action-cell">
                                                    <a href="view_invoice.php?id=<?= $sale['id'] ?>" class="act-btn audit-btn" title="View Ledger Detail">
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
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .inventory-view { padding-bottom: 4rem; }
    .view-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .view-title { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem; }
    .view-subtitle { color: var(--text-dim); font-size: 0.9rem; }

    .inventory-controls { 
        display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; background: rgba(30, 41, 59, 0.4); 
    }

    .search-engine { flex: 1; max-width: 500px; }
    .search-input-wrapper { display: flex; align-items: center; gap: 0.75rem; color: var(--text-dim); }
    .search-input { background: transparent; border: none; color: var(--text-primary); font-size: 0.95rem; width: 100%; outline: none; padding: 0.5rem 0; }

    .stats-badge { background: rgba(255, 255, 255, 0.05); padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; border: 1px solid var(--border-color); }
    .count-label { color: var(--text-dim); margin-right: 0.4rem; }
    .count-value { color: var(--accent-primary); font-weight: 700; }

    .table-card { padding: 0; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    table { width: 100%; border-collapse: collapse; min-width: 900px; }
    th { padding: 1.25rem 1.5rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border-color); letter-spacing: 0.1em; }
    td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; vertical-align: middle; }

    .sale-row { transition: 0.2s; }
    .sale-row:hover { background: rgba(255, 255, 255, 0.02); }

    .id-col { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: var(--text-dim); font-weight: 600; }
    .timestamp-cell { display: flex; flex-direction: column; }
    .date-val { color: var(--text-primary); font-weight: 700; font-size: 0.95rem; }
    .time-val { color: var(--text-dim); font-size: 0.75rem; }

    .customer-info-cell { display: flex; flex-direction: column; gap: 0.15rem; }
    .customer-name { color: var(--text-primary); font-weight: 700; font-size: 0.95rem; }
    .customer-sub-info { display: flex; gap: 1rem; flex-wrap: wrap; }
    .info-item { font-size: 0.75rem; color: var(--text-dim); display: flex; align-items: center; gap: 0.35rem; }
    .info-item.retail { color: var(--accent-primary); font-weight: 800; letter-spacing: 0.05em; opacity: 0.7; }

    .price-col { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; height: 100%; }
    .price-symbol { color: var(--accent-primary); font-weight: 600; }
    .price-value { color: var(--text-primary); font-weight: 700; font-size: 1.05rem; }
    .due-tag { font-size: 0.65rem; color: #ef4444; font-weight: 700; margin-top: -0.2rem; }

    .method-pill { background: rgba(255, 255, 255, 0.06); padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border: 1px solid var(--border-color); }

    .action-cell { display: flex; justify-content: center; }
    .audit-btn { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--text-dim); transition: 0.2s; border: 1px solid var(--border-color); background: rgba(255,255,255,0.03); }
    .audit-btn:hover { background: rgba(139, 92, 246, 0.15); color: var(--accent-primary); border-color: var(--accent-primary); }

    .empty-state { padding: 5rem; text-align: center; color: var(--text-muted); }
    .empty-icon { opacity: 0.3; margin-bottom: 2rem; }

    @media (max-width: 1000px) { .customer-sub-info { display: none; } }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('salesSearch');
        const saleRows = document.querySelectorAll('.sale-row');
        const countDisplay = document.getElementById('saleCount');

        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            let visibleCount = 0;

            saleRows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if (searchData.includes(term)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            countDisplay.textContent = `${visibleCount} Records`;
        });
    });
    </script>
</body>
</html>
