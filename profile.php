<?php
require 'auth.php';
require 'config.php';

$id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, username, full_name, email, image FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email     = $_POST['email']     ?? '';
    $curr_pass = $_POST['current_password'] ?? '';
    $new_pass  = $_POST['new_password'] ?? '';
    
    // 1. Basic Info Update
    $image_path = $user['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = 'user_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
            if ($image_path && file_exists($image_path)) unlink($image_path);
            $image_path = $new_path;
        }
    }
    
    $stmt = $pdo->prepare('UPDATE users SET full_name = ?, email = ?, image = ? WHERE id = ?');
    $stmt->execute([$full_name, $email, $image_path, $id]);
    $success_msg = "Identity parameters synchronized.";

    // 2. Password Change Logic
    if (!empty($new_pass)) {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $db_user = $stmt->fetch();
        
        if (password_verify($curr_pass, $db_user['password'])) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hashed, $id]);
            $success_msg = "Credentials and Identity synchronized successfully.";
        } else {
            $error_msg = "Security verification failed: Current credentials incorrect.";
        }
    }
    
    // Refresh user data
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
}

function getAvatarProfile($user) {
    if (!empty($user['image']) && file_exists($user['image'])) return $user['image'];
    $name = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=8b5cf6&color=fff&size=512&bold=true";
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Personal Control Workspace';
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
                        <h1 class="view-title">My Workspace</h1>
                        <p class="view-subtitle">Management of personal credentials, communication channels and digital assets</p>
                    </div>
                </div>

                <div class="profile-layout">
                    <!-- Sidebar: Perception & Status -->
                    <div class="profile-side">
                        <div class="card person-badge glass">
                            <div class="avatar-framework">
                                <div class="p-ring"></div>
                                <img src="<?= getAvatarProfile($user) ?>" alt="Operator" class="p-avatar">
                            </div>
                            <div class="p-identity">
                                <h2 class="p-full-name"><?= htmlspecialchars($user['full_name'] ?: 'Unit-00') ?></h2>
                                <span class="p-handle">@<?= htmlspecialchars($user['username']) ?></span>
                                <div class="node-status">
                                    <span class="status-indicator"></span>
                                    Node Active
                                </div>
                            </div>
                        </div>

                        <div class="card security-brief glass">
                            <h3 class="brief-title">Access Overview</h3>
                            <div class="brief-item">
                                <span class="b-label">Clearance</span>
                                <span class="b-val text-premium">Super-Admin</span>
                            </div>
                            <div class="brief-item">
                                <span class="b-label">Session ID</span>
                                <span class="b-val"><?= substr(session_id(), 0, 8) ?>...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Main Settings Node -->
                    <div class="profile-main">
                        <div class="card form-card glass">
                            <form method="POST" action="" class="intelligence-form" enctype="multipart/form-data">
                                
                                <div class="form-section">
                                    <h3 class="section-title">Identity Parameters</h3>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Display Name</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            <input type="text" name="full_name" class="form-control pl-icon" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group flex-1">
                                            <label class="form-label">Private Email Channel</label>
                                            <div class="input-icon-wrapper">
                                                <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                                <input type="email" name="email" class="form-control pl-icon" value="<?= htmlspecialchars($user['email']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group flex-1">
                                            <label class="form-label">System Handle (Fixed)</label>
                                            <div class="input-icon-wrapper">
                                                <span class="field-symbol">@</span>
                                                <input type="text" class="form-control pl-symbol readonly-field" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-divider"></div>

                                <div class="form-section">
                                    <h3 class="section-title">Security Synchronization</h3>
                                    <div class="form-row">
                                        <div class="form-group flex-1">
                                            <label class="form-label">New Credential (Password)</label>
                                            <div class="input-icon-wrapper">
                                                <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                                <input type="password" name="new_password" class="form-control pl-icon" placeholder="••••••••">
                                            </div>
                                        </div>
                                        <div class="form-group flex-1">
                                            <label class="form-label">Authentication Challenge (Current)</label>
                                            <div class="input-icon-wrapper">
                                                <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                                <input type="password" name="current_password" class="form-control pl-icon" placeholder="Verify to save">
                                            </div>
                                        </div>
                                    </div>
                                    <span class="input-hint">Security challenge is required for any credential or identity modification.</span>
                                </div>

                                <div class="form-divider"></div>

                                <div class="form-section">
                                    <h3 class="section-title">Digital Avatar Asset</h3>
                                    <div class="image-management glass">
                                        <div class="upload-trigger" onclick="document.getElementById('profileInput').click()">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                            <div class="upload-text">
                                                <span id="fileNameDisplay">Upload Professional Profile Asset</span>
                                                <span class="file-hint">JPG, PNG or WEBP (Standard Square ratio)</span>
                                            </div>
                                            <input type="file" name="image" id="profileInput" style="display: none;" accept="image/*" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0].name">
                                        </div>
                                    </div>
                                </div>

                                <?php if ($success_msg): ?>
                                    <div class="system-success-card animate-fade-in"><?= $success_msg ?></div>
                                <?php endif; ?>
                                <?php if ($error_msg): ?>
                                    <div class="system-error-card animate-shake"><?= $error_msg ?></div>
                                <?php endif; ?>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">Synchronize Pulse</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .inventory-view { padding-bottom: 4rem; }
    .view-header { margin-bottom: 2.5rem; text-align: center; }
    .view-title { font-size: 2.22rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem; }
    .view-subtitle { color: var(--text-dim); font-size: 1.05rem; }

    .profile-layout { display: grid; grid-template-columns: 340px 1fr; gap: 2rem; max-width: 1100px; margin: 0 auto; align-items: start; }
    
    .profile-side { display: flex; flex-direction: column; gap: 2rem; }
    .person-badge { padding: 3rem 2rem; border-radius: 28px; text-align: center; border: 1px solid var(--border-color); }
    .avatar-framework { position: relative; width: 140px; height: 140px; margin: 0 auto 2.5rem; }
    .p-ring { position: absolute; inset: -6px; border-radius: 46px; border: 2.5px solid var(--accent-primary); opacity: 0.3; }
    .p-avatar { position: relative; width: 100%; height: 100%; object-fit: cover; border-radius: 40px; border: 5px solid #0f1120; z-index: 1; }

    .p-full-name { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.3rem; }
    .p-handle { font-size: 1rem; color: var(--accent-primary); font-weight: 700; display: block; margin-bottom: 1.5rem; }
    .node-status { display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.4rem 1rem; border-radius: 100px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-indicator { width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 10px #10b981; }

    .security-brief { padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); }
    .brief-title { font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1.25rem; }
    .brief-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; }
    .b-label { font-size: 0.8rem; color: var(--text-dim); font-weight: 600; }
    .b-val { font-size: 0.85rem; color: var(--text-primary); font-weight: 700; }
    .text-premium { color: var(--accent-primary); }

    /* Form Styles */
    .form-card { padding: 3rem; border-radius: 28px; border: 1px solid var(--border-color); }
    .form-section { margin-bottom: 2rem; }
    .section-title { font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 1.5rem; }

    .form-group { margin-bottom: 1.5rem; }
    .form-row { display: flex; gap: 1.5rem; }
    .flex-1 { flex: 1; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; }
    
    .input-icon-wrapper { position: relative; }
    .field-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }
    .field-symbol { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--accent-primary); font-weight: 800; font-size: 1.1rem; pointer-events: none; }
    
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1.25rem; color: white; transition: 0.25s; font-size: 0.95rem; }
    .pl-icon { padding-left: 3.5rem !important; }
    .pl-symbol { padding-left: 3rem !important; }
    .readonly-field { background: rgba(255, 255, 255, 0.03) !important; color: #94a3b8 !important; cursor: not-allowed; border-style: dashed; }
    .form-control:focus:not(.readonly-field) { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    
    .input-hint { font-size: 0.75rem; color: var(--text-dim); margin-top: 0.6rem; display: block; line-height: 1.4; }
    .form-divider { height: 1px; background: linear-gradient(to right, transparent, var(--border-color), transparent); margin: 2rem 0; }

    .image-management { display: flex; align-items: center; gap: 2rem; padding: 1.5rem; border-radius: 18px; border: 1.5px dashed var(--border-color); background: rgba(0,0,0,0.1); }
    .upload-trigger { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-dim); transition: 0.2s; }
    .upload-trigger:hover { color: var(--accent-primary); transform: translateY(-2px); }
    .upload-text { display: flex; flex-direction: column; align-items: center; text-align: center; }
    #fileNameDisplay { font-weight: 800; font-size: 0.9rem; }
    .file-hint { font-size: 0.7rem; opacity: 0.6; }

    .system-success-card { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 14px; margin-top: 1.5rem; font-size: 0.9rem; text-align: center; font-weight: 700; }
    .system-error-card { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 1rem; border-radius: 14px; margin-top: 1.5rem; font-size: 0.9rem; text-align: center; font-weight: 700; }

    .form-footer { margin-top: 3rem; display: flex; justify-content: flex-end; }
    .btn { padding: 1rem 2.5rem; font-weight: 800; border-radius: 14px; transition: 0.25s; cursor: pointer; border: none; }
    .btn-primary { background: var(--accent-primary); color: white; box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3); }
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(139, 92, 246, 0.4); }

    @media (max-width: 1000px) {
        .profile-layout { grid-template-columns: 1fr; }
        .form-row { flex-direction: column; gap: 1.5rem; }
    }
    </style>
</body>
</html>
