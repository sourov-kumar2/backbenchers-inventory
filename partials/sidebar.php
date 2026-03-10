<aside class="sidebar">
    <div class="sidebar-content">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="inventory.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 2H3v6h6V2z"></path>
                        <path d="M21 2h-6v6h6V2z"></path>
                        <path d="M21 14h-6v6h6v-6z"></path>
                        <path d="M9 14H3v6h6v-6z"></path>
                    </svg>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="add_item.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Add Item</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<style>
.sidebar {
    width: 260px;
    position: fixed;
    top: 60px;
    left: 0;
    background: linear-gradient(180deg, #0f0f1e 0%, #1a1a2e 100%);
    height: calc(100vh - 60px);
    padding: 0;
    z-index: 999;
    border-right: 1px solid rgba(0, 212, 255, 0.1);
    overflow-y: auto;
    box-shadow: 8px 0 24px rgba(0, 0, 0, 0.3);
}

.sidebar-content {
    padding: 30px 0;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    position: relative;
    margin: 8px 0;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px 24px;
    color: #a0a0a0;
    text-decoration: none;
    font-weight: 500;
    font-size: 15px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(0, 212, 255, 0.05);
    transition: left 0.3s ease;
    z-index: -1;
}

.nav-link:hover::before {
    left: 0;
}

.nav-link:hover,
.nav-link.active {
    color: #00d4ff;
    border-left-color: #00d4ff;
    padding-left: 20px;
}

.nav-icon {
    width: 22px;
    height: 22px;
    stroke-width: 2;
    flex-shrink: 0;
}

.nav-link:hover .nav-icon {
    filter: drop-shadow(0 0 6px rgba(0, 212, 255, 0.5));
}

/* Scrollbar styling */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(0, 212, 255, 0.3);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 212, 255, 0.6);
}

@media (max-width: 768px) {
    .sidebar {
        width: 70px;
    }
    
    .sidebar-content {
        padding: 20px 0;
    }
    
    .nav-link {
        padding: 16px 12px;
        justify-content: center;
    }
    
    .nav-link span {
        display: none;
    }
    
    .nav-item {
        margin: 4px 0;
    }
}
</style>