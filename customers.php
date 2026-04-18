<?php
require 'auth.php';
require 'config.php';

// Intelligence Engine: Fetching Customer Intelligence
$search = $_GET['search'] ?? '';
$query = 'SELECT * FROM customers';
$params = [];

if ($search) {
    $query .= ' WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?';
    $params = ["%$search%", "%$search%", "%$search%"];
}

$query .= ' ORDER BY id DESC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Customer Intelligence Registry';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <div class="inventory-view animate-fade-in">
                <!-- Page Header & CTA -->
                <div class="view-header">
                    <div class="header-content">
                        <h1 class="view-title">Customer Directory</h1>
                        <p class="view-subtitle">Management of institutional and retail client profiles</p>
                    </div>
                    <div class="header-actions">
                        <a href="add_customer.php" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add New Customer
                        </a>
                    </div>
                </div>

                <!-- Searching & Utility -->
                <div class="inventory-controls glass">
                    <div class="search-engine">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <form method="GET" action="" style="flex: 1;">
                                <input type="text" name="search" class="search-input" placeholder="Search by name, phone or email..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                            </form>
                        </div>
                    </div>
                    <div class="stats-badge">
                        <span class="count-label">Intelligence Count:</span>
                        <span class="count-value"><?= count($customers) ?> Clients</span>
                    </div>
                </div>

                <!-- Data Ledger -->
                <div class="table-card glass">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 100px">System ID</th>
                                    <th>Client Profile</th>
                                    <th>Communication Details</th>
                                    <th>Service Address</th>
                                    <th style="width: 140px">Registry Date</th>
                                    <th style="width: 120px" class="text-center">Maintenance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers)): ?>
                                    <tr class="no-data">
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-icon">
                                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="9" cy="7" r="4"></circle>
                                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                    </svg>
                                                </div>
                                                <p>No client records detected in the intelligence registry.</p>
                                                <a href="add_customer.php" class="btn btn-outline btn-sm">Initialize First Profile</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $c): ?>
                                        <tr class="client-row">
                                            <td class="id-col">#<?= str_pad($c['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                            <td>
                                                <div class="identity-cell">
                                                    <span class="c-name"><?= htmlspecialchars($c['name']) ?></span>
                                                    <span class="c-id">ID: CU-<?= $c['id'] ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-details-cell">
                                                    <span class="c-phone">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                                        <?= htmlspecialchars($c['phone'] ?: 'No Phone') ?>
                                                    </span>
                                                    <span class="c-email"><?= htmlspecialchars($c['email'] ?: 'No Email Address') ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="address-cell">
                                                    <span class="c-address"><?= htmlspecialchars($c['address'] ?: 'Walk-in client (No address)') ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="date-badge">
                                                    <?= date('M d, Y', strtotime($c['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="maintain-actions">
                                                    <a href="edit_customer.php?id=<?= $c['id'] ?>" class="m-btn edit" title="Modify Intel">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="delete_customer.php?id=<?= $c['id'] ?>" class="m-btn delete" onclick="return confirm('Archive profile permanently?')" title="Archive">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
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

    .search-engine { flex: 1; max-width: 450px; }
    .search-input-wrapper { display: flex; align-items: center; gap: 0.75rem; color: var(--text-dim); }
    .search-input { background: transparent; border: none; color: var(--text-primary); font-size: 0.95rem; width: 100%; outline: none; padding: 0.5rem 0; }

    .stats-badge { background: rgba(255, 255, 255, 0.05); padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; border: 1px solid var(--border-color); }
    .count-label { color: var(--text-dim); margin-right: 0.4rem; }
    .count-value { color: var(--accent-primary); font-weight: 700; }

    .table-card { padding: 0; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    table { width: 100%; border-collapse: collapse; min-width: 900px; }
    th { padding: 1.25rem 1.5rem; text-align: left; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); border-bottom: 1px solid var(--border-color); letter-spacing: 0.1em; }
    td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; vertical-align: middle; }

    .client-row { transition: 0.2s; }
    .client-row:hover { background: rgba(255, 255, 255, 0.02); }

    .id-col { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: var(--text-dim); font-weight: 600; }
    .identity-cell { display: flex; flex-direction: column; }
    .c-name { color: var(--text-primary); font-weight: 700; font-size: 0.95rem; }
    .c-id { color: var(--text-dim); font-size: 0.75rem; }

    .contact-details-cell { display: flex; flex-direction: column; gap: 0.15rem; }
    .c-phone { color: var(--text-primary); font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 0.4rem; }
    .c-email { color: var(--accent-primary); font-size: 0.75rem; font-weight: 600; }

    .address-cell { color: var(--text-dim); font-size: 0.85rem; line-height: 1.4; max-width: 250px; }
    .c-address { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    .date-badge { color: var(--text-muted); font-size: 0.8rem; }

    .maintain-actions { display: flex; justify-content: center; gap: 0.4rem; }
    .m-btn { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--text-dim); transition: 0.2s; border: 1px solid var(--border-color); background: rgba(255,255,255,0.03); text-decoration: none; }
    .m-btn:hover { border-color: var(--accent-primary); color: var(--accent-primary); background: rgba(139, 92, 246, 0.1); }
    .m-btn.delete:hover { border-color: #ef4444; color: #ef4444; background: rgba(239, 68, 68, 0.1); }

    .empty-state { padding: 5rem; text-align: center; color: var(--text-muted); }
    .empty-icon { opacity: 0.3; margin-bottom: 2rem; }

    @media (max-width: 1000px) { .address-cell { display: none; } }
    </style>
</body>
</html>
