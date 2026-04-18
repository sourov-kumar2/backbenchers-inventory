<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?: null;
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    
    // Financial Fields
    $subtotal_amount = (float)($_POST['subtotal_amount'] ?? 0);
    $discount_amount = (float)($_POST['discount_amount'] ?? 0);
    $tax_amount      = (float)($_POST['tax_amount'] ?? 0);
    
    // Grand Total calculation
    $total_amount = ($subtotal_amount - $discount_amount) + $tax_amount;

    $cart_json = $_POST['cart_data'] ?? '[]';
    $cart = json_decode($cart_json, true);

    if (empty($cart)) {
        header('Location: pos.php?error=empty_cart');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert Sale Header with Taxes and Discounts
        $stmt = $pdo->prepare('INSERT INTO sales (customer_id, subtotal_amount, total_amount, discount_amount, tax_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $customer_id, 
            $subtotal_amount, 
            $total_amount, 
            $discount_amount, 
            $tax_amount, 
            $payment_method
        ]);
        $sale_id = $pdo->lastInsertId();

        // 2. Insert Sale Items & Update Inventory
        $stmtItem = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
        $stmtUpdate = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');

        foreach ($cart as $item) {
            $subtotal = $item['price'] * $item['qty'];
            
            // Insert line item
            $stmtItem->execute([
                $sale_id, 
                $item['id'], 
                $item['qty'], 
                $item['price'], 
                $subtotal
            ]);

            // Deduct stock
            $stmtUpdate->execute([$item['qty'], $item['id']]);
        }

        $pdo->commit();
        header('Location: pos.php?success=1&sale_id=' . $sale_id);
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: pos.php?error=' . urlencode($e->getMessage()));
    }
    exit();
}
header('Location: pos.php');
exit();
