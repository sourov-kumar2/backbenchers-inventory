<?php
require 'auth.php';
require 'config.php';

// Intelligence Engine: Parsing Operator Network
$stmt = $pdo->query('SELECT id, username, full_name, email, image, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

$success = $_GET['success'] ?? false;
$error = $_GET['error'] ?? false;

function getAvatar($user) {
    if (!empty($user['image']) && file_exists($user['image'])) {
        return $user['image'];
    }
    $name = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=8b5cf6&color=fff&size=256&bold=true";
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Operating System Personnel';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <div class="inventory-view animate-fade-in">
                <!-- Page Header & Onboarding CTA -->
                <div class="view-header">
                    <div class="header-content">
                        <h1 class="view-title">System Operators</h1>
                        <p class="view-subtitle">Management of institutional personnel and access hierarchies</p>
                    </div>
                    <div class="header-actions">
                        <a href="add_user.php" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <line x1="19" y1="8" x2="19" y2="14"></line>
                                <line x1="22" y1="11" x2="16" y2="11"></line>
                            </svg>
                            Onboard New Staff
                        </a>
                    </div>
                </div>

                <div class="users-grid">
                    <?php foreach ($users as $user): ?>
                        <div class="personnel-card glass">
                            <!-- Network Status Badge -->
                            <div class="network-badge">
                                <span class="status-dot"></span>
                                Authorized
                            </div>

                            <div class="card-identity">
                                <div class="avatar-framework">
                                    <div class="avatar-ring"></div>
                                    <img src="<?= getAvatar($user) ?>" alt="Operator" class="operator-avatar">
                                </div>
                                <div class="identity-text">
                                    <h3 class="operator-title"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h3>
                                    <span class="operator-handle">@<?= htmlspecialchars($user['username']) ?></span>
                                </div>
                            </div>

                            <div class="card-details">
                                <div class="detail-row">
                                    <span class="detail-label">Communication</span>
                                    <span class="detail-val"><?= htmlspecialchars($user['email'] ?: 'Internal Routing Only') ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Deployment</span>
                                    <span class="detail-val"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="access-level">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                    Root Access
                                </div>
                                <div class="operator-actions">
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="o-btn edit" title="Modify Clearance">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </a>
                                    <?php if ($user['id'] != 1): ?>
                                        <button onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" class="o-btn revoke" title="Revoke Access">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                                            </svg>
                                        </button>
                                    <?php else: ?>
                                        <div class="locked-node" title="Critical Root Node (Locked)">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .inventory-view { padding-bottom: 4rem; }
    .view-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
    .view-title { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem; }
    .view-subtitle { color: var(--text-dim); font-size: 0.95rem; }

    .users-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; }
    
    .personnel-card { position: relative; padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); overflow: hidden; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .personnel-card:hover { transform: translateY(-8px); border-color: var(--accent-primary); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
    .personnel-card:hover .avatar-ring { opacity: 1; transform: scale(1.1); }
    
    .network-badge { position: absolute; top: 1.5rem; right: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 0.4rem 0.8rem; border-radius: 100px; letter-spacing: 0.05em; }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981; }

    .card-identity { text-align: center; margin-bottom: 2rem; }
    .avatar-framework { position: relative; width: 100px; height: 100px; margin: 0 auto 1.5rem; }
    .avatar-ring { position: absolute; inset: -5px; border-radius: 32px; border: 2px solid var(--accent-primary); opacity: 0.3; transition: 0.4s; z-index: 0; }
    .operator-avatar { position: relative; width: 100%; height: 100%; object-fit: cover; border-radius: 28px; z-index: 1; border: 3px solid #0f1120; background: #1e293b; }
    
    .operator-title { font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem; }
    .operator-handle { font-size: 0.85rem; color: var(--accent-primary); font-weight: 700; letter-spacing: 0.02em; }

    .card-details { background: rgba(0,0,0,0.2); border-radius: 16px; padding: 1.25rem; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.03); }
    .detail-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
    .detail-row:last-child { margin-bottom: 0; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; color: var(--text-dim); font-weight: 800; letter-spacing: 0.05em; }
    .detail-val { font-size: 0.85rem; color: var(--text-primary); font-weight: 600; }

    .card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 1.25rem; border-top: 1px solid var(--border-color); }
    .access-level { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-dim); }
    .operator-actions { display: flex; gap: 0.6rem; }
    
    .o-btn { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--text-dim); background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); transition: 0.25s; cursor: pointer; text-decoration: none; }
    .o-btn:hover { background: rgba(139, 92, 246, 0.1); border-color: var(--accent-primary); color: var(--accent-primary); }
    .o-btn.revoke:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #ef4444; }
    
    .locked-node { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; color: var(--text-dim); opacity: 0.25; cursor: not-allowed; }

    @media (max-width: 600px) {
        .users-grid { grid-template-columns: 1fr; }
    }
    </style>

    <script>
    function confirmDelete(id, username) {
        Swal.fire({
            title: 'Revoke Clearance?',
            text: "User @" + username + " will lose all system access immediately.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: 'rgba(255,255,255,0.1)',
            confirmButtonText: 'Revoke Access',
            background: '#1e293b',
            color: '#f1f5f9'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete_user.php?id=' + id;
            }
        });
    }

    <?php if ($success): ?>
    Swal.fire({ icon: 'success', title: 'Network Update', text: 'Personnel records synchronized successfully.', confirmButtonColor: 'var(--accent-primary)', background: '#1e293b', color: '#f1f5f9' });
    <?php endif; ?>
    </script>
</body>
</html>
