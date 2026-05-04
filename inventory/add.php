<?php
$base_url = '../';
require '../auth.php';
require '../config.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['item_name']    ?? '';
    $desc  = $_POST['description']  ?? '';
    $tags  = $_POST['tags']         ?? '';
    
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
    $stmt  = $pdo->prepare('INSERT INTO products (item_name, description, tags, quantity, price, image) VALUES (?, ?, ?, 0, 0, ?)');
    if ($stmt->execute([$name, $desc, $tags, $image_path])) {
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
                                        <input type="text" name="item_name" id="productName" class="form-control" placeholder="e.g. Master CPU Core i9" required autofocus>
                                        <button type="button" id="aiSuggestBtn" class="ai-magic-btn" title="AI Magic Suggest">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                            </svg>
                                            AI Suggest
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Detailed Description</label>
                                    <textarea name="description" id="productDesc" class="form-control" rows="5" placeholder="Document technical specifications..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Search Keywords / Tags</label>
                                    <input type="text" name="tags" id="productTags" class="form-control" placeholder="electronics, high-end, cpu">
                                    <span class="input-hint">Separated by commas for enhanced catalog searching.</span>
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

    .ai-magic-btn { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); color: var(--accent-primary); padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.8rem; font-weight: 800; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.2s; z-index: 5; }
    .ai-magic-btn:hover { background: var(--accent-primary); color: white; transform: translateY(-50%) scale(1.05); }
    .ai-magic-btn.loading { opacity: 0.7; pointer-events: none; }
    
    .loader-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.6s infinite linear; display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }

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
    <script>
    document.getElementById('aiSuggestBtn').addEventListener('click', function() {
        const name = document.getElementById('productName').value.trim();
        if (!name) {
            Swal.fire({ icon: 'warning', title: 'Product Name Required', text: 'Please enter a product name first so the AI has context.' });
            return;
        }

        const btn = this;
        btn.classList.add('loading');
        btn.innerHTML = `<span class="loader-spinner"></span> Generating...`;

        const formData = new FormData();
        formData.append('product_name', name);

        fetch('../api/inventory_ai_helper.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestion) {
                document.getElementById('productDesc').value = data.suggestion.description;
                document.getElementById('productTags').value = data.suggestion.tags;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Intelligence Synced',
                    text: `AI generated a description and suggests a price of ৳${data.suggestion.suggested_price}. (Pricing must be set during Purchase)`,
                    confirmButtonColor: 'var(--accent-primary)'
                });
            } else {
                let errorHtml = `<p>${data.error || 'The AI could not process this name.'}</p>`;
                if (data.debug_raw) {
                    errorHtml += `<div style="text-align:left; margin-top:10px; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px; font-family:monospace; font-size:11px; max-height:200px; overflow-y:auto; color:#ff9f9f;">
                        <strong>Raw AI Output:</strong><br>${data.debug_raw}
                    </div>`;
                }
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Generation Failed', 
                    html: errorHtml,
                    width: '600px'
                });
            }
        })
        .catch(error => {
            Swal.fire({ icon: 'error', title: 'Connection Error', text: error.message });
        })
        .finally(() => {
            btn.classList.remove('loading');
            btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg> AI Suggest`;
        });
    });
    </script>
</body>
</html>