<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar glass">
    <div class="sidebar-content">
        <!-- Dashboard Section -->
        <div class="sidebar-section">
            <span class="section-label">Overview</span>
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
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

        <!-- Inventory Section -->
        <div class="sidebar-section">
            <span class="section-label">Inventory Control</span>
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="inventory.php" class="nav-link <?= ($current_page == 'inventory.php' || $current_page == 'edit_item.php') ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                </svg>
                            </div>
                            <span class="nav-text">Products List</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="stock_report.php" class="nav-link <?= $current_page == 'stock_report.php' ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <span class="nav-text">Stock Health</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="add_item.php" class="nav-link <?= $current_page == 'add_item.php' ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </div>
                            <span class="nav-text">Register Product</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="purchase.php" class="nav-link <?= $current_page == 'purchase.php' ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="20" y1="8" x2="20" y2="14"></line>
                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                </svg>
                            </div>
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
                        <a href="suppliers.php" class="nav-link <?= strpos($current_page, 'supplier') !== false ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            </div>
                            <span class="nav-text">Suppliers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="customers.php" class="nav-link <?= strpos($current_page, 'customer') !== false ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <span class="nav-text">Customers</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Sales Section -->
        <div class="sidebar-section">
            <span class="section-label">Sales & POS</span>
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="pos.php" class="nav-link <?= $current_page == 'pos.php' ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <path d="M7 15h.01"></path>
                                    <path d="M11 15h.01"></path>
                                    <path d="M15 15h.01"></path>
                                    <path d="M7 11h.01"></path>
                                    <path d="M11 11h.01"></path>
                                    <path d="M15 11h.01"></path>
                                </svg>
                            </div>
                            <span class="nav-text">POS Terminal</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="sales_list.php" class="nav-link <?= $current_page == 'sales_list.php' ? 'active' : '' ?>">
                            <div class="nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </div>
                            <span class="nav-text">Sales History</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</aside>

<style>
.sidebar {
    position: fixed;
    top: var(--navbar-height);
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    z-index: 999;
    padding: 1.5rem 0.75rem;
    border-right: 1px solid var(--border-color);
    transition: width var(--transition), transform var(--transition);
}

.sidebar-content {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.sidebar-section {
    display: flex;
    flex-direction: column;
}

.section-label {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-dim);
    margin-bottom: 0.75rem;
    padding-left: 0.75rem;
    letter-spacing: 0.1em;
    opacity: 0.8;
}

.nav-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: var(--radius-md);
    transition: var(--transition);
    font-weight: 500;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.04);
    color: var(--text-primary);
}

.nav-link.active {
    background: rgba(139, 92, 246, 0.12);
    color: var(--accent-primary);
    border: 1px solid rgba(139, 92, 246, 0.1);
}

.nav-link.active .nav-icon {
    color: var(--accent-primary);
}

.nav-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    color: var(--text-dim);
    transition: var(--transition);
}

.nav-text {
    font-size: 0.85rem;
}

@media (max-width: 1024px) {
    .sidebar { width: var(--sidebar-collapsed-width); }
    .nav-text, .section-label { display: none; }
    .nav-link { justify-content: center; padding: 1rem; }
}

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.active { transform: translateX(0); }
}
</style>