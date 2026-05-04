<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pos.php');
    exit();
}

// ── Input sanitization ──────────────────────────────────────
$customer_id     = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
$payment_method  = in_array($_POST['payment_method'] ?? '', ['Cash','Card','Credits']) ? $_POST['payment_method'] : 'Cash';

$subtotal_amount = (float)($_POST['subtotal_amount'] ?? 0);
$discount_amount = (float)($_POST['discount_amount'] ?? 0);
$tax_amount      = (float)($_POST['tax_amount']      ?? 0);
$amount_paid     = (float)($_POST['amount_paid']     ?? 0);

$cart_data = json_decode($_POST['cart_data'] ?? '[]', true);

// ── Validation ──────────────────────────────────────────────
if (empty($cart_data) || !is_array($cart_data)) {
    header('Location: pos.php?error=empty_cart');
    exit();
}

$total_amount = $subtotal_amount - $discount_amount + $tax_amount;
$amount_due   = max(0, $total_amount - $amount_paid);

// Strict rule: walk-in customers cannot have outstanding dues
if ($customer_id === null && $amount_due > 0.001) {
    header('Location: pos.php?error=walkin_debt');
    exit();
}

// ── Transaction ─────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    // 1. Create sale record
    $stmt = $pdo->prepare('
        INSERT INTO sales
            (customer_id, subtotal_amount, total_amount, discount_amount, tax_amount, amount_paid, amount_due, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$customer_id, $subtotal_amount, $total_amount, $discount_amount, $tax_amount, $amount_paid, $amount_due, $payment_method]);
    $sale_id = (int)$pdo->lastInsertId();

    // 2. Insert line items & deduct stock
    $insertItem  = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
    $updateStock = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?');

    foreach ($cart_data as $item) {
        $product_id = (int)$item['id'];
        $qty        = (int)$item['qty'];
        $price      = (float)$item['price'];

        // Guard against over-selling
        $result = $updateStock->execute([$qty, $product_id, $qty]);
        if ($updateStock->rowCount() === 0) {
            throw new Exception("Insufficient stock for product ID $product_id");
        }

        $insertItem->execute([$sale_id, $product_id, $qty, $price, $price * $qty]);
    }

    // 3. Add dues to customer account if applicable
    if ($customer_id && $amount_due > 0.001) {
        $stmt = $pdo->prepare('UPDATE customers SET total_due = total_due + ? WHERE id = ?');
        $stmt->execute([$amount_due, $customer_id]);
    }

    $pdo->commit();
    header("Location: view_invoice.php?id=$sale_id&success=1");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    // Log the error properly in production
    error_log('Sale transaction failed: ' . $e->getMessage());
    header('Location: pos.php?error=transaction_failed');
    exit();
}