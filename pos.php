<?php
require 'auth.php';
require 'config.php';

// Fetch all products (including those with 0 quantity so we can show alerts)
$stmt = $pdo->query('SELECT * FROM products ORDER BY item_name ASC');
$products = $stmt->fetchAll();

// Fetch all customers for selection
$stmt = $pdo->query('SELECT * FROM customers ORDER BY name ASC');
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'POS Retail Terminal';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">POS Terminal</h1>
                    <p class="text-muted">High-performance retail processing with integrated stock monitoring.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-outline" onclick="resetTerminal()">Reset Transaction</button>
                </div>
            </header>

            <div class="pos-grid animate-fade-in" style="animation-delay: 0.1s">
                <div class="pos-left-panel">
                    <!-- Product Selection -->
                    <div class="card glass pos-search-card">
                        <div class="search-header">
                            <h2 class="section-title">Product Catalog</h2>
                            <div class="pos-search-input">
                                <input type="text" id="productSearch" onkeyup="filterProducts()" placeholder="Search catalog...">
                            </div>
                        </div>
                        
                        <div class="product-selection-grid" id="productGrid">
                            <?php foreach ($products as $p): 
                                $isLow = ($p['quantity'] > 0 && $p['quantity'] < 10);
                                $isOut = ($p['quantity'] <= 0);
                                $cardClass = $isOut ? 'item-out' : ($isLow ? 'item-low' : '');
                            ?>
                                <div class="product-card glass <?= $cardClass ?>" 
                                     data-id="<?= $p['id'] ?>" 
                                     data-name="<?= htmlspecialchars($p['item_name']) ?>" 
                                     data-price="<?= $p['price'] ?>" 
                                     data-stock="<?= $p['quantity'] ?>"
                                     onclick="handleProductSelection(this)">
                                    
                                    <div class="card-media">
                                        <?php if ($p['image']): ?>
                                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="" class="card-img">
                                        <?php else: ?>
                                            <div class="card-placeholder">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                    <polyline points="21 15 16 10 5 21"></polyline>
                                                </svg>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($isOut): ?>
                                            <div class="badge badge-error">Out of Stock</div>
                                        <?php elseif ($isLow): ?>
                                            <div class="badge badge-warning">Low Stock</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-info">
                                        <div class="name-row">
                                            <span class="p-card-name"><?= htmlspecialchars($p['item_name']) ?></span>
                                            <span class="p-card-stock"><?= $p['quantity'] ?> units</span>
                                        </div>
                                        <div class="price-row">
                                            <span class="p-card-price">৳<?= number_format($p['price'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="pos-right-panel">
                    <!-- Cart Summary -->
                    <form id="posForm" method="POST" action="process_sale.php">
                        <div class="card glass pos-cart-card">
                            <h2 class="section-title">Current Bucket</h2>
                            <div class="cart-items-container" id="cartContainer">
                                <div class="empty-cart-msg">Select products to begin transaction.</div>
                            </div>

                            <div class="cart-summary-footer">
                                <div class="form-group">
                                    <label class="form-label">Customer Profile</label>
                                    <select name="customer_id" class="form-control pos-select">
                                        <option value="">Walk-in Retail Customer</option>
                                        <?php foreach ($customers as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= $c['phone'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Discount (%)</label>
                                    <input type="number" id="discountPercent" class="form-control pos-select" value="0" min="0" max="100" step="0.1">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tax / VAT (%)</label>
                                    <input type="number" id="taxPercent" class="form-control pos-select" value="0" min="0" max="100" step="0.1">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Payment Mode</label>
                                    <select name="payment_method" class="form-control pos-select">
                                        <option value="Cash">Physical Cash</option>
                                        <option value="Card">Card / Digital Pay</option>
                                        <option value="Credits">Internal Store Credits</option>
                                    </select>
                                </div>

                                <div class="totals-section">
                                    <div class="total-row">
                                        <span>Subtotal</span>
                                        <span id="subtotalVal">৳0.00</span>
                                    </div>
                                    <div class="total-row discount-row" style="color: var(--danger);">
                                        <span>Discount</span>
                                        <span id="discountVal">৳0.00</span>
                                    </div>
                                    <div class="total-row tax-row" style="color: var(--success);">
                                        <span>VAT / Tax</span>
                                        <span id="taxVal">৳0.00</span>
                                    </div>
                                    <div class="total-row grand-total">
                                        <span>Final Total</span>
                                        <span id="grandTotalVal">৳0.00</span>
                                    </div>
                                </div>

                                <input type="hidden" name="cart_data" id="cartDataInput">
                                <input type="hidden" name="subtotal_amount" id="subtotalInput">
                                <input type="hidden" name="discount_amount" id="discountInput">
                                <input type="hidden" name="tax_amount" id="taxInput">
                                <button type="submit" class="btn btn-primary btn-full checkout-btn">
                                    Finalize Transaction
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .pos-grid { display: grid; grid-template-columns: 1fr 400px; gap: 1.5rem; align-items: start; }
    
    /* Left Panel: Boxy Catalog */
    .pos-search-card { padding: 1.75rem; display: flex; flex-direction: column; height: 750px; }
    .search-header { margin-bottom: 2rem; }
    .pos-search-input input { width: 100%; padding: 0.9rem 1.25rem; background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: 14px; color: white; outline: none; transition: 0.2s; font-size: 0.9rem; }
    .pos-search-input input:focus { border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.4); }

    .product-selection-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1.25rem; overflow-y: auto; flex: 1; padding-right: 0.75rem; }
    
    .product-card { padding: 0; cursor: pointer; border-radius: 20px; overflow: hidden; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02); }
    .product-card:hover { transform: translateY(-4px); border-color: var(--accent-primary); background: rgba(139, 92, 246, 0.05); }
    
    .card-media { position: relative; height: 140px; background: rgba(0, 0, 0, 0.2); }
    .card-img { width: 100%; height: 100%; object-fit: cover; }
    .card-placeholder { height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-dim); }
    
    .badge { position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.35rem 0.6rem; border-radius: 8px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
    .badge-error { background: rgba(239, 68, 68, 0.9); color: white; backdrop-filter: blur(4px); }
    .badge-warning { background: rgba(245, 158, 11, 0.9); color: white; backdrop-filter: blur(4px); }

    .card-info { padding: 1.25rem; }
    .name-row { margin-bottom: 0.75rem; }
    .p-card-name { display: block; font-weight: 700; font-size: 0.95rem; line-height: 1.2; margin-bottom: 0.4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .p-card-stock { display: block; font-size: 0.75rem; color: var(--text-muted); }
    .p-card-price { color: var(--accent-primary); font-weight: 800; font-size: 1.1rem; }

    .item-out { opacity: 0.6; grayscale: 100%; -webkit-filter: grayscale(1); }
    .item-out .p-card-price { color: var(--text-dim); }

    /* Right Panel: Cart */
    .pos-cart-card { padding: 1.75rem; display: flex; flex-direction: column; height: 750px; }
    .cart-items-container { flex: 1; overflow-y: auto; margin: 1rem 0; padding-right: 0.5rem; border-bottom: 1px solid var(--border-color); }
    .empty-cart-msg { text-align: center; color: var(--text-dim); margin-top: 5rem; font-style: italic; font-size: 0.85rem; }

    .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .cart-item-name { display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.2rem; }
    .cart-item-price { font-size: 0.8rem; color: var(--accent-primary); font-weight: 600; }
    .cart-item-controls { display: flex; align-items: center; gap: 0.75rem; }
    .qty-btn { background: rgba(255, 255, 255, 0.08); border: none; color: white; width: 28px; height: 28px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .qty-btn:hover { background: var(--accent-primary); }
    .cart-item-qty { font-weight: 800; min-width: 24px; text-align: center; font-size: 0.9rem; }

    .totals-section { background: rgba(255, 255, 255, 0.04); padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; border: 1px solid var(--border-color); }
    .total-row { display: flex; justify-content: space-between; margin-bottom: 0.6rem; font-size: 0.9rem; color: var(--text-secondary); }
    .grand-total { border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 0.75rem; font-weight: 900; font-size: 1.4rem; color: var(--accent-primary); }
    
    .btn-full { width: 100%; border-radius: 14px; padding: 1.1rem; font-size: 1rem; }

    @media (max-width: 1200px) { .pos-grid { grid-template-columns: 1fr; } .pos-search-card, .pos-cart-card { height: auto; min-height: 500px; } }
    </style>

    <script>
    let cart = [];

    function handleProductSelection(element) {
        const id = element.dataset.id;
        const name = element.dataset.name;
        const price = parseFloat(element.dataset.price);
        const stock = parseInt(element.dataset.stock);

        // Alert: Out of Stock
        if (stock <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Out of Stock',
                text: 'The product "' + name + '" is currently unavailable.',
                confirmButtonColor: 'var(--accent-primary)'
            });
            return;
        }

        // Alert: Low Stock Warning
        if (stock < 10) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'warning',
                title: 'Low Stock Warning',
                text: name + ' has only ' + stock + ' units left.'
            });
        }

        addToCart(id, name, price, stock);
    }

    function addToCart(id, name, price, stock) {
        const existing = cart.find(item => item.id == id);
        if (existing) {
            if (existing.qty < stock) {
                existing.qty++;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Inventory Lock',
                    text: 'You cannot exceed the available units in stock.',
                    confirmButtonColor: 'var(--accent-primary)'
                });
            }
        } else {
            cart.push({ id, name, price, qty: 1, stock });
        }
        renderCart();
    }

    function removeFromCart(id) {
        const index = cart.findIndex(item => item.id == id);
        if (cart[index].qty > 1) {
            cart[index].qty--;
        } else {
            cart.splice(index, 1);
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartContainer');
        const dataInput = document.getElementById('cartDataInput');
        
        if (cart.length === 0) {
            container.innerHTML = '<div class="empty-cart-msg">Select products to begin transaction.</div>';
            updateTotals(0);
            dataInput.value = '';
            return;
        }

        let html = '';
        let total = 0;
        cart.forEach(item => {
            const subtotal = item.price * item.qty;
            total += subtotal;
            html += `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <span class="cart-item-name">${item.name}</span>
                        <span class="cart-item-price">৳${item.price.toFixed(2)}</span>
                    </div>
                    <div class="cart-item-controls">
                        <button type="button" class="qty-btn" onclick="removeFromCart(${item.id})">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                        <span class="cart-item-qty">${item.qty}</span>
                        <button type="button" class="qty-btn" onclick="addToCart(${item.id}, '${item.name}', ${item.price}, ${item.stock})">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
        updateTotals(total);
        dataInput.value = JSON.stringify(cart);
    }

    function updateTotals(subtotal) {
        const dPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const tPercent = parseFloat(document.getElementById('taxPercent').value) || 0;

        const dAmount = subtotal * (dPercent / 100);
        const taxableAmount = subtotal - dAmount;
        const tAmount = taxableAmount * (tPercent / 100);
        const grandTotal = taxableAmount + tAmount;

        document.getElementById('subtotalVal').innerText = '৳' + subtotal.toFixed(2);
        document.getElementById('discountVal').innerText = '-৳' + dAmount.toFixed(2);
        document.getElementById('taxVal').innerText = '৳' + tAmount.toFixed(2);
        document.getElementById('grandTotalVal').innerText = '৳' + grandTotal.toFixed(2);

        // Hidden inputs for form submission
        document.getElementById('subtotalInput').value = subtotal.toFixed(2);
        document.getElementById('discountInput').value = dAmount.toFixed(2);
        document.getElementById('taxInput').value = tAmount.toFixed(2);
    }

    // Add listeners to percentage inputs
    document.getElementById('discountPercent').addEventListener('input', () => {
        let total = 0;
        cart.forEach(item => total += (item.price * item.qty));
        updateTotals(total);
    });
    document.getElementById('taxPercent').addEventListener('input', () => {
        let total = 0;
        cart.forEach(item => total += (item.price * item.qty));
        updateTotals(total);
    });

    function filterProducts() {
        const filter = document.getElementById('productSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            if (name.includes(filter)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function resetTerminal() {
        if (cart.length > 0) {
            Swal.fire({
                title: 'Reset Session?',
                text: "Current cart data will be discarded.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--accent-primary)',
                cancelButtonColor: 'var(--border-color)',
                confirmButtonText: 'Yes, reset'
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = [];
                    renderCart();
                }
            });
        }
    }
    
    document.getElementById('posForm').onsubmit = function(e) {
        if (cart.length === 0) {
            Swal.fire({ icon: 'error', title: 'Empty Cart', text: 'Please select at least one item to checkout.' });
            return false;
        }
    };
    </script>
</body>
</html>
