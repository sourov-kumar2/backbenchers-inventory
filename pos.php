<?php
require 'auth.php';
require 'config.php';

// Fetch all products (including 0 qty for stock alerts)
$stmt = $pdo->query('SELECT * FROM products ORDER BY item_name ASC');
$products = $stmt->fetchAll();

// Fetch all customers
$stmt = $pdo->query('SELECT * FROM customers ORDER BY name ASC');
$customers = $stmt->fetchAll();

// Error messages from redirects
$errorMsg = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_cart') $errorMsg = 'Cart was empty. Please add items.';
    if ($_GET['error'] === 'walkin_debt') $errorMsg = 'Walk-in customers must pay the full amount.';
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'POS Terminal';
include 'partials/head.php';
?>
<body>
<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'partials/navbar.php'; ?>

        <?php if ($errorMsg): ?>
        <div class="alert-banner">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($errorMsg) ?>
        </div>
        <?php endif; ?>

        <div class="pos-shell animate-fade-in">

            <!-- ═══════════════════════════════════════ LEFT PANEL -->
            <div class="pos-left">

                <!-- Neural Command -->
                <div class="panel-card neural-card">
                    <div class="panel-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        Neural Command Terminal
                    </div>
                    <div class="neural-wrap">
                        <svg class="neural-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        <input type="text" id="aiCommandInput" class="neural-input"
                               placeholder="e.g. Add 3 SSDs for Ayesha with 10% discount...">
                        
                        <div class="neural-controls">
                            <button type="button" id="langToggle" class="lang-toggle" title="Switch Language">
                                <span class="lang-en">EN</span>
                                <span class="lang-sep">/</span>
                                <span class="lang-bn">বাং</span>
                            </button>
                            <button type="button" id="micBtn" class="mic-btn" title="Voice Command">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>
                            </button>
                        </div>
                        
                        <div class="neural-loader" id="aiLoader"></div>
                    </div>
                    <span class="neural-hint">Press <kbd>Enter</kbd> to execute — AI will parse your command instantly.</span>
                </div>

                <!-- Product Catalog -->
                <div class="panel-card catalog-card">
                    <div class="catalog-header">
                        <div class="panel-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="3"/></svg>
                            Product Catalog
                        </div>
                        <div class="search-row">
                            <div class="search-wrap">
                                <svg class="search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" id="productSearch" class="search-input"
                                       onkeyup="filterProducts()" placeholder="Search catalog...">
                            </div>
                            <span class="catalog-count" id="catalogCount"><?= count($products) ?> items</span>
                        </div>
                    </div>

                    <div class="product-grid" id="productGrid">
                        <?php foreach ($products as $p):
                            $isOut = ($p['quantity'] <= 0);
                            $isLow = (!$isOut && $p['quantity'] < 10);
                        ?>
                        <div class="p-card <?= $isOut ? 'p-out' : ($isLow ? 'p-low' : '') ?>"
                             data-id="<?= $p['id'] ?>"
                             data-name="<?= htmlspecialchars($p['item_name']) ?>"
                             data-price="<?= $p['price'] ?>"
                             data-stock="<?= $p['quantity'] ?>"
                             data-image="<?= htmlspecialchars($p['image'] ?? '') ?>"
                             onclick="handleProductClick(this)">

                            <!-- Media area -->
                            <div class="p-media">
                                <?php if (!empty($p['image'])): ?>
                                    <img class="p-img" src="<?= htmlspecialchars($p['image']) ?>" alt="">
                                <?php else: ?>
                                    <!-- NO IMAGE: show initials only, name is in p-body -->
                                    <div class="p-noimg">
                                        <div class="p-noimg-initials"><?= mb_strtoupper(mb_substr($p['item_name'], 0, 2)) ?></div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($isOut): ?>
                                    <span class="p-badge p-badge-out">Out of Stock</span>
                                <?php elseif ($isLow): ?>
                                    <span class="p-badge p-badge-low">Low <?= $p['quantity'] ?> left</span>
                                <?php endif; ?>

                                <!-- Cart quantity bubble (shown when in cart) -->
                                <span class="p-cart-bubble" id="bubble-<?= $p['id'] ?>" style="display:none">0</span>
                            </div>

                            <div class="p-body">
                                <div class="p-name"><?= htmlspecialchars($p['item_name']) ?></div>
                                <div class="p-footer">
                                    <span class="p-price">৳<?= number_format($p['price'], 2) ?></span>
                                    <span class="p-stock"><?= $p['quantity'] ?>u</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════ RIGHT PANEL: CART -->
            <div class="pos-right">
                <form id="posForm" method="POST" action="process_sale.php">

                    <!-- Cart header -->
                    <div class="cart-header">
                        <div class="cart-title-row">
                            <h2 class="cart-title">Cart</h2>
                            <span class="cart-pill" id="cartPill">0</span>
                        </div>
                        <button type="button" class="cart-clear-btn" onclick="resetCart()">Clear all</button>
                    </div>

                    <!-- Cart items -->
                    <div class="cart-body" id="cartBody">
                        <div class="cart-empty" id="cartEmpty">
                            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            <p>Select products from the catalog<br>to start a transaction.</p>
                        </div>
                        <div id="cartItems"></div>
                    </div>

                    <!-- Cart footer / options -->
                    <div class="cart-footer">
                        <div class="form-group">
                            <label class="form-lbl">Customer Profile</label>
                            <select name="customer_id" class="pos-select" id="customerSelect">
                                <option value="">Walk-in Retail Customer</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> &nbsp;(<?= htmlspecialchars($c['phone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-lbl">Discount %</label>
                                <input type="number" id="discountPercent" class="pos-input" value="0" min="0" max="100" step="0.1">
                            </div>
                            <div class="form-group">
                                <label class="form-lbl">VAT / Tax %</label>
                                <input type="number" id="taxPercent" class="pos-input" value="0" min="0" max="100" step="0.1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-lbl">Payment Method</label>
                            <select name="payment_method" class="pos-select">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card / Digital Pay</option>
                                <option value="Credits">Store Credits</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-lbl">Amount Paid (৳)</label>
                            <input type="number" name="amount_paid" id="amountPaid" class="pos-input" step="0.01" required>
                            <div class="due-hint" id="dueHint"></div>
                        </div>

                        <!-- Totals -->
                        <div class="totals-box">
                            <div class="total-row">
                                <span class="t-label">Subtotal</span>
                                <span class="t-val" id="subtotalDisp">৳0.00</span>
                            </div>
                            <div class="total-row">
                                <span class="t-label">Discount</span>
                                <span class="t-val t-disc" id="discountDisp">-৳0.00</span>
                            </div>
                            <div class="total-row">
                                <span class="t-label">VAT / Tax</span>
                                <span class="t-val t-tax" id="taxDisp">৳0.00</span>
                            </div>
                            <div class="total-row total-grand">
                                <span>Final Total</span>
                                <span class="t-grand" id="grandDisp">৳0.00</span>
                            </div>
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <input type="hidden" name="cart_data" id="cartDataInput">
                        <input type="hidden" name="subtotal_amount" id="subtotalInput">
                        <input type="hidden" name="discount_amount" id="discountAmtInput">
                        <input type="hidden" name="tax_amount" id="taxAmtInput">

                        <button type="submit" class="checkout-btn" id="checkoutBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Finalize Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php include 'partials/footer.php'; ?>
    </div>
</div>

<!-- ═══════════════ STYLES ═══════════════ -->
<style>
/* ── Reset & Shell ── */
*, *::before, *::after { box-sizing: border-box; }

:root {
    --bg:       #090910;
    --surface:  #101018;
    --card:     #16161f;
    --card2:    #1c1c28;
    --border:   #252535;
    --border2:  #32324a;
    --accent:   #7c6fe0;
    --accent-g: #a594f7;
    --teal:     #5eead4;
    --danger:   #f87171;
    --warn:     #fbbf24;
    --success:  #4ade80;
    --text:     #e4e4f0;
    --muted:    #6a6a8a;
    --dim:      #3a3a55;
    --font-ui:  system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    --font-mono: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Monaco, 'Consolas', monospace;
}

body { font-family: var(--font-ui); background: var(--bg); color: var(--text); }

/* Alert banner */
.alert-banner {
    display: flex; align-items: center; gap: .6rem;
    background: rgba(248,113,113,.08); border: 1px solid rgba(248,113,113,.25);
    color: var(--danger); border-radius: 10px; padding: .75rem 1.25rem;
    font-size: .82rem; margin-bottom: 1rem;
}

/* ── POS Layout ── */
.pos-shell {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 1.25rem;
    align-items: start;
    margin-bottom: 2rem;
}

/* ── Left Panel ── */
.pos-left { display: flex; flex-direction: column; gap: 1rem; }

.panel-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
}

.panel-label {
    display: flex; align-items: center; gap: .5rem;
    font-size: .7rem; font-family: var(--font-mono);
    letter-spacing: .12em; text-transform: uppercase;
    color: var(--muted); margin-bottom: 1rem;
}

/* Neural Card */
.neural-wrap { position: relative; }
.neural-input {
    width: 100%; padding: .85rem 3rem .85rem 2.75rem;
    background: rgba(124,111,224,.06); border: 1px solid rgba(124,111,224,.3);
    border-radius: 12px; color: var(--text); font-family: var(--font-ui);
    font-size: .88rem; font-weight: 600; outline: none; transition: .2s;
}
.neural-input:focus {
    border-color: var(--accent);
    background: rgba(124,111,224,.1);
    box-shadow: 0 0 0 4px rgba(124,111,224,.12);
}
.neural-input::placeholder { color: var(--muted); font-weight: 400; }
.neural-input:disabled { opacity: .5; }
.neural-icon {
    position: absolute; left: .9rem; top: 50%;
    transform: translateY(-50%); color: var(--accent);
    animation: pulse-icon 3s ease-in-out infinite;
}
@keyframes pulse-icon { 0%,100%{opacity:1} 50%{opacity:.5} }

.mic-btn.recording { color: var(--danger); animation: mic-pulse 1.5s infinite; }

.neural-controls {
    position: absolute; right: 2.75rem; top: 50%; transform: translateY(-50%);
    display: flex; align-items: center; gap: .5rem;
}

.lang-toggle {
    background: var(--dim); border: 1px solid var(--border2);
    border-radius: 20px; padding: .2rem .6rem;
    font-size: .65rem; font-weight: 700; color: var(--muted);
    cursor: pointer; display: flex; align-items: center; gap: .25rem;
    transition: .2s; font-family: var(--font-mono);
}
.lang-toggle.is-bn .lang-bn { color: var(--accent); }
.lang-toggle.is-bn .lang-en { color: var(--muted); }
.lang-toggle.is-en .lang-en { color: var(--accent); }
.lang-toggle.is-en .lang-bn { color: var(--muted); }
.lang-toggle:hover { border-color: var(--accent); }

.mic-btn {
    background: transparent; border: none; color: var(--muted);
    cursor: pointer; transition: .2s; padding: .4rem; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.mic-btn:hover { color: var(--accent); background: rgba(124,111,224,.1); }

@keyframes mic-pulse {
    0% { transform: translateY(-50%) scale(1); box-shadow: 0 0 0 0 rgba(248,113,113,0.4); }
    70% { transform: translateY(-50%) scale(1.2); box-shadow: 0 0 0 10px rgba(248,113,113,0); }
    100% { transform: translateY(-50%) scale(1); box-shadow: 0 0 0 0 rgba(248,113,113,0); }
}

.neural-loader {
    position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
    width: 17px; height: 17px;
    border: 2px solid rgba(124,111,224,.15); border-top-color: var(--accent);
    border-radius: 50%; animation: spin .7s linear infinite; display: none;
}
@keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
.neural-hint {
    font-size: .72rem; color: var(--muted); font-family: var(--font-mono);
    margin-top: .5rem; display: block;
}
.neural-hint kbd {
    background: var(--card2); border: 1px solid var(--border2);
    border-radius: 4px; padding: .1rem .35rem; font-size: .7rem;
}

/* Catalog Card */
.catalog-card { padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 630px; }
.catalog-header { padding: 1.25rem 1.5rem 1rem; border-bottom: 1px solid var(--border); flex-shrink: 0; }

.search-row { display: flex; align-items: center; gap: .75rem; margin-top: .75rem; }
.search-wrap { position: relative; flex: 1; }
.search-input {
    width: 100%; padding: .6rem 1rem .6rem 2.25rem;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 9px; color: var(--text); font-family: var(--font-ui);
    font-size: .84rem; outline: none; transition: .2s;
}
.search-input:focus { border-color: var(--dim); }
.search-icon { position: absolute; left: .7rem; top: 50%; transform: translateY(-50%); color: var(--muted); }
.catalog-count { font-family: var(--font-mono); font-size: .7rem; color: var(--muted); white-space: nowrap; }

/* Product Grid */
.product-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(165px, 1fr));
    gap: 1rem; overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1; align-content: start;
}
.product-grid::-webkit-scrollbar { width: 4px; }
.product-grid::-webkit-scrollbar-track { background: transparent; }
.product-grid::-webkit-scrollbar-thumb { background: var(--dim); border-radius: 4px; }

/* Product Card */
.p-card {
    background: var(--card2); border: 1px solid var(--border);
    border-radius: 15px; overflow: hidden; cursor: pointer;
    transition: .22s cubic-bezier(.4,0,.2,1); position: relative;
    display: flex; flex-direction: column; height: 100%;
}
.p-card:hover { transform: translateY(-4px); border-color: var(--accent); box-shadow: 0 10px 28px rgba(124,111,224,.18); }
.p-card.p-out { opacity: .45; pointer-events: none; filter: grayscale(.85); }
.p-card.p-low { border-color: rgba(251,191,36,.3); }
.p-card.in-cart { border-color: rgba(94,234,212,.45); background: rgba(94,234,212,.04); }

/* Media area */
.p-media {
    position: relative; height: 128px;
    background: var(--surface); overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.p-img { width: 100%; height: 100%; object-fit: cover; }

/* NO-IMAGE placeholder — shows product info clearly */
.p-noimg {
    width: 100%; height: 100%; padding: .75rem;
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: .5rem; text-align: center;
}
.p-noimg-initials {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(124,111,224,.15); border: 1px solid rgba(124,111,224,.25);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-mono); font-size: .85rem; font-weight: 600;
    color: var(--accent); flex-shrink: 0;
}

/* Status badge */
.p-badge {
    position: absolute; top: .6rem; left: .6rem;
    padding: .22rem .55rem; border-radius: 6px;
    font-size: .63rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .04em;
}
.p-badge-out { background: rgba(248,113,113,.15); color: var(--danger); border: 1px solid rgba(248,113,113,.3); }
.p-badge-low { background: rgba(251,191,36,.12); color: var(--warn); border: 1px solid rgba(251,191,36,.25); }

/* Cart bubble overlay */
.p-cart-bubble {
    position: absolute; top: .6rem; right: .6rem;
    background: var(--accent); color: #fff;
    width: 21px; height: 21px; border-radius: 50%;
    font-family: var(--font-mono); font-size: .7rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(124,111,224,.4);
    animation: pop .2s cubic-bezier(.4,0,.2,1);
}
@keyframes pop { 0%{transform:scale(.5)} 70%{transform:scale(1.15)} 100%{transform:scale(1)} }

/* Product body */
.p-body { padding: .9rem; flex: 1; display: flex; flex-direction: column; background: var(--card2); position: relative; z-index: 2; }
.p-name {
    font-size: .81rem; font-weight: 700; line-height: 1.3;
    margin-bottom: .45rem; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.p-footer { display: flex; justify-content: space-between; align-items: center; }
.p-price { font-size: .92rem; font-weight: 800; color: var(--accent); font-family: var(--font-mono); }
.p-stock { font-size: .68rem; color: var(--muted); font-family: var(--font-mono); }

/* ── Right Panel: Cart ── */
.pos-right {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 18px; overflow: hidden;
    display: flex; flex-direction: column;
    position: sticky; top: 1rem;
    height: calc(100vh - 120px);
}
.pos-right form { display: flex; flex-direction: column; height: 100%; overflow-y: auto; overflow-x: hidden; }
.pos-right form::-webkit-scrollbar { width: 5px; }
.pos-right form::-webkit-scrollbar-thumb { background: var(--dim); border-radius: 5px; }

.cart-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); flex-shrink: 0;
}
.cart-title-row { display: flex; align-items: center; gap: .65rem; }
.cart-title { font-size: 1rem; font-weight: 800; margin: 0; }
.cart-pill {
    background: var(--accent); color: #fff;
    padding: .18rem .6rem; border-radius: 20px;
    font-family: var(--font-mono); font-size: .72rem; font-weight: 700;
}
.cart-clear-btn {
    background: transparent; border: 1px solid var(--border2);
    color: var(--muted); padding: .3rem .75rem; border-radius: 7px;
    font-size: .72rem; cursor: pointer; font-family: var(--font-ui); transition: .2s;
}
.cart-clear-btn:hover { border-color: var(--danger); color: var(--danger); }

/* Cart Body */
.cart-body { flex: 1; overflow-y: auto; padding: .75rem 1.5rem; min-height: 120px; }
.cart-body::-webkit-scrollbar { width: 3px; }
.cart-body::-webkit-scrollbar-thumb { background: var(--dim); border-radius: 3px; }

.cart-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 2.5rem 0; color: var(--muted); gap: .75rem; text-align: center;
}
.cart-empty svg { opacity: .18; }
.cart-empty p { font-size: .8rem; line-height: 1.65; font-family: var(--font-mono); }

/* Cart Item */
.cart-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .8rem 0; border-bottom: 1px solid rgba(255,255,255,.04);
    animation: itemIn .2s ease;
}
@keyframes itemIn { from{opacity:0;transform:translateX(10px)} to{opacity:1;transform:none} }
.cart-item:last-child { border-bottom: none; }

.cart-thumb {
    width: 38px; height: 38px; border-radius: 9px; flex-shrink: 0;
    background: var(--card2); border: 1px solid var(--border);
    overflow: hidden; display: flex; align-items: center; justify-content: center;
}
.cart-thumb img { width: 100%; height: 100%; object-fit: cover; }
.cart-thumb-init {
    font-family: var(--font-mono); font-size: .72rem; font-weight: 700; color: var(--accent);
}

.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name {
    font-size: .81rem; font-weight: 700;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: .15rem;
}
.cart-item-unit { font-size: .7rem; color: var(--muted); font-family: var(--font-mono); }

.qty-group { display: flex; align-items: center; gap: .35rem; flex-shrink: 0; }
.qty-btn {
    width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0;
    background: var(--card2); border: 1px solid var(--border2);
    color: var(--text); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: .15s;
}
.qty-btn:hover { background: var(--accent); border-color: var(--accent); }
.qty-btn.danger:hover { background: var(--danger); border-color: var(--danger); }
.qty-num {
    font-family: var(--font-mono); font-size: .85rem; font-weight: 700;
    min-width: 24px; text-align: center;
}

.cart-item-sub {
    font-family: var(--font-mono); font-size: .8rem; font-weight: 700;
    color: var(--teal); min-width: 72px; text-align: right; flex-shrink: 0;
}

/* Cart Footer */
.cart-footer { padding: 1rem 1.5rem 1.5rem; border-top: 1px solid var(--border); flex-shrink: 0; background: var(--card); }

.form-group { margin-bottom: .875rem; }
.form-lbl {
    display: block; font-size: .68rem; font-family: var(--font-mono);
    text-transform: uppercase; letter-spacing: .08em; color: var(--muted); margin-bottom: .35rem;
}
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: .875rem; }

.pos-select, .pos-input {
    width: 100%; padding: .6rem .875rem;
    background: var(--card2); border: 1px solid var(--border2);
    border-radius: 9px; color: var(--text); font-family: var(--font-ui);
    font-size: .84rem; outline: none; transition: .2s;
}
.pos-select:focus, .pos-input:focus { border-color: var(--accent); }
option { background: #1c1c28; }

.due-hint { font-size: .72rem; color: var(--danger); font-family: var(--font-mono); margin-top: .3rem; display: none; }

/* Totals */
.totals-box {
    background: var(--card2); border: 1px solid var(--border);
    border-radius: 12px; padding: .85rem 1rem; margin-bottom: .85rem;
}
.total-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .28rem 0; font-size: .8rem;
}
.t-label { color: var(--muted); }
.t-val { font-family: var(--font-mono); font-weight: 600; }
.t-disc { color: var(--danger); }
.t-tax { color: var(--warn); }
.total-grand {
    padding-top: .75rem; margin-top: .5rem; border-top: 1px solid var(--border);
    font-size: 1rem; font-weight: 800;
}
.t-grand { font-family: var(--font-mono); font-size: 1.05rem; color: var(--teal); font-weight: 800; }

/* Checkout */
.checkout-btn {
    width: 100%; padding: 1rem; display: flex; align-items: center; justify-content: center; gap: .6rem;
    background: var(--accent); border: none; border-radius: 13px;
    color: #fff; font-family: var(--font-ui); font-size: .95rem; font-weight: 800;
    cursor: pointer; letter-spacing: .02em; transition: .25s; margin-top: .25rem;
}
.checkout-btn:hover { background: #9b8ff0; transform: translateY(-2px); box-shadow: 0 12px 28px rgba(124,111,224,.3); }
.checkout-btn:active { transform: scale(.98); }

/* Toast notifications */
.toast {
    position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
    background: var(--card2); border-radius: 12px; padding: .75rem 1.1rem;
    font-size: .8rem; border: 1px solid var(--border2); max-width: 300px;
    animation: toastIn .3s ease; pointer-events: none;
}
.toast.t-success { border-color: rgba(74,222,128,.35); color: var(--success); }
.toast.t-warn    { border-color: rgba(251,191,36,.35); color: var(--warn); }
.toast.t-error   { border-color: rgba(248,113,113,.35); color: var(--danger); }
@keyframes toastIn { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:none} }

/* Responsive */
@media (max-width: 1150px) {
    .pos-shell { grid-template-columns: 1fr; }
    .pos-right { position: static; max-height: none; }
    .catalog-card { height: auto; min-height: 400px; }
}
</style>

<!-- ═══════════════ JAVASCRIPT ═══════════════ -->
<script>
// ─── State ───────────────────────────────────────────────
let cart = [];
let manualPaid = false;

// Product data injected from PHP
const PRODUCTS = <?= json_encode(array_map(function($p) {
    return [
        'id'    => (int)$p['id'],
        'name'  => $p['item_name'],
        'price' => (float)$p['price'],
        'stock' => (int)$p['quantity'],
        'image' => $p['image'] ?? null,
    ];
}, $products)) ?>;

// ─── Product Click Handler ────────────────────────────────
function handleProductClick(el) {
    const id    = parseInt(el.dataset.id);
    const stock = parseInt(el.dataset.stock);
    const name  = el.dataset.name;

    if (stock <= 0) {
        toast('"' + name + '" is out of stock.', 'error');
        return;
    }

    addToCart(id);

    if (stock < 10) {
        toast(name + ' — only ' + stock + ' units left!', 'warn');
    }
}

// ─── Cart Operations ──────────────────────────────────────
function addToCart(id, qty = 1) {
    const product = PRODUCTS.find(p => p.id === id);
    if (!product) return;

    const existing = cart.find(c => c.id === id);
    if (existing) {
        const newQty = existing.qty + qty;
        if (newQty > product.stock) {
            toast('Cannot exceed available stock for ' + product.name, 'warn');
            return;
        }
        existing.qty = newQty;
    } else {
        cart.push({ id, name: product.name, price: product.price, qty, stock: product.stock, image: product.image });
    }
    updateAll();
}

function changeQty(id, delta) {
    const idx = cart.findIndex(c => c.id === id);
    if (idx < 0) return;
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    updateAll();
}

function resetCart() {
    if (cart.length === 0) return;
    Swal.fire({
        title: 'Clear cart?',
        text: 'All items will be removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7c6fe0',
        cancelButtonColor: '#252535',
        confirmButtonText: 'Yes, clear',
        background: '#16161f',
        color: '#e4e4f0'
    }).then(r => { if (r.isConfirmed) { cart = []; updateAll(); } });
}

// ─── Render ───────────────────────────────────────────────
function updateAll() {
    renderCartItems();
    updateProductBubbles();
    calcTotals();
    syncHiddenInputs();
}

function initials(name) {
    return name.split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '??';
}

function renderCartItems() {
    const body    = document.getElementById('cartBody');
    const itemsEl = document.getElementById('cartItems');
    const pill    = document.getElementById('cartPill');
    const total   = cart.reduce((s, i) => s + i.qty, 0);
    pill.textContent = total;

    if (cart.length === 0) {
        itemsEl.innerHTML = '';
        document.getElementById('cartEmpty').style.display = 'flex';
        return;
    }
    document.getElementById('cartEmpty').style.display = 'none';

    itemsEl.innerHTML = cart.map(item => {
        const sub  = item.price * item.qty;
        const init = initials(item.name);
        const thumbHtml = item.image
            ? `<img src="${item.image}" alt="">`
            : `<span class="cart-thumb-init">${init}</span>`;

        return `<div class="cart-item" data-id="${item.id}">
            <div class="cart-thumb">${thumbHtml}</div>
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-unit">৳${item.price.toLocaleString('en-US', {minimumFractionDigits:2})} × ${item.qty}</div>
            </div>
            <div class="qty-group">
                <button type="button" class="qty-btn danger" onclick="changeQty(${item.id}, -1)" title="Remove one">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <span class="qty-num">${item.qty}</span>
                <button type="button" class="qty-btn" onclick="changeQty(${item.id}, 1)" title="Add one">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
            </div>
            <div class="cart-item-sub">৳${sub.toLocaleString('en-US', {minimumFractionDigits:2})}</div>
        </div>`;
    }).join('');
}

function updateProductBubbles() {
    // Reset all
    document.querySelectorAll('.p-card').forEach(card => {
        const bubble = document.getElementById('bubble-' + card.dataset.id);
        const cartItem = cart.find(c => c.id == card.dataset.id);
        if (cartItem && cartItem.qty > 0) {
            card.classList.add('in-cart');
            bubble.style.display = 'flex';
            bubble.textContent = cartItem.qty;
        } else {
            card.classList.remove('in-cart');
            bubble.style.display = 'none';
        }
    });
}

// ─── Totals ───────────────────────────────────────────────
function calcTotals() {
    const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
    const dPct     = parseFloat(document.getElementById('discountPercent').value) || 0;
    const tPct     = parseFloat(document.getElementById('taxPercent').value) || 0;
    const dAmt     = subtotal * (dPct / 100);
    const taxable  = subtotal - dAmt;
    const tAmt     = taxable * (tPct / 100);
    const grand    = taxable + tAmt;

    const fmt = v => '৳' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('subtotalDisp').textContent  = fmt(subtotal);
    document.getElementById('discountDisp').textContent  = '-' + fmt(dAmt);
    document.getElementById('taxDisp').textContent       = fmt(tAmt);
    document.getElementById('grandDisp').textContent     = fmt(grand);

    if (!manualPaid) {
        document.getElementById('amountPaid').value = grand.toFixed(2);
    }
    updateDueHint(grand);
    return { subtotal, dAmt, tAmt, grand };
}

function updateDueHint(grand) {
    if (grand === undefined) {
        const txt = document.getElementById('grandDisp').textContent.replace('৳','').replace(/,/g,'');
        grand = parseFloat(txt) || 0;
    }
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const hint = document.getElementById('dueHint');
    if (paid < grand - 0.001) {
        hint.textContent = 'Due: ৳' + (grand - paid).toLocaleString('en-US', { minimumFractionDigits: 2 });
        hint.style.display = 'block';
    } else {
        hint.style.display = 'none';
    }
}

function syncHiddenInputs() {
    const { subtotal, dAmt, tAmt } = calcTotals();
    document.getElementById('cartDataInput').value    = JSON.stringify(cart);
    document.getElementById('subtotalInput').value    = subtotal.toFixed(2);
    document.getElementById('discountAmtInput').value = dAmt.toFixed(2);
    document.getElementById('taxAmtInput').value      = tAmt.toFixed(2);
}

// ─── Event Listeners ──────────────────────────────────────
document.getElementById('discountPercent').addEventListener('input', () => { manualPaid = false; updateAll(); });
document.getElementById('taxPercent').addEventListener('input', () => { manualPaid = false; updateAll(); });
document.getElementById('amountPaid').addEventListener('input', function() {
    manualPaid = true;
    const txt = document.getElementById('grandDisp').textContent.replace('৳','').replace(/,/g,'');
    updateDueHint(parseFloat(txt) || 0);
});

// Product search filter
let cachedCards = null;
function filterProducts() {
    if (!cachedCards) cachedCards = Array.from(document.querySelectorAll('.p-card'));
    const q = document.getElementById('productSearch').value.toLowerCase();
    let visible = 0;
    requestAnimationFrame(() => {
        cachedCards.forEach(card => {
            const match = card.dataset.name.toLowerCase().includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('catalogCount').textContent = visible + ' items';
    });
}

// ─── Form Submit ──────────────────────────────────────────
document.getElementById('posForm').addEventListener('submit', function(e) {
    syncHiddenInputs();

    if (cart.length === 0) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'Empty Cart', text: 'Add at least one product.', background: '#16161f', color: '#e4e4f0', confirmButtonColor: '#7c6fe0' });
        return;
    }

    const customerId = document.getElementById('customerSelect').value;
    const grandTxt   = document.getElementById('grandDisp').textContent.replace('৳','').replace(/,/g,'');
    const grand      = parseFloat(grandTxt) || 0;
    const paid       = parseFloat(document.getElementById('amountPaid').value) || 0;

    if (!customerId && paid < grand - 0.001) {
        e.preventDefault();
        Swal.fire({
            icon: 'error', title: 'Payment Required',
            text: 'Walk-in customers must pay the full amount. Register a customer to allow credit sales.',
            background: '#16161f', color: '#e4e4f0', confirmButtonColor: '#7c6fe0'
        });
    }
});

// ─── AI Neural Command ────────────────────────────────────
const commandInput = document.getElementById('aiCommandInput');
const micBtn = document.getElementById('micBtn');
const langToggle = document.getElementById('langToggle');

// Language State
let currentVoiceLang = localStorage.getItem('pos_voice_lang') || 'bn-BD';
updateLangUI();

function updateLangUI() {
    if (currentVoiceLang === 'bn-BD') {
        langToggle.classList.add('is-bn');
        langToggle.classList.remove('is-en');
    } else {
        langToggle.classList.add('is-en');
        langToggle.classList.remove('is-bn');
    }
}

langToggle.addEventListener('click', () => {
    currentVoiceLang = (currentVoiceLang === 'bn-BD') ? 'en-US' : 'bn-BD';
    localStorage.setItem('pos_voice_lang', currentVoiceLang);
    updateLangUI();
    toast(`Voice tracking set to ${currentVoiceLang === 'bn-BD' ? 'Bangla' : 'English'}`, 'info');
    if (recognition) recognition.lang = currentVoiceLang;
});

// Voice Command Logic
let recognition = null;
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = currentVoiceLang;

    recognition.onstart = () => {
        micBtn.classList.add('recording');
        commandInput.placeholder = "Listening...";
    };

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        commandInput.value = transcript;
        submitAICommand(transcript);
    };

    recognition.onerror = (event) => {
        console.error('Speech recognition error:', event.error);
        toast('Voice error: ' + event.error, 'error');
        stopRecording();
    };

    recognition.onend = () => {
        stopRecording();
    };
} else {
    micBtn.style.display = 'none';
}

function stopRecording() {
    micBtn.classList.remove('recording');
    commandInput.placeholder = "e.g. Add 3 SSDs for Ayesha with 10% discount...";
}

micBtn.addEventListener('click', () => {
    if (micBtn.classList.contains('recording')) {
        recognition.stop();
    } else {
        commandInput.value = '';
        recognition.start();
    }
});

commandInput.addEventListener('keypress', function(e) {
    if (e.key !== 'Enter') return;
    submitAICommand(this.value.trim());
});

function submitAICommand(command) {
    if (!command) return;

    const loader = document.getElementById('aiLoader');
    commandInput.disabled = true;
    loader.style.display = 'block';

    const formData = new FormData();
    formData.append('command', command);

    fetch('api/pos_ai_helper.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success && Array.isArray(data.actions)) {
                processAIActions(data.actions);
                commandInput.value = '';
                toast('Neural command executed', 'success');
            } else {
                Swal.fire({ icon: 'error', title: 'AI Confusion', text: data.error || 'Could not parse command.', background: '#16161f', color: '#e4e4f0', confirmButtonColor: '#7c6fe0' });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Neural Link Error', text: err.message, background: '#16161f', color: '#e4e4f0', confirmButtonColor: '#7c6fe0' });
        })
        .finally(() => {
            commandInput.disabled = false;
            loader.style.display = 'none';
            commandInput.focus();
        });
}

function processAIActions(actions) {
    actions.forEach(action => {
        switch (action.action) {
            case 'add_item':
                addToCart(parseInt(action.id), parseInt(action.qty) || 1);
                break;
            case 'set_customer':
                document.getElementById('customerSelect').value = action.id;
                toast('Customer set', 'success');
                break;
            case 'set_discount':
                document.getElementById('discountPercent').value = action.percent;
                manualPaid = false;
                updateAll();
                toast('Discount set to ' + action.percent + '%', 'success');
                break;
            case 'set_tax':
                document.getElementById('taxPercent').value = action.percent;
                manualPaid = false;
                updateAll();
                toast('Tax set to ' + action.percent + '%', 'success');
                break;
            case 'finalize_sale':
                updateAll();
                setTimeout(() => {
                    if (cart.length > 0) {
                        toast('Finalizing transaction...', 'info');
                        document.getElementById('posForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                        // If dispatchEvent didn't trigger validation stop, we submit manually if valid
                        const checkoutBtn = document.querySelector('button[type="submit"]');
                        if (checkoutBtn) checkoutBtn.click();
                    } else {
                        toast('Cart is empty, cannot finalize.', 'error');
                    }
                }, 400);
                break;
        }
    });
    updateAll();
}

// ─── Toast Utility ────────────────────────────────────────
function toast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = 'toast t-' + type;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transition = 'opacity .3s';
        setTimeout(() => el.remove(), 300);
    }, 2800);
}
</script>
</body>
</html>