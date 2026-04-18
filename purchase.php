<?php
require 'auth.php';
require 'config.php';

// Fetch all products for selection
$stmt = $pdo->query('SELECT id, item_name, quantity, price FROM products ORDER BY item_name ASC');
$products = $stmt->fetchAll();

// Fetch all suppliers for selection
$stmt = $pdo->query('SELECT id, name FROM suppliers ORDER BY name ASC');
$suppliers = $stmt->fetchAll();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id   = $_POST['product_id']   ?? null;
    $supplier_id  = $_POST['supplier_id']  ?? null;
    $cost_price   = $_POST['purchase_price'] ?? 0;
    $quantity     = $_POST['quantity']     ?? 0;

    if ($product_id && $supplier_id && $cost_price > 0 && $quantity > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Record Purchase
            $stmt = $pdo->prepare('INSERT INTO purchases (product_id, supplier_id, purchase_price, quantity) VALUES (?, ?, ?, ?)');
            $stmt->execute([$product_id, $supplier_id, $cost_price, $quantity]);

            // 2. Update Product: Increment Stock & Calculate 15% Markup
            // Selling Price = Cost * 1.15
            $selling_price = $cost_price * 1.15;
            $stmt = $pdo->prepare('UPDATE products SET quantity = quantity + ?, price = ? WHERE id = ?');
            $stmt->execute([$quantity, $selling_price, $product_id]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Transaction Failed: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill all fields with valid data.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Record Purchase';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Stock Purchase</h1>
                    <p class="text-muted">Procure new units and automatically calculate retail market pricing.</p>
                </div>
                <div class="header-actions">
                    <a href="inventory.php" class="btn btn-outline">Stock Inventory</a>
                </div>
            </header>

            <div class="purchase-grid animate-fade-in" style="animation-delay: 0.1s">
                <div class="form-section">
                    <div class="card form-card glass">
                        <form method="POST" action="" class="glass-form" id="purchaseForm">
                            <div class="form-grid">
                                <div class="form-group span-2">
                                    <label class="form-label">Select Product</label>
                                    <select name="product_id" class="form-control" required>
                                        <option value="">-- Choose Product --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= $p['id'] ?>">
                                                <?= htmlspecialchars($p['item_name']) ?> (Current: <?= $p['quantity'] ?> units)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group span-2">
                                    <label class="form-label">Select Supplier</label>
                                    <select name="supplier_id" class="form-control" required>
                                        <option value="">-- Choose Source --</option>
                                        <?php foreach ($suppliers as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Purchase Cost (per unit)</label>
                                    <div class="price-wrap">
                                        <span class="currency-label">৳</span>
                                        <input type="number" name="purchase_price" class="form-control" placeholder="0.00" step="0.01" min="0.01" required>
                                    </div>
                                    <span class="input-hint">The price you paid to supplier</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Quantity Procured</label>
                                    <input type="number" name="quantity" class="form-control" placeholder="0" min="1" required>
                                    <span class="input-hint">Units to add to inventory</span>
                                </div>

                                <div class="intel-box span-2" id="priceIntel" style="display: none;">
                                    <div class="intel-item">
                                        <span class="intel-label">Calculated Retail Price (+15%)</span>
                                        <span class="intel-value text-purple" id="retailPreview">৳0.00</span>
                                    </div>
                                    <div class="intel-item">
                                        <span class="intel-label">Estimated Profit / Unit</span>
                                        <span class="intel-value text-green" id="profitPreview">৳0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-full">Commit Purchase</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .purchase-grid { display: flex; justify-content: center; gap: 2rem; align-items: start; }
    .form-section { width: 100%; max-width: 800px; }
    .form-card { padding: 2.5rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .span-2 { grid-column: span 2; }
    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; }
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.8rem 1rem; color: white; transition: 0.2s; font-size: 0.9rem; }
    .form-control:focus { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); }
    
    .price-wrap { position: relative; }
    .currency-label { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--accent-primary); font-weight: 700; }
    .price-wrap .form-control { padding-left: 2.5rem; }
    .input-hint { font-size: 0.75rem; color: var(--text-dim); margin-top: 0.5rem; display: block; }

    .intel-box { background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 16px; padding: 1.5rem; display: flex; justify-content: space-between; margin-top: 1rem; }
    .intel-item { display: flex; flex-direction: column; gap: 0.5rem; }
    .intel-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); font-weight: 800; }
    .intel-value { font-size: 1.4rem; font-weight: 900; }
    .text-purple { color: var(--accent-primary); }
    .text-green { color: var(--success); }

    .form-actions { margin-top: 2.5rem; }
    .btn-full { width: 100%; padding: 1.1rem; border-radius: 14px; font-weight: 700; font-size: 1rem; }


    @media (max-width: 1100px) { .purchase-grid { grid-template-columns: 1fr; } }
    </style>

    <script>
    <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: 'Purchase Recorded',
            text: 'Inventory updated and 15% markup applied successfully!',
            confirmButtonColor: 'var(--accent-primary)'
        }).then(() => { window.location.href = 'inventory.php'; });
    <?php endif; ?>

    <?php if ($error): ?>
        Swal.fire({ icon: 'error', title: 'Error', text: '<?= $error ?>', confirmButtonColor: 'var(--accent-primary)' });
    <?php endif; ?>

    const pIn = document.querySelector('input[name="purchase_price"]');
    const intel = document.getElementById('priceIntel');
    const retailP = document.getElementById('retailPreview');
    const profitP = document.getElementById('profitPreview');

    pIn.addEventListener('input', (e) => {
        const cost = parseFloat(e.target.value);
        if (cost > 0) {
            const retail = cost * 1.15;
            const profit = retail - cost;
            retailP.innerText = '৳' + retail.toFixed(2);
            profitP.innerText = '৳' + profit.toFixed(2);
            intel.style.display = 'flex';
        } else {
            intel.style.display = 'none';
        }
    });
    </script>
</body>
</html>
