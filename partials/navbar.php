<?php
// Fetch latest user info for real-time profile updates in navbar
$nav_user_id = $_SESSION['user_id'] ?? 0;
$nav_stmt = $pdo->prepare('SELECT full_name, username, email, image FROM users WHERE id = ?');
$nav_stmt->execute([$nav_user_id]);
$nav_user = $nav_stmt->fetch();

$display_name = $nav_user['full_name'] ?: ($nav_user['username'] ?? 'Guest');
$avatar_src = (!empty($nav_user['image']) && file_exists($nav_user['image'])) 
    ? $nav_user['image'] 
    : "https://ui-avatars.com/api/?name=" . urlencode($display_name) . "&background=8b5cf6&color=fff&bold=true";

$brand_name = $sys['system_name'] ?? 'Backbenchers';
$logo_src = $sys['system_logo'] ?? '';
?>
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
            
            <!-- Mobile Brand (Only visible on small screens) -->
            <a href="<?= $base_url ?>dashboard.php" class="mobile-navbar-brand">
                <?php if ($logo_src): ?>
                    <img src="<?= $logo_src ?>" alt="Logo" class="brand-logo-img">
                <?php else: ?>
                    <div class="brand-logo-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--accent-primary)" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="navbar-end">
            <div class="dropdown-container">
                <button class="user-profile-trigger" id="userMenuBtn">
                    <div class="user-details">
                        <span class="u-name"><?= htmlspecialchars($display_name) ?></span>
                        <span class="u-role">Administrator</span>
                    </div>
                    <div class="u-avatar-img">
                        <img src="<?= $avatar_src ?>" alt="Me">
                    </div>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="chevron-icon">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>

                <div class="user-dropdown-menu glass" id="userMenu">
                    <div class="dropdown-header">
                        <div class="header-info">
                            <p class="dropdown-user-name"><?= htmlspecialchars($display_name) ?></p>
                            <p class="dropdown-user-email">@<?= htmlspecialchars($nav_user['username']) ?></p>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <ul class="dropdown-list">
                        <li>
                            <a href="<?= $base_url ?>profile.php" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_url ?>settings.php" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                System Settings
                            </a>
                        </li>
                    </ul>
                    <div class="dropdown-divider"></div>
                    <a href="<?= $base_url ?>logout.php" class="dropdown-item logout-danger">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Logout Session
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.navbar { position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: var(--navbar-height); z-index: 998; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; padding: 0 1.5rem; transition: left var(--transition); background: var(--bg-surface); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
.navbar-container { display: flex; justify-content: space-between; align-items: center; width: 100%; margin: 0 auto; }
    .navbar-left { display: flex; align-items: center; gap: 1.5rem; }
    #sidebarToggle { display: none; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); color: var(--text-primary); padding: 0.5rem; border-radius: 10px; cursor: pointer; transition: 0.2s; align-items: center; justify-content: center; }
    #sidebarToggle:hover { background: var(--accent-primary); border-color: var(--accent-primary); color: white; }

    .mobile-navbar-brand { display: none; align-items: center; text-decoration: none; }
    .brand-logo-img { height: 32px; width: auto; border-radius: 8px; }

    .navbar-end { display: flex; align-items: center; }
    .dropdown-container { position: relative; }
    .user-profile-trigger { background: transparent; border: 1px solid transparent; border-radius: 14px; display: flex; align-items: center; gap: 0.85rem; padding: 0.4rem 0.6rem; cursor: pointer; transition: 0.2s; color: var(--text-primary); outline: none; }
    .user-profile-trigger:hover { background: rgba(255, 255, 255, 0.05); border-color: var(--border-color); }
    .user-details { text-align: right; }
    .u-name { display: block; font-size: 0.85rem; font-weight: 700; }
    .u-role { font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; }
    .u-avatar-img { width: 36px; height: 36px; border-radius: 10px; overflow: hidden; border: 1.5px solid var(--accent-primary); }
    .u-avatar-img img { width: 100%; height: 100%; object-fit: cover; }
    .chevron-icon { color: var(--text-dim); transition: 0.3s; }
    .user-profile-trigger.active .chevron-icon { transform: rotate(180deg); color: var(--accent-primary); }

    .user-dropdown-menu { 
        position: absolute; top: calc(100% + 12px); right: 0; width: 250px; border-radius: 20px; padding: 0.75rem; 
        display: none; transform: translateY(10px); opacity: 0; transition: 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28); z-index: 1001;
        background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }
    .user-dropdown-menu.active { display: block; transform: translateY(0); opacity: 1; }
    .dropdown-header { padding: 0.75rem 0.5rem; }
    .dropdown-user-name { font-weight: 700; color: var(--text-primary); font-size: 1rem; margin-bottom: 0.25rem; }
    .dropdown-user-email { font-size: 0.75rem; color: var(--text-dim); }
    .dropdown-divider { height: 1px; background: var(--border-color); margin: 0.5rem 0; }
    .dropdown-list { list-style: none; }
    .dropdown-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 12px; color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; transition: 0.2s; }
    .dropdown-item:hover { background: rgba(255, 255, 255, 0.06); color: var(--text-primary); }
    .dropdown-item svg { color: var(--text-dim); transition: 0.2s; }
    .dropdown-item:hover svg { color: var(--accent-primary); }
    .logout-danger { color: #fca5a5 !important; margin-top: 0.25rem; }
    .logout-danger:hover { background: rgba(239, 68, 68, 0.1) !important; color: #f87171 !important; }
    .logout-danger svg { color: #fca5a5 !important; }

    @media (max-width: 1024px) {
        .navbar { left: 0; }
        #sidebarToggle { display: flex; }
        .mobile-navbar-brand { display: flex; }
        .user-details { display: none; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('userMenuBtn');
    const menu = document.getElementById('userMenu');

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        trigger.classList.toggle('active');
        menu.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && !trigger.contains(e.target)) {
            trigger.classList.remove('active');
            menu.classList.remove('active');
        }
    });

    // Sidebar Logic (Mobile & Overlay)
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');
    const closeBtn = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if(toggle) toggle.addEventListener('click', openSidebar);
    if(overlay) overlay.addEventListener('click', closeSidebar);
    if(closeBtn) closeBtn.addEventListener('click', closeSidebar);
});
</script>