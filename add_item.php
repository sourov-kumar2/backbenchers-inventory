<?php
require 'auth.php';
require 'config.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['item_name']    ?? '';
    $desc  = $_POST['description']  ?? '';
    
    // Handle Image Upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/products/';
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . uniqid() . '.' . $extension;
        $image_path = $upload_dir . $filename;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image_path = null;
        }
    }
    
    // Default quantity and price are 0 on registration
    $stmt  = $pdo->prepare('INSERT INTO products (item_name, description, quantity, price, image) VALUES (?, ?, 0, 0, ?)');
    if ($stmt->execute([$name, $desc, $image_path])) {
        $success = true;
        header('Location: inventory.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Add Inventory';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Register Item</h1>
                    <p class="text-muted">Populate the primary details. Stock and pricing are managed via Purchases.</p>
                </div>
                <div class="header-actions">
                    <a href="inventory.php" class="btn btn-outline">
                        Cancel Entry
                    </a>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="alert-success animate-fade-in">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Record committed successfully. Redirecting...</span>
                </div>
            <?php endif; ?>

            <div class="form-container animate-fade-in" style="animation-delay: 0.1s">
                <div class="card form-card glass">
                    <form method="POST" action="" class="glass-form" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group span-2">
                                <label class="form-label">Product Name / Model</label>
                                <input type="text" name="item_name" class="form-control" placeholder="e.g. Master CPU Core i9" required autofocus>
                            </div>
                            
                            <div class="form-group span-2">
                                <label class="form-label">Technical Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Technical specifications and notes..."></textarea>
                            </div>

                            <div class="form-group span-2">
                                <label class="form-label">Product Imagery</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                                    <span class="input-hint">Formats: JPG, PNG, WEBP (Max 2MB)</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Initialize Product</button>
                            <span class="info-note">Note: Stock and Pricing will be added through the Purchase module.</span>
                        </div>
                    </form>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .form-container { max-width: 800px; margin: 0 auto; }
    .form-card { padding: 2.5rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .span-2 { grid-column: span 2; }
    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; }
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.8rem 1rem; color: white; transition: 0.2s; }
    .form-control:focus { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    .input-hint { font-size: 0.75rem; color: var(--text-dim); margin-top: 0.5rem; display: block; }

    .form-actions { display: flex; align-items: center; gap: 1.5rem; margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--border-color); }
    .info-note { font-size: 0.8rem; color: var(--text-dim);-    font-style: italic; }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-card { padding: 1.5rem; }
    }
    </style>
</body>
</html>