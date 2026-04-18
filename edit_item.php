<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: inventory.php'); exit(); }

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { header('Location: inventory.php'); exit(); }

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['item_name']   ?? '';
    $desc  = $_POST['description'] ?? '';
    
    // Note: quantity and price are no longer updatable here.
    // They are updated through the Purchase module.
    
    $image_path = $item['image']; // Keep old image by default
    
    // Handle Image Upload/Replacement
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/products/';
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . uniqid() . '.' . $extension;
        $new_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
            // Delete old image if exists
            if ($image_path && file_exists($image_path)) {
                unlink($image_path);
            }
            $image_path = $new_path;
        }
    }
    
    $stmt  = $pdo->prepare('UPDATE products SET item_name = ?, description = ?, image = ? WHERE id = ?');
    if ($stmt->execute([$name, $desc, $image_path, $id])) {
        $success = true;
        header('Location: inventory.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Edit Inventory';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Edit Record</h1>
                    <p class="text-muted">Modifying identity for <span class="active-item">#<?= str_pad($item['id'], 3, '0', STR_PAD_LEFT) ?> - <?= htmlspecialchars($item['item_name']) ?></span></p>
                </div>
                <div class="header-actions">
                    <a href="inventory.php" class="btn btn-outline">
                        Discard Changes
                    </a>
                </div>
            </header>

            <div class="form-container animate-fade-in" style="animation-delay: 0.1s">
                <div class="card form-card glass">
                    <form method="POST" action="" class="glass-form" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group span-2">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($item['item_name']) ?>" required autofocus>
                            </div>
                            
                            <div class="form-group span-2">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($item['description']) ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Current Stock (Read-Only)</label>
                                <input type="number" class="form-control readonly-field" value="<?= $item['quantity'] ?>" readonly>
                                <span class="input-hint">Update via Purchase Module</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Calculated Sale Price (Read-Only)</label>
                                <div class="price-wrap">
                                    <span class="currency-label">৳</span>
                                    <input type="number" id="priceInput" class="form-control readonly-field" value="<?= $item['price'] ?>" readonly>
                                </div>
                                <span class="input-hint">Derived from latest purchase + 15%</span>
                            </div>

                            <div class="form-group span-2">
                                <label class="form-label">Price In Words</label>
                                <input type="text" id="priceText" class="form-control readonly-field" style="background: rgba(0,0,0,0.1);" readonly>
                            </div>

                            <div class="form-group span-2">
                                <label class="form-label">Product Image</label>
                                <div class="image-edit-container">
                                    <?php if ($item['image']): ?>
                                        <div class="current-image">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product">
                                            <p class="input-hint">Current Image</p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="file-upload-wrapper">
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <span class="input-hint">Select a file to replace.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Product Identity</button>
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
    .readonly-field { background: rgba(255, 255, 255, 0.05) !important; color: #94a3b8 !important; cursor: not-allowed; border-color: rgba(255,255,255,0.05); }
    .price-wrap { position: relative; }
    .currency-label { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--accent-primary); font-weight: 700; }
    .price-wrap .form-control { padding-left: 2.5rem; }
    .input-hint { font-size: 0.75rem; color: var(--text-dim); margin-top: 0.5rem; display: block; }
    .active-item { color: var(--accent-primary); font-weight: 700; }
    .image-edit-container { display: flex; gap: 2rem; align-items: center; background: rgba(0,0,0,0.1); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); }
    .current-image img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid var(--accent-primary); }

    .form-actions { margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--border-color); }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .image-edit-container { flex-direction: column; align-items: flex-start; }
    }
    </style>
    <script>
    function numberToWords(number) {
        const units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        const scales = ['', 'Thousand', 'Million', 'Billion'];

        if (number == 0) return 'Zero Taka Only';
        let [integerPart, decimalPart] = number.toString().split('.');
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
        if (decimalPart) {
            let paisa = parseInt(decimalPart.substring(0, 2));
            if (paisa > 0) { words += ' and ' + convertThreeDigit(paisa).trim() + ' Paisa'; }
        }
        return words + ' Only';
    }

    const pIn = document.getElementById('priceInput');
    const pTx = document.getElementById('priceText');
    if (pIn.value) pTx.value = numberToWords(pIn.value);
    </script>
</body>
</html>