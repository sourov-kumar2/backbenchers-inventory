<?php
require 'auth.php';
require 'config.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_name    = $_POST['system_name']    ?? '';
    $system_details = $_POST['system_details'] ?? '';
    $fraud_api_key  = $_POST['fraud_api_key']  ?? '';
    $groq_api_key   = $_POST['groq_api_key']   ?? '';
    $groq_model     = $_POST['groq_model']     ?? 'llama-3.3-70b-versatile';
    
    $logo_path = $sys['system_logo'];
    
    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/branding/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $extension;
        $new_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $new_path)) {
            if ($logo_path && file_exists($logo_path)) unlink($logo_path);
            $logo_path = $new_path;
        }
    }
    
    $stmt = $pdo->prepare("UPDATE system_settings SET system_name = ?, system_logo = ?, system_details = ?, fraud_api_key = ?, groq_api_key = ?, groq_model = ? WHERE id = 1");
    if ($stmt->execute([$system_name, $logo_path, $system_details, $fraud_api_key, $groq_api_key, $groq_model])) {
        header('Location: settings.php?success=global_synced');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Global Systems Configuration';
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
                        <h1 class="view-title">System Configuration</h1>
                        <p class="view-subtitle">Management of global branding, integrations, and institutional identity</p>
                    </div>
                </div>

                <div class="settings-layout">
                    <!-- Global Configuration Form -->
                    <div class="settings-core">
                        <div class="card form-card glass">
                            <form method="POST" action="" class="intelligence-form" enctype="multipart/form-data">
                                
                                <div class="form-section">
                                    <h3 class="section-title">Brand Identity</h3>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Institutional Name</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                            <input type="text" name="system_name" class="form-control pl-icon" value="<?= htmlspecialchars($sys['system_name']) ?>" required placeholder="e.g. Backbenchers Inventory">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">System Catchphrase / Catch-all Details</label>
                                        <textarea name="system_details" class="form-control" rows="3" placeholder="Describe your institutional focus..."><?= htmlspecialchars($sys['system_details']) ?></textarea>
                                    </div>
                                </div>

                                <div class="form-divider"></div>

                                <div class="form-section">
                                    <h3 class="section-title">Security & Integrations</h3>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Fraud Checker Master API Key</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                                            <input type="password" name="fraud_api_key" class="form-control pl-icon" value="<?= htmlspecialchars($sys['fraud_api_key']) ?>" placeholder="your_private_api_key">
                                        </div>
                                        <span class="input-hint">Synchronized with fraudbd.com for automated courier reputation auditing.</span>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Groq AI Intelligence Key</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path><path d="M12 22V12"></path></svg>
                                            <input type="password" name="groq_api_key" class="form-control pl-icon" value="<?= htmlspecialchars($sys['groq_api_key'] ?? '') ?>" placeholder="gsk_...">
                                        </div>
                                        <span class="input-hint">API Key for LLAMA-3 Business Analyst (Generate at groq.com).</span>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">AI Model Preference</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                            <select name="groq_model" class="form-control pl-icon">
                                                <option value="llama-3.3-70b-versatile" <?= ($sys['groq_model'] ?? '') == 'llama-3.3-70b-versatile' ? 'selected' : '' ?>>Llama 3.3 70B (Versatile)</option>
                                                <option value="llama-3.1-8b-instant" <?= ($sys['groq_model'] ?? '') == 'llama-3.1-8b-instant' ? 'selected' : '' ?>>Llama 3.1 8B (Instant)</option>
                                                <option value="mixtral-8x7b-32768" <?= ($sys['groq_model'] ?? '') == 'mixtral-8x7b-32768' ? 'selected' : '' ?>>Mixtral 8x7B</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-divider"></div>

                                <div class="form-section">
                                    <h3 class="section-title">Institutional Assets (Logo)</h3>
                                    
                                    <div class="asset-management glass">
                                        <div class="logo-preview-box">
                                            <div class="l-ring"></div>
                                            <?php if ($sys['system_logo']): ?>
                                                <img src="<?= $sys['system_logo'] ?>" alt="Logo">
                                            <?php else: ?>
                                                <div class="l-void">B</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="upload-trigger" onclick="document.getElementById('logoInput').click()">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                            <div class="upload-text">
                                                <span id="fileNameDisplay">Upload Primary Brandmark</span>
                                                <span class="file-hint">Transparent SVG or PNG (Max 2MB)</span>
                                            </div>
                                            <input type="file" name="logo" id="logoInput" style="display: none;" accept="image/*" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0].name">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-footer">
                                    <div class="disclaimer-note">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        Saving these settings will immediately re-propagate Branding across all system nodes including Navbars and PDF Invoices.
                                    </div>
                                    <div class="action-buttons">
                                        <a href="dashboard.php" class="btn btn-outline">Discard</a>
                                        <button type="submit" class="btn btn-primary">Synchronize Branding</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Live Sidebar Preview -->
                    <div class="settings-sidebar">
                        <div class="card preview-card glass">
                            <h3 class="preview-title">Sidebar Perception Live</h3>
                            <div class="mock-sidebar">
                                <div class="mock-header">
                                    <div class="m-logo">
                                        <?php if ($sys['system_logo']): ?>
                                            <img src="<?= $sys['system_logo'] ?>" alt="">
                                        <?php else: ?>
                                            <span>B</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="m-brand"><?= htmlspecialchars($sys['system_name']) ?></span>
                                </div>
                                <div class="mock-items">
                                    <div class="m-item active"></div>
                                    <div class="m-item"></div>
                                    <div class="m-item"></div>
                                    <div class="m-item"></div>
                                </div>
                            </div>
                            <p class="preview-note">Visual representation of how staff and operators will perceive your institution's brand identity.</p>
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

    .settings-layout { display: grid; grid-template-columns: 1fr 340px; gap: 2rem; max-width: 1100px; margin: 0 auto; align-items: start; }
    
    .form-card { padding: 3rem; border-radius: 28px; border: 1px solid var(--border-color); }
    .form-section { margin-bottom: 2rem; }
    .section-title { font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 1.5rem; }

    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; }
    
    .input-icon-wrapper { position: relative; }
    .field-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }
    
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1.25rem; color: white; transition: 0.25s; font-size: 0.95rem; }
    .pl-icon { padding-left: 3.5rem !important; }
    .form-control:focus { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    
    .input-hint { font-size: 0.75rem; color: var(--text-dim); margin-top: 0.6rem; display: block; line-height: 1.4; }
    .form-divider { height: 1px; background: linear-gradient(to right, transparent, var(--border-color), transparent); margin: 2rem 0; }

    /* Asset Management */
    .asset-management { display: flex; align-items: center; gap: 2rem; padding: 1.5rem; border-radius: 18px; border: 1.5px dashed var(--border-color); background: rgba(0,0,0,0.1); }
    .logo-preview-box { position: relative; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: #0f1120; border-radius: 18px; }
    .l-ring { position: absolute; inset: -4px; border-radius: 22px; border: 2px solid var(--accent-primary); opacity: 0.3; }
    .logo-preview-box img { max-width: 50px; max-height: 50px; position: relative; z-index: 1; }
    .l-void { font-size: 1.5rem; font-weight: 900; color: var(--accent-primary); }

    .upload-trigger { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-dim); transition: 0.2s; }
    .upload-trigger:hover { color: var(--accent-primary); transform: translateY(-2px); }
    .upload-text { display: flex; flex-direction: column; align-items: center; text-align: center; }
    #fileNameDisplay { font-weight: 800; font-size: 0.9rem; }
    .file-hint { font-size: 0.7rem; opacity: 0.6; }

    /* Preview Card */
    .preview-card { padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); }
    .preview-title { font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.12em; text-align: center; margin-bottom: 2rem; }
    .mock-sidebar { background: #0f1120; border-radius: 18px; padding: 1.5rem; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 2rem; }
    .mock-header { display: flex; align-items: center; gap: 0.75rem; }
    .m-logo { width: 28px; height: 28px; background: var(--accent-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .m-logo img { width: 16px; height: 16px; object-fit: contain; }
    .m-logo span { color: white; font-weight: 900; font-size: 0.7rem; }
    .m-brand { font-size: 0.8rem; font-weight: 800; color: white; letter-spacing: 0.05em; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mock-items { display: flex; flex-direction: column; gap: 0.8rem; }
    .m-item { height: 14px; border-radius: 6px; background: rgba(255,255,255,0.05); }
    .m-item.active { background: rgba(139, 92, 246, 0.2); border: 1px solid rgba(139, 92, 246, 0.2); }
    .preview-note { margin-top: 1.5rem; font-size: 0.75rem; color: var(--text-muted); text-align: center; line-height: 1.5; }

    .form-footer { margin-top: 3rem; display: flex; flex-direction: column; gap: 2rem; }
    .disclaimer-note { display: flex; align-items: center; gap: 0.8rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 14px; font-size: 0.78rem; color: var(--text-dim); border: 1px solid var(--border-color); line-height: 1.4; }
    .action-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
    
    .btn { padding: 1rem 2rem; font-weight: 700; border-radius: 14px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-outline:hover { background: rgba(255, 255, 255, 0.05); color: var(--text-primary); }

    @media (max-width: 1000px) {
        .settings-layout { grid-template-columns: 1fr; }
        .settings-sidebar { display: none; }
    }
    </style>

    <script>
    <?php if (isset($_GET['success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Global Sync Complete',
        text: 'System Branding and Logic parameters have been successfully re-propagated.',
        confirmButtonColor: 'var(--accent-primary)',
        background: '#1e293b',
        color: '#f8fafc'
    });
    <?php endif; ?>
    </script>
</body>
</html>
