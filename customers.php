<?php
require 'auth.php';
require 'config.php';

// Search logic
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
$pageTitle = 'Customers Registry';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Customers Registry</h1>
                    <p class="text-muted">Maintain comprehensive profiles for all your clients and retail buyers.</p>
                </div>
                <div class="header-actions">
                    <a href="add_customer.php" class="btn btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                        Add Customer
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
                        <input type="text" name="search" class="search-input" placeholder="Search customers..." value="<?= htmlspecialchars($search) ?>">
                    </form>
                </div>
            </div>

            <div class="table-card glass animate-fade-in" style="animation-delay: 0.2s">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Customer Name</th>
                                <th>Contact Details</th>
                                <th>Address</th>
                                <th>Recent At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="6" class="empty-row text-center">
                                        <p>No customer records detected.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $c): ?>
                                    <tr>
                                        <td class="id-col">#<?= str_pad($c['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="product-cell">
                                                <span class="p-name"><?= htmlspecialchars($c['name']) ?></span>
                                                <span class="p-desc">Client ID: CU-<?= $c['id'] ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <span><?= htmlspecialchars($c['phone'] ?: 'N/A') ?></span>
                                                <span class="email-tag"><?= htmlspecialchars($c['email'] ?: 'No email') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-cell">
                                                <span class="p-desc truncate"><?= htmlspecialchars($c['address'] ?: 'Walk-in customer') ?></span>
                                            </div>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                        <td>
                                            <div class="action-cell">
                                                <a href="edit_customer.php?id=<?= $c['id'] ?>" class="act-btn edit">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </a>
                                                <a href="delete_customer.php?id=<?= $c['id'] ?>" class="act-btn del" onclick="return confirm('Archive client data?')">
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
    .truncate { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .contact-info { display: flex; flex-direction: column; gap: 2px; font-size: 0.85rem; }
    .email-tag { color: var(--accent-primary); font-size: 0.75rem; }
    .action-cell { display: flex; gap: 0.5rem; justify-content: center; }
    .act-btn { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-dim); transition: var(--transition); text-decoration: none; }
    .act-btn:hover { background: rgba(255, 255, 255, 0.05); color: var(--text-primary); }
    .act-btn.edit:hover { background: rgba(139, 92, 246, 0.1); color: var(--accent-primary); }
    .act-btn.del:hover { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .empty-row { padding: 4rem; color: var(--text-muted); }
    </style>
</body>
</html>
