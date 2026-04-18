<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_inventory = ($current_dir === 'inventory' || in_array($current_page, ['inventory.php', 'edit_item.php', 'add_item.php', 'purchase.php', 'stock_report.php']));

$base_url = $base_url ?? '';
$brand_name = $sys['system_name'] ?? 'Backbenchers';
$logo_src = $sys['system_logo'] ?? '';
if ($logo_src && !filter_var($logo_src, FILTER_VALIDATE_URL) && strpos($logo_src, '/') !== 0) {
    $logo_src = $base_url . $logo_src;
}
?>
<aside class="sidebar glass">
    <div class="sidebar-header">
        <a href="<?= $base_url ?>dashboard.php" class="sidebar-brand">
            <?php if ($logo_src): ?>
                <img src="<?= $logo_src ?>" alt="Logo" class="s-brand-logo">
            <?php else: ?>
                <div class="s-brand-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--accent-primary)" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
            <?php endif; ?>
            <span class="s-brand-text"><?= htmlspecialchars(strtoupper($brand_name)) ?></span>
        </a>
        <!-- Mobile Close Button -->
        <button id="sidebarClose" class="show-mobile btn-icon-sm">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <div class="sidebar-wrapper">
        <div class="sidebar-content">
            <!-- Dashboard Section -->
            <div class="sidebar-section">
                <span class="section-label">Overview</span>
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?= $base_url ?>dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                                <div class="nav-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                </div>
                                <span class="nav-text">Main Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Sales Section (Moved Up) -->
            <div class="sidebar-section">
                <span class="section-label">Sales & POS</span>
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?= $base_url ?>pos.php" class="nav-link <?= $current_page == 'pos.php' ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M7 15h.01"></path><path d="M11 15h.01"></path><path d="M15 15h.01"></path><path d="M7 11h.01"></path><path d="M11 11h.01"></path><path d="M15 11h.01"></path></svg></div>
                                <span class="nav-text">POS Terminal</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>sales_list.php" class="nav-link <?= $current_page == 'sales_list.php' ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
                                <span class="nav-text">Sales History</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>manage_dues.php" class="nav-link <?= $current_page == 'manage_dues.php' ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                                <span class="nav-text">Collected Dues</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>fraud_checker.php" class="nav-link <?= $current_page == 'fraud_checker.php' ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                                <span class="nav-text">Fraud Intelligence</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Inventory Section -->
            <div class="sidebar-section">
                <span class="section-label">Inventory Control</span>
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?= $base_url ?>inventory/index.php" class="nav-link <?= ($is_inventory && in_array($current_page, ['index.php', 'inventory.php', 'edit.php', 'edit_item.php'])) ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg></div>
                                <span class="nav-text">Products List</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>inventory/stock_report.php" class="nav-link <?= ($is_inventory && in_array($current_page, ['stock_report.php'])) ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
                                <span class="nav-text">Stock Health</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>inventory/add.php" class="nav-link <?= ($is_inventory && in_array($current_page, ['add.php', 'add_item.php'])) ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
                                <span class="nav-text">Register Product</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>inventory/purchase.php" class="nav-link <?= ($is_inventory && in_array($current_page, ['purchase.php'])) ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg></div>
                                <span class="nav-text">Stock Purchase</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Contacts Section -->
            <div class="sidebar-section">
                <span class="section-label">Contact Management</span>
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?= $base_url ?>suppliers.php" class="nav-link <?= strpos($current_page, 'supplier') !== false ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
                                <span class="nav-text">Suppliers</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>customers.php" class="nav-link <?= strpos($current_page, 'customer') !== false ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
                                <span class="nav-text">Customers</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Administration Section -->
            <div class="sidebar-section">
                <span class="section-label">Administration</span>
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?= $base_url ?>users.php" class="nav-link <?= strpos($current_page, 'user') !== false ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg></div>
                                <span class="nav-text">Staff Members</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>profile.php" class="nav-link <?= $current_page == 'profile.php' ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                                <span class="nav-text">My Profile</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>settings.php" class="nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
                                <div class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg></div>
                                <span class="nav-text">System Settings</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    z-index: 999;
    background: var(--bg-surface);
    border-right: 1px solid var(--border-color);
    transition: width var(--transition), transform var(--transition);
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    min-height: var(--navbar-height);
    display: flex;
    align-items: center;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
}

.s-brand-logo { height: 28px; width: auto; border-radius: 6px; }
.s-brand-text { font-size: 0.85rem; font-weight: 800; letter-spacing: 0.05em; color: var(--text-primary); line-height: 1.3; }

.sidebar-wrapper {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem 0.75rem;
}

/* Custom Thin Scrollbar */
.sidebar-wrapper::-webkit-scrollbar { width: 4px; }
.sidebar-wrapper::-webkit-scrollbar-track { background: transparent; }
.sidebar-wrapper::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
.sidebar-wrapper:hover::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); }

.sidebar-content { display: flex; flex-direction: column; gap: 1.5rem; }

.section-label { 
    display: block;
    font-size: 0.65rem; 
    font-weight: 800; 
    text-transform: uppercase; 
    color: var(--text-dim); 
    letter-spacing: 0.1em;
    padding: 0 0.75rem;
    margin-bottom: 0.75rem;
}

.nav-list { list-style: none; display: flex; flex-direction: column; gap: 0.3rem; }
.nav-link { 
    display: flex; 
    align-items: center; 
    gap: 0.75rem; 
    padding: 0.75rem 1rem; 
    color: var(--text-secondary); 
    text-decoration: none; 
    border-radius: 12px; 
    transition: 0.2s; 
    font-weight: 500; 
    font-size: 0.9rem; 
}
.nav-link:hover { background: rgba(255,255,255,0.04); color: var(--text-primary); }
.nav-link.active { background: rgba(139, 92, 246, 0.12); color: var(--accent-primary); border: 1px solid rgba(139, 92, 246, 0.1); }
.nav-icon { width: 20px; color: var(--text-dim); display: flex; align-items: center; justify-content: center; }
.nav-link.active .nav-icon { color: var(--accent-primary); }

/* Responsive Behavior */
.btn-icon-sm { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-dim); cursor: pointer; transition: 0.2s; }
.btn-icon-sm:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

@media (min-width: 1025px) {
    .sidebar { transform: translateX(0) !important; width: var(--sidebar-width); }
}

@media (max-width: 1024px) {
    .sidebar { transform: translateX(-100%); width: 280px; top: 0; height: 100vh; z-index: 2000; box-shadow: -10px 0 30px rgba(0,0,0,0.5); }
    .sidebar.active { transform: translateX(0); }
}
@media (max-width: 768px) {
    .sidebar-header { justify-content: space-between; }
}
</style>