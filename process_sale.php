<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?: null;
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    
    // Financial Fields
    $subtotal_amount = $_POST['subtotal_amount'] ?? 0;
    $discount_amount = $_POST['discount_amount'] ?? 0;
    $tax_amount     = $_POST['tax_amount']     ?? 0;
    $amount_paid    = $_POST['amount_paid']    ?? 0;
    
    $cart_data = json_decode($_POST['cart_data'], true);
    
    if (empty($cart_data)) {
        header('Location: pos.php?error=empty_cart');
        exit();
    }

    $total_amount = $subtotal_amount - $discount_amount + $tax_amount;
    $amount_due = $total_amount - $amount_paid;

    // Strict Validation: No Debt for Walk-ins
    if ($customer_id === null && $amount_due > 0) {
        header('Location: pos.php?error=walkin_debt');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Create Sale Record
        $stmt = $pdo->prepare('INSERT INTO sales (customer_id, subtotal_amount, total_amount, discount_amount, tax_amount, amount_paid, amount_due, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$customer_id, $subtotal_amount, $total_amount, $discount_amount, $tax_amount, $amount_paid, $amount_due, $payment_method]);
        $sale_id = $pdo->lastInsertId();

        // 2. Process Items and Inventory
        $stmt = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
        $updateStock = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');

        foreach ($cart_data as $item) {
            $stmt->execute([$sale_id, $item['id'], $item['qty'], $item['price'], $item['price'] * $item['qty']]);
            $updateStock->execute([$item['qty'], $item['id']]);
        }

        // 3. Update Customer Dues if applicable
        if ($customer_id && $amount_due > 0) {
            $stmt = $pdo->prepare('UPDATE customers SET total_due = total_due + ? WHERE id = ?');
            $stmt->execute([$amount_due, $customer_id]);
        }

        $pdo->commit();
        header("Location: view_invoice.php?id=$sale_id&success=1");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Transaction failed: " . $e->getMessage());
    }
}
?>
