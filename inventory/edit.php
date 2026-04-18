<?php
$base_url = '../';
require '../auth.php';
require '../config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit(); }

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { header('Location: index.php'); exit(); }

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['item_name']   ?? '';
    $desc  = $_POST['description'] ?? '';
    
    $image_path = $item['image']; // Keep old image by default
    
    // Handle Image Upload/Replacement
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir_physical = '../assets/uploads/products/';
        if (!is_dir($upload_dir_physical)) mkdir($upload_dir_physical, 0777, true);
        
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . uniqid() . '.' . $extension;
        $new_path = $upload_dir_physical . $filename;
        $db_path = 'assets/uploads/products/' . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
            $old_file_physical = (strpos($image_path, '../') === 0) ? $image_path : '../' . ltrim($image_path, '/');
            if ($image_path && file_exists($old_file_physical)) {
                unlink($old_file_physical);
            }
            $image_path = $db_path;
        }
    }
    
    $stmt  = $pdo->prepare('UPDATE products SET item_name = ?, description = ?, image = ? WHERE id = ?');
    if ($stmt->execute([$name, $desc, $image_path, $id])) {
        $success = true;
        header('Location: index.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Product Re-Engineering';
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
                        <h1 class="view-title">Modify Product Profile</h1>
                        <p class="view-subtitle">Updating identity for SKU: #<?= str_pad($item['id'], 3, '0', STR_PAD_LEFT) ?></p>
                    </div>
                </div> -->

                <div class="form-container">
                    <div class="card form-card glass">
                        <form method="POST" action="" class="intelligence-form" enctype="multipart/form-data">
                            
                            <div class="form-section">
                                <h3 class="section-title">Core Identity</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Product Name / Model</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                        <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($item['item_name']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Technical Description</label>
                                    <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($item['description']) ?></textarea>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Financial & Velocity Metrics (Read-Only)</h3>
                                <div class="stats-mini-grid">
                                    <div class="mini-stat-box glass">
                                        <span class="mini-label">System Stock</span>
                                        <h4 class="mini-val"><?= $item['quantity'] ?> Units</h4>
                                    </div>
                                    <div class="mini-stat-box glass">
                                        <span class="mini-label">Unit Price</span>
                                        <h4 class="mini-val">৳<?= number_format($item['price'], 2) ?></h4>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 1rem;">
                                    <label class="form-label">Valuation in Words</label>
                                    <p id="priceText" class="valuation-card"><?= $item['price'] ?></p>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Media Modification</h3>
                                
                                <div class="image-management glass">
                                    <?php 
                                        $img_src = '';
                                        if (!empty($item['image'])) {
                                            $img_src = strpos($item['image'], 'http') === 0 ? $item['image'] : $base_url . ltrim(str_replace('../', '', $item['image']), '/');
                                        }
                                    ?>
                                    <?php if ($img_src): ?>
                                        <div class="current-avatar">
                                            <div class="avatar-wrap">
                                                <img src="<?= htmlspecialchars($img_src) ?>" alt="">
                                            </div>
                                            <span class="avatar-hint">Active Profile</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="upload-trigger" onclick="document.getElementById('imageInput').click()">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                        <div class="upload-text">
                                            <span id="fileNameDisplay">Replace Asset</span>
                                            <span class="file-hint">Max size: 2MB</span>
                                        </div>
                                        <input type="file" name="image" id="imageInput" style="display: none;" accept="image/*" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0].name">
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <div class="disclaimer-note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    Metrics such as stock levels and pricing are locked here to maintain audit integrity; update them via the Purchase terminal.
                                </div>
                                <div class="action-buttons">
                                    <a href="index.php" class="btn btn-outline">Discard Changes</a>
                                    <button type="submit" class="btn btn-primary">Commit Updates</button>
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
    .view-title { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem; }
    .view-subtitle { color: var(--text-dim); font-size: 0.95rem; }

    .form-container { max-width: 650px; margin: 0 auto; }
    .form-card { padding: 3rem; border-radius: 28px; border: 1px solid var(--border-color); }
    
    .form-section { margin-bottom: 2rem; }
    .section-title { font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 1.5rem; }

    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; }
    
    .input-icon-wrapper { position: relative; }
    .field-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }
    
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1.25rem; color: white; transition: 0.25s; font-size: 0.95rem; }
    .input-icon-wrapper .form-control { padding-left: 3.5rem; }
    .form-control:focus { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    
    .stats-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .mini-stat-box { padding: 1.25rem; border-radius: 16px; border: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02); }
    .mini-label { font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 0.4rem; }
    .mini-val { font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin: 0; }
    
    .valuation-card { font-size: 0.82rem; color: var(--accent-primary); font-weight: 700; background: rgba(139, 92, 246, 0.05); padding: 1rem; border-radius: 12px; border: 1px dashed rgba(139, 92, 246, 0.3); }

    .form-divider { height: 1px; background: linear-gradient(to right, transparent, var(--border-color), transparent); margin: 2rem 0; }

    .image-management { display: flex; align-items: center; gap: 2rem; padding: 1.5rem; border-radius: 18px; border: 1.5px dashed var(--border-color); background: rgba(0,0,0,0.1); }
    .current-avatar { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
    .avatar-wrap { width: 80px; height: 80px; border-radius: 16px; overflow: hidden; border: 2px solid var(--accent-primary); box-shadow: 0 0 20px rgba(139, 92, 246, 0.2); }
    .avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-hint { font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; font-weight: 800; }

    .upload-trigger { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-dim); transition: 0.2s; }
    .upload-trigger:hover { color: var(--accent-primary); transform: translateY(-2px); }
    .upload-text { display: flex; flex-direction: column; align-items: center; }
    #fileNameDisplay { font-weight: 800; font-size: 0.95rem; }
    .file-hint { font-size: 0.7rem; opacity: 0.6; }

    .form-footer { margin-top: 3rem; display: flex; flex-direction: column; gap: 2rem; }
    .disclaimer-note { display: flex; align-items: center; gap: 0.8rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 14px; font-size: 0.78rem; color: var(--text-dim); border: 1px solid var(--border-color); line-height: 1.4; }
    .action-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
    
    .btn { padding: 1rem 2rem; font-weight: 700; border-radius: 14px; }
    .btn-outline:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    @media (max-width: 600px) {
        .form-card { padding: 1.5rem; }
        .action-buttons { flex-direction: column; }
        .image-management { flex-direction: column; gap: 1.5rem; }
    }
    </style>

    <script>
    function numberToWords(number) {
        const units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        const scales = ['', 'Thousand', 'Million', 'Billion'];

        if (number == 0) return 'Zero Taka Only';
        let parts = parseFloat(number).toFixed(2).split('.');
        let integerPart = parts[0];
        let decimalPart = parts[1];
        let words = '';

        function convertThreeDigit(num) {
            let s = '';
            if (num >= 100) { s += units[Math.floor(num / 100)] + ' Hundred '; num %= 100; }
            if (num >= 20) { s += tens[Math.floor(num / 10)] + ' '; num %= 10; }
            if (num > 0) { s += units[num] + ' '; }
            return s;
        }

        let num = parseInt(integerPart);
        let scaleIdx = 0;
        while (num > 0) {
            let chunk = num % 1000;
            if (chunk > 0) { words = convertThreeDigit(chunk) + scales[scaleIdx] + ' ' + words; }
            num = Math.floor(num / 1000);
            scaleIdx++;
        }
        words = words.trim() + ' Taka';
        let paisa = parseInt(decimalPart);
        if (paisa > 0) { words += ' and ' + convertThreeDigit(paisa).trim() + ' Paisa'; }
        return words + ' Only';
    }

    const pTx = document.getElementById('priceText');
    if (pTx) pTx.textContent = numberToWords(parseFloat(pTx.textContent));
    </script>
</body>
</html>