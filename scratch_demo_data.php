<?php
require 'config.php';

try {
    // 1. Clear existing demo data (Optional: user didn't ask, but good for a clean start)
    // $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE sale_items; TRUNCATE sales; TRUNCATE products; TRUNCATE customers; SET FOREIGN_KEY_CHECKS = 1;");

    // 2. Insert Products
    $products = [
        ['Luxe Pro Headphones', 'Noise-cancelling wireless headphones with 40h battery.', 50, 249.99],
        ['UltraTab S10', 'High-definition 10-inch tablet with 128GB storage.', 15, 499.00],
        ['Logi Keyboard K380', 'Compact multi-device Bluetooth keyboard.', 5, 39.99], // Low Stock
        ['MacBook Retina Display', 'Renewed 13-inch MacBook with M1 chip.', 0, 999.00], // Out of Stock
        ['Office Desk Mate', 'Ergonomic mousepad with wrist rest.', 100, 19.50]
    ];

    $stmt = $pdo->prepare("INSERT INTO products (item_name, description, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($products as $p) {
        $stmt->execute($p);
        echo "Product inserted: {$p[0]}\n";
    }

    // 3. Insert Customers
    $customers = [
        ['James Anderson', '555-0101', 'james@example.com', '123 Tech Lane, NY'],
        ['Sarah Jenkins', '555-0102', 'sarah@example.com', '456 Main St, CA'],
        ['Robert Wilson', '555-0103', 'robert@example.com', '789 Oak Ave, TX']
    ];

    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
    foreach ($customers as $c) {
        $stmt->execute($c);
        echo "Customer inserted: {$c[0]}\n";
    }

    // 4. Insert Demo Sales
    // Get some IDs
    $prod_ids = $pdo->query("SELECT id, price FROM products")->fetchAll(PDO::FETCH_KEY_PAIR);
    $cust_ids = $pdo->query("SELECT id FROM customers")->fetchAll(PDO::FETCH_COLUMN);

    // Sale 1: James buys 2 Headphones
    $pdo->exec("INSERT INTO sales (customer_id, total_amount, payment_method) VALUES ({$cust_ids[0]}, 499.98, 'Card')");
    $sale_id = $pdo->lastInsertId();
    reset($prod_ids);
    $p_id = key($prod_ids);
    $pdo->exec("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES ($sale_id, $p_id, 2, 249.99, 499.98)");

    // Sale 2: Sarah buys 1 Tablet
    $pdo->exec("INSERT INTO sales (customer_id, total_amount, payment_method) VALUES ({$cust_ids[1]}, 499.00, 'Cash')");
    $sale_id = $pdo->lastInsertId();
    next($prod_ids); // Tablet is next
    $p_id = key($prod_ids);
    $pdo->exec("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES ($sale_id, $p_id, 1, 499.00, 499.00)");

    // Sale 3: Walk-in buys Keyboard and Mousepad
    $pdo->exec("INSERT INTO sales (customer_id, total_amount, payment_method) VALUES (NULL, 59.49, 'Card')");
    $sale_id = $pdo->lastInsertId();
    // Keyboard
    $keys = array_keys($prod_ids);
    $pdo->exec("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES ($sale_id, {$keys[2]}, 1, 39.99, 39.99)");
    // Mousepad
    $pdo->exec("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES ($sale_id, {$keys[4]}, 1, 19.50, 19.50)");

    echo "\nDemo data generation complete!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
