<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: users.php'); exit(); }

$stmt = $pdo->prepare('SELECT id, username, full_name, email, image FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { header('Location: users.php'); exit(); }

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name']  ?? '';
    $email     = $_POST['email']      ?? '';
    $new_pass  = $_POST['new_password'] ?? '';
    
    $image_path = $user['image'];
    
    // Handle Image Update
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . time() . '_' . uniqid() . '.' . $extension;
        $new_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
            if ($image_path && file_exists($image_path)) unlink($image_path);
            $image_path = $new_path;
        }
    }
    
    $sql = "UPDATE users SET full_name = ?, email = ?, image = ?";
    $params = [$full_name, $email, $image_path];
    
    if (!empty($new_pass)) {
        $sql .= ", password = ?";
        $params[] = password_hash($new_pass, PASSWORD_DEFAULT);
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        header('Location: users.php?success=profile_updated');
        exit();
    }
}

function getAvatarEdit($user) {
    if (!empty($user['image']) && file_exists($user['image'])) {
        return $user['image'];
    }
    return "https://ui-avatars.com/api/?name=" . urlencode($user['full_name'] ?: $user['username']) . "&background=8b5cf6&color=fff&size=256&bold=true";
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Registry: Personnel Modification';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <div class="inventory-view animate-fade-in">
                <!-- Header intentionally omitted as per user request -->

                <div class="form-container">
                    <div class="card form-card glass">
                        <form method="POST" action="" class="intelligence-form" enctype="multipart/form-data">
                            
                            <div class="form-section">
                                <h3 class="section-title">Identity & Clearance</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Full Professional Name</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <input type="text" name="full_name" class="form-control pl-icon" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group flex-1">
                                        <label class="form-label">Network Handle (Read-Only)</label>
                                        <div class="input-icon-wrapper">
                                            <span class="field-symbol">@</span>
                                            <input type="text" class="form-control pl-symbol readonly-field" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group flex-1">
                                        <label class="form-label">Active Email</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                            <input type="email" name="email" class="form-control pl-icon" value="<?= htmlspecialchars($user['email']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Security Overrides</h3>
                                <div class="form-group">
                                    <label class="form-label">Reset Credentials (Password)</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        <input type="password" name="new_password" class="form-control pl-icon" placeholder="••••••••">
                                    </div>
                                    <span class="input-hint">Leave field null to preserve current encrypted credentials.</span>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Profile Asset Management</h3>
                                
                                <div class="image-management glass">
                                    <div class="avatar-preview-box">
                                        <div class="p-ring"></div>
                                        <img id="avatarPreview" src="<?= getAvatarEdit($user) ?>" alt="Current Avatar">
                                    </div>
                                    
                                    <div class="upload-trigger" onclick="document.getElementById('imageInput').click()">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        <div class="upload-text">
                                            <span id="fileNameDisplay">Replace Digital Profile Asset</span>
                                            <span class="file-hint">Max file size: 2MB | Best with square aspect ratio</span>
                                        </div>
                                        <input type="file" name="image" id="imageInput" style="display: none;" accept="image/*" onchange="previewProfile(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <div class="disclaimer-note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                    Modifying personnel profiles will impact all historical action logs associated with this @<?= htmlspecialchars($user['username']) ?> node.
                                </div>
                                <div class="action-buttons">
                                    <a href="users.php" class="btn btn-outline">Discard Changes</a>
                                    <button type="submit" class="btn btn-primary">Commit Personnel Updates</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .inventory-view { padding-bottom: 4rem; padding-top: 2rem; }
    .form-container { max-width: 650px; margin: 0 auto; }
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
    .avatar-preview-box { position: relative; width: 80px; height: 80px; }
    .p-ring { position: absolute; inset: -4px; border-radius: 20px; border: 2px solid var(--accent-primary); opacity: 0.3; }
    .avatar-preview-box img { width: 100%; height: 100%; object-fit: cover; border-radius: 18px; position: relative; z-index: 1; border: 2px solid #0f1120; }

    .upload-trigger { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-dim); transition: 0.2s; }
    .upload-trigger:hover { color: var(--accent-primary); transform: translateY(-2px); }
    .upload-text { display: flex; flex-direction: column; align-items: center; text-align: center; }
    #fileNameDisplay { font-weight: 800; font-size: 0.9rem; }
    .file-hint { font-size: 0.7rem; opacity: 0.6; }

    .form-footer { margin-top: 3rem; display: flex; flex-direction: column; gap: 2rem; }
    .disclaimer-note { display: flex; align-items: center; gap: 0.8rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 14px; font-size: 0.78rem; color: var(--text-dim); border: 1px solid var(--border-color); line-height: 1.4; }
    .action-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
    
    .btn { padding: 1rem 2rem; font-weight: 700; border-radius: 14px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: 0.25s; }
    .btn-outline:hover { background: rgba(255, 255, 255, 0.05); color: var(--text-primary); }

    @media (max-width: 600px) {
        .form-card { padding: 1.5rem; }
        .form-row { flex-direction: column; gap: 1.5rem; }
        .action-buttons { flex-direction: column; }
        .image-management { flex-direction: column; gap: 1.5rem; text-align: center; }
    }
    </style>

    <script>
    function previewProfile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
                document.getElementById('fileNameDisplay').textContent = input.files[0].name;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>
