<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: sales_list.php'); exit(); }

// 1. Fetch Sale Header
$stmt = $pdo->prepare('SELECT s.*, c.name, c.email, c.phone, c.address 
                       FROM sales s 
                       LEFT JOIN customers c ON s.customer_id = c.id 
                       WHERE s.id = ?');
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) { header('Location: sales_list.php'); exit(); }

// 2. Fetch Sale Items
$stmt = $pdo->prepare('SELECT si.*, p.item_name, p.image 
                       FROM sale_items si 
                       JOIN products p ON si.product_id = p.id 
                       WHERE si.sale_id = ?');
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Invoice INV-' . str_pad($sale['id'], 5, '0', STR_PAD_LEFT);
include 'partials/head.php'; 
?>
<body class="invoice-body">
    <div class="invoice-wrapper">
        <div class="invoice-controls no-print">
            <a href="sales_list.php" class="btn btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Return to History
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Invoice
            </button>
        </div>

        <div class="invoice-card glass animate-fade-in">
            <!-- Header Section -->
            <div class="invoice-header">
                <div class="brand-side">
                    <h1 class="brand-title">BACKBENCHERS</h1>
                    <div class="brand-details">
                        <p>Inventory & Retail POS Systems</p>
                        <p>Terminal 4, Tech District, BD</p>
                    </div>
                </div>
                <div class="meta-side">
                    <h2 class="doc-type">INVOICE</h2>
                    <div class="meta-info">
                        <div class="meta-row">
                            <span class="m-label">Invoice No:</span>
                            <span class="m-val">#INV-<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="m-label">Issue Date:</span>
                            <span class="m-val"><?= date('F d, Y', strtotime($sale['sale_date'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adressing Section -->
            <div class="address-grid">
                <div class="address-card">
                    <h3 class="addr-label">Bill From</h3>
                    <div class="addr-content">
                        <p class="company-name">Backbenchers Terminal</p>
                        <p>+880 1234-567890</p>
                        <p>terminal@backbenchers.inventory</p>
                        <p>Level 2, Business Plaza, Dhaka</p>
                    </div>
                </div>
                <div class="address-card">
                    <h3 class="addr-label">Bill To</h3>
                    <div class="addr-content">
                        <?php if ($sale['name']): ?>
                            <p class="client-name"><?= htmlspecialchars($sale['name']) ?></p>
                            <p><?= htmlspecialchars($sale['phone'] ?: 'N/A') ?></p>
                            <p><?= htmlspecialchars($sale['email'] ?: 'N/A') ?></p>
                            <p><?= nl2br(htmlspecialchars($sale['address'] ?: 'Customer Address N/A')) ?></p>
                        <?php else: ?>
                            <p class="client-name">Walk-in Customer</p>
                            <p>Standard Retail Transaction</p>
                            <p>Point of Sale Entry</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-outline">
                <table class="inv-data-table">
                    <thead>
                        <tr>
                            <th class="col-desc">Product Description</th>
                            <th class="col-price text-right">Unit Price</th>
                            <th class="col-qty text-center">Qty</th>
                            <th class="col-total text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="col-desc">
                                    <span class="p-item-title"><?= htmlspecialchars($item['item_name']) ?></span>
                                </td>
                                <td class="col-price text-right">৳<?= number_format($item['unit_price'], 2) ?></td>
                                <td class="col-qty text-center"><?= $item['quantity'] ?></td>
                                <td class="col-total text-right">৳<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Section -->
            <div class="summary-bracket">
                <div class="payment-note">
                    <span class="note-label">Payment Information:</span>
                    <p class="note-val">Method: <?= htmlspecialchars($sale['payment_method']) ?></p>
                    <p class="note-val">Status: <?= $sale['amount_due'] > 0 ? '<span style="color: #ef4444;">Pending Due</span>' : '<span style="color: #10b981;">Paid in Full</span>' ?></p>
                </div>
                <div class="totals-box">
                    <div class="total-row">
                        <span class="t-label">Subtotal</span>
                        <span class="t-val">৳<?= number_format($sale['subtotal_amount'] ?: $sale['total_amount'], 2) ?></span>
                    </div>
                    <?php if ($sale['discount_amount'] > 0): ?>
                    <div class="total-row">
                        <span class="t-label">Discount</span>
                        <span class="t-val" style="color: #ef4444;">-৳<?= number_format($sale['discount_amount'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="total-row">
                        <span class="t-label">VAT / Tax</span>
                        <span class="t-val">৳<?= number_format($sale['tax_amount'], 2) ?></span>
                    </div>
                    <div class="total-row grand-divider">
                        <span class="t-label-grand">Final Total</span>
                        <span class="t-val-grand">৳<?= number_format($sale['total_amount'], 2) ?></span>
                    </div>
                    <div class="total-row" style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="t-label">Amount Paid</span>
                        <span class="t-val">৳<?= number_format($sale['amount_paid'], 2) ?></span>
                    </div>
                    <?php if ($sale['amount_due'] > 0): ?>
                    <div class="total-row">
                        <span class="t-label" style="color: #fca5a5;">Outstanding Due</span>
                        <span class="t-val" style="color: #ef4444; font-weight: 800;">৳<?= number_format($sale['amount_due'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer & Legal -->
            <div class="invoice-notices">
                <div class="terms-conditions">
                    <span class="terms-label">Terms & Conditions:</span>
                    <ul class="terms-list">
                        <li>Please check all items before leaving the counter.</li>
                        <li>Items once sold are non-refundable after 7 days of purchase.</li>
                        <li>Original receipt required for any warranty claims or exchanges.</li>
                    </ul>
                </div>
                <p class="thanks-msg">Thank you for your business!</p>
                <div class="legal-disclaimer">
                    <p>This is a computer-generated document. No signature required.</p>
                    <p>Backbenchers POS Inventory System v2.0</p>
                </div>
            </div>
        </div>
    </div>

    <style>
    .invoice-body { background: #0b0f1a; padding: 3rem 1rem; color: #e2e8f0; }
    .invoice-wrapper { max-width: 900px; margin: 0 auto; }
    .invoice-controls { display: flex; justify-content: space-between; margin-bottom: 2rem; }

    .invoice-card { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 6rem 4rem 4rem 4rem; position: relative; overflow: hidden; }
    
    .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4.5rem; border-bottom: 1.5px solid rgba(139, 92, 246, 0.2); padding-bottom: 3rem; }
    .brand-title { font-size: 1.8rem; font-weight: 900; color: #8b5cf6; letter-spacing: 0.15em; margin-bottom: 0.75rem; }
    .brand-details p { font-size: 0.85rem; color: #94a3b8; line-height: 1.5; }

    .doc-type { font-size: 3rem; font-weight: 200; letter-spacing: 0.25em; text-align: right; line-height: 1; margin-bottom: 1.5rem; color: #f8fafc; }
    .meta-row { display: flex; justify-content: flex-end; gap: 1rem; margin-bottom: 0.5rem; font-size: 0.9rem; }
    .m-label { color: #64748b; font-weight: 600; }
    .m-val { color: #cbd5e1; font-weight: 700; min-width: 120px; text-align: right; }

    .address-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 4.5rem; }
    .addr-label { font-size: 0.75rem; color: #8b5cf6; text-transform: uppercase; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem; margin-bottom: 1rem; letter-spacing: 1px; }
    .company-name, .client-name { font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin-bottom: 0.5rem; }
    .addr-content p { font-size: 0.9rem; color: #94a3b8; line-height: 1.6; margin-bottom: 0.2rem; }

    .table-outline { margin-bottom: 3rem; }
    .inv-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .inv-data-table th { background: rgba(139, 92, 246, 0.05); color: #8b5cf6; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; padding: 1.25rem 1rem; border-bottom: 1px solid rgba(139, 92, 246, 0.2); }
    .inv-data-table td { padding: 1.25rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; font-size: 0.9rem; }
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .p-item-title { font-weight: 600; color: #f8fafc; }

    .summary-bracket { display: flex; justify-content: space-between; align-items: flex-start; gap: 4rem; }
    .payment-note { flex: 1; padding: 1.5rem; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); }
    .note-label { display: block; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; color: #94a3b8; margin-bottom: 0.75rem; }
    .note-val { font-size: 0.85rem; color: #cbd5e1; margin-bottom: 0.25rem; font-weight: 600; }

    .totals-box { min-width: 320px; }
    .total-row { display: flex; justify-content: space-between; padding: 0.75rem 0; font-size: 0.95rem; }
    .t-label { color: #94a3b8; }
    .t-val { color: #f8fafc; font-weight: 600; }
    .grand-divider { border-top: 2px solid #8b5cf6; margin-top: 1rem; padding-top: 1.5rem; }
    .t-label-grand { font-size: 1.1rem; font-weight: 800; color: #f8fafc; }
    .t-val-grand { font-size: 1.8rem; font-weight: 900; color: #8b5cf6; }

    .invoice-notices { margin-top: 6rem; text-align: center; border-top: 1px dotted rgba(255,255,255,0.1); padding-top: 3rem; }
    .terms-conditions { text-align: left; background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 12px; margin-bottom: 2.5rem; border: 1px solid rgba(255,255,255,0.05); }
    .terms-label { display: block; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; color: #8b5cf6; margin-bottom: 0.75rem; letter-spacing: 1px; }
    .terms-list { list-style: disc; padding-left: 1.5rem; }
    .terms-list li { font-size: 0.8rem; color: #94a3b8; margin-bottom: 0.4rem; line-height: 1.4; }
    
    .thanks-msg { font-size: 1.1rem; font-weight: 700; color: #cbd5e1; margin-bottom: 1rem; }
    .legal-disclaimer { font-size: 0.75rem; color: #64748b; line-height: 1.5; }

    @media print {
        .invoice-body { background: white !important; color: black !important; padding: 0 !important; }
        .invoice-card { background: none !important; border: none !important; padding: 0 !important; color: black !important; box-shadow: none !important; overflow: visible; }
        .brand-title, .doc-type, .t-val-grand, .addr-label, .inv-data-table th { color: black !important; border-color: #eee !important; }
        .grand-divider { border-top-color: black !important; }
        .invoice-card * { color: black !important; }
        .no-print { display: none !important; }
        .payment-note { background: #f8fafc !important; border: 1px solid #ddd !important; }
        .inv-data-table th { background: #f1f5f9 !important; }
    }
    </style>
</body>
</html>
