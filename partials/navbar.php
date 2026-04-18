<nav class="navbar glass animate-fade-in">
    <div class="navbar-container">
        <div class="navbar-left">
            <button id="sidebarToggle" class="btn-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <a href="dashboard.php" class="navbar-brand">
                <span class="brand-text">BACKBENCHERS</span>
            </a>
        </div>
        
        <div class="navbar-end">
            <div class="user-profile">
                <div class="user-details">
                    <span class="u-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></span>
                    <span class="u-role">Administrator</span>
                </div>
                <div class="u-avatar">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'G', 0, 1)) ?>
                </div>
            </div>
            <div class="nav-divider"></div>
            <a href="logout.php" class="logout-link" title="Logout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </a>
        </div>
    </div>
</nav>

<style>
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--navbar-height);
    z-index: 1000;
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.navbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn-icon {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
}

.btn-icon:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
}

.navbar-brand {
    text-decoration: none;
}

.brand-text {
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: var(--text-primary);
}

.navbar-end {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.u-name {
    font-size: 0.85rem;
    font-weight: 600;
}

.u-role {
    font-size: 0.65rem;
    color: var(--text-muted);
    text-transform: uppercase;
}

.u-avatar {
    width: 36px;
    height: 36px;
    background: var(--accent-primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    color: white;
}

.nav-divider {
    width: 1px;
    height: 24px;
    background: var(--border-color);
}

.logout-link {
    color: var(--text-dim);
    transition: var(--transition);
}

.logout-link:hover {
    color: var(--danger);
}

@media (max-width: 768px) {
    .user-details { display: none; }
}
</style>