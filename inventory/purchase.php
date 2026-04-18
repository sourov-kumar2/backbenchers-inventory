<?php
$base_url = '../';
require '../auth.php';
require '../config.php';

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
$pageTitle = 'Procurement Intelligence';
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
                        <h1 class="view-title">Stock Procurement</h1>
                        <p class="view-subtitle">Bridge the supply chain and update inventory levels</p>
                    </div>
                </div> -->

                <div class="form-container">
                    <div class="card form-card glass">
                        <form method="POST" action="" class="intelligence-form" id="purchaseForm">
                            
                            <div class="form-section">
                                <h3 class="section-title">Source & Logistics</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Target Product</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                        <select name="product_id" class="form-control custom-select" required>
                                            <option value="">-- Select Product Profile --</option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= $p['id'] ?>">
                                                    <?= htmlspecialchars($p['item_name']) ?> (Current: <?= $p['quantity'] ?> units)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Supplier Source</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        <select name="supplier_id" class="form-control custom-select" required>
                                            <option value="">-- Choose Origin Supplier --</option>
                                            <?php foreach ($suppliers as $s): ?>
                                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Volume & Valuation</h3>
                                
                                <div class="form-row">
                                    <div class="form-group flex-1">
                                        <label class="form-label">Purchase Cost (Unit)</label>
                                        <div class="input-icon-wrapper">
                                            <span class="field-symbol">৳</span>
                                            <input type="number" name="purchase_price" class="form-control pl-symbol" placeholder="0.00" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                    <div class="form-group flex-1">
                                        <label class="form-label">Quantity</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>
                                            <input type="number" name="quantity" class="form-control pl-icon" placeholder="0" min="1" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Intelligence Prediction Box -->
                                <div class="intel-dashboard glass" id="priceIntel" style="display: none;">
                                    <div class="intel-header">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                                        System Prediction Engine
                                    </div>
                                    <div class="intel-metrics">
                                        <div class="intel-stat">
                                            <span class="intel-dim">Target Retail (+15%)</span>
                                            <span class="intel-bold text-premium" id="retailPreview">৳0.00</span>
                                        </div>
                                        <div class="intel-stat">
                                            <span class="intel-dim">Projected Yield / Unit</span>
                                            <span class="intel-bold text-success" id="profitPreview">৳0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <div class="disclaimer-note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    Committing this transaction will automatically update the product's unit price in the catalog and increment the on-hand stock inventory.
                                </div>
                                <div class="action-buttons">
                                    <a href="index.php" class="btn btn-outline">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Commit Procurement</button>
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
    .form-row { display: flex; gap: 1.5rem; }
    .flex-1 { flex: 1; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; }
    
    .input-icon-wrapper { position: relative; }
    .field-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }
    .field-symbol { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--accent-primary); font-weight: 800; font-size: 1.1rem; pointer-events: none; }
    
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1.25rem; color: white; transition: 0.25s; font-size: 0.95rem; appearance: none; }
    .custom-select { cursor: pointer; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2394a3b8%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 1.25rem center; background-size: 1.2rem; padding-right: 3.5rem; }
    
    .input-icon-wrapper .form-control { padding-left: 3.5rem; }
    .custom-select option { background: #1e293b; color: white; }
    .form-control:focus { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    
    .form-divider { height: 1px; background: linear-gradient(to right, transparent, var(--border-color), transparent); margin: 2rem 0; }

    .intel-dashboard { background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 20px; padding: 1.5rem; margin-top: 1rem; }
    .intel-header { font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: var(--accent-primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; letter-spacing: 0.1em; }
    .intel-metrics { display: flex; justify-content: space-between; gap: 1rem; }
    .intel-stat { display: flex; flex-direction: column; gap: 0.25rem; }
    .intel-dim { font-size: 0.75rem; color: var(--text-dim); font-weight: 600; }
    .intel-bold { font-size: 1.5rem; font-weight: 900; }
    
    .text-premium { color: var(--text-primary); }
    .text-success { color: #10b981; }

    .form-footer { margin-top: 3rem; display: flex; flex-direction: column; gap: 2rem; }
    .disclaimer-note { display: flex; align-items: center; gap: 0.8rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 14px; font-size: 0.8rem; color: var(--text-dim); border: 1px solid var(--border-color); line-height: 1.4; }
    .action-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
    
    .btn { padding: 1rem 2rem; font-weight: 700; border-radius: 14px; }
    .btn-outline:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    @media (max-width: 600px) {
        .form-card { padding: 1.5rem; }
        .form-row { flex-direction: column; gap: 1.5rem; }
        .action-buttons { flex-direction: column; }
    }
    </style>

    <script>
    <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: 'Commit Successful',
            text: 'Procurement transaction recorded. Catalog valuation updated with 15% markup.',
            confirmButtonColor: 'var(--accent-primary)',
            background: '#1e293b',
            color: '#f8fafc'
        }).then(() => { window.location.href = 'index.php'; });
    <?php endif; ?>

    <?php if ($error): ?>
        Swal.fire({ icon: 'error', title: 'System Error', text: '<?= $error ?>', confirmButtonColor: '#ef4444' });
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
            retailP.innerText = '৳' + retail.toLocaleString(undefined, {minimumFractionDigits: 2});
            profitP.innerText = '৳' + profit.toLocaleString(undefined, {minimumFractionDigits: 2});
            intel.style.display = 'block';
        } else {
            intel.style.display = 'none';
        }
    });
    </script>
</body>
</html>
