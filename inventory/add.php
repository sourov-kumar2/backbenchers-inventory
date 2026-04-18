<?php
$base_url = '../';
require '../auth.php';
require '../config.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['item_name']    ?? '';
    $desc  = $_POST['description']  ?? '';
    
    // Handle Image Upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir_physical = '../assets/uploads/products/';
        if (!is_dir($upload_dir_physical)) mkdir($upload_dir_physical, 0777, true);
        
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . uniqid() . '.' . $extension;
        $new_path = $upload_dir_physical . $filename;
        $db_path = 'assets/uploads/products/' . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
            $image_path = $db_path;
        } else {
            $image_path = null;
        }
    }
    
    // Default quantity and price are 0 on registration
    $stmt  = $pdo->prepare('INSERT INTO products (item_name, description, quantity, price, image) VALUES (?, ?, 0, 0, ?)');
    if ($stmt->execute([$name, $desc, $image_path])) {
        $success = true;
        header('Location: index.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Product Engineering';
include '../partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include '../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../partials/navbar.php'; ?>
            
            <div class="inventory-view animate-fade-in">
                <!-- Page Header -->
                <!-- <div class="view-header">
                    <div class="header-content">
                        <h1 class="view-title">Register New Product</h1>
                        <p class="view-subtitle">Initialize a new catalog entry in the central system</p>
                    </div>
                </div> -->

                <div class="form-container">
                    <div class="card form-card glass">
                        <form method="POST" action="" class="intelligence-form" enctype="multipart/form-data">
                            
                            <div class="form-section">
                                <h3 class="section-title">Identity & Specifications</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Product Name / Model</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                        <input type="text" name="item_name" class="form-control" placeholder="e.g. Master CPU Core i9" required autofocus>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Detailed Description</label>
                                    <textarea name="description" class="form-control" rows="5" placeholder="Document technical specifications and hardware notes..."></textarea>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Media Assets</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Product Imagery</label>
                                    <div class="custom-file-upload glass" onclick="document.getElementById('imageInput').click()">
                                        <div class="file-info">
                                            <svg class="upload-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                            <span id="fileNameDisplay">Select Image File</span>
                                            <span class="file-hint">Formats: JPG, PNG, WEBP (Max 2MB)</span>
                                        </div>
                                        <input type="file" name="image" id="imageInput" style="display: none;" accept="image/*" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0].name">
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <div class="disclaimer-note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    Note: Inventory levels and pricing should be managed post-registration via the Purchase module.
                                </div>
                                <div class="action-buttons">
                                    <a href="index.php" class="btn btn-outline">Discard Entry</a>
                                    <button type="submit" class="btn btn-primary">Initialize Product</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .inventory-view { padding-bottom: 4rem; }
    .view-header { margin-bottom: 2.5rem; text-align: center; }
    .view-title { font-size: 2.22rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem; }
    .view-subtitle { color: var(--text-dim); font-size: 1.05rem; }

    .form-container { max-width: 650px; margin: 0 auto; }
    .form-card { padding: 3rem; border-radius: 28px; border: 1px solid var(--border-color); }
    
    .form-section { margin-bottom: 2rem; }
    .section-title { font-size: 0.85rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1.5rem; }

    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; }
    
    .input-icon-wrapper { position: relative; }
    .field-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }
    
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1.25rem; color: white; transition: 0.25s; font-size: 0.95rem; }
    .input-icon-wrapper .form-control { padding-left: 3.5rem; }
    .form-control:focus { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    
    .form-divider { height: 1px; background: linear-gradient(to right, transparent, var(--border-color), transparent); margin: 2rem 0; }

    .custom-file-upload { border: 2px dashed var(--border-color); border-radius: 16px; padding: 2.5rem; text-align: center; cursor: pointer; transition: 0.2s; }
    .custom-file-upload:hover { border-color: var(--accent-primary); background: rgba(139, 92, 246, 0.05); }
    
    .file-info { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
    .upload-icon { color: var(--accent-primary); opacity: 0.8; }
    #fileNameDisplay { font-weight: 700; color: var(--text-primary); font-size: 0.95rem; }
    .file-hint { font-size: 0.75rem; color: var(--text-dim); }

    .form-footer { margin-top: 3rem; display: flex; flex-direction: column; gap: 2rem; }
    .disclaimer-note { display: flex; align-items: center; gap: 0.8rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 14px; font-size: 0.8rem; color: var(--text-dim); border: 1px solid var(--border-color); line-height: 1.4; }
    .action-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
    
    .btn { padding: 1rem 2rem; }
    .btn-outline:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    @media (max-width: 600px) {
        .form-card { padding: 1.5rem; }
        .action-buttons { flex-direction: column; }
    }
    </style>
</body>
</html>