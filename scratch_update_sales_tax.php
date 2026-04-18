<?php
require 'config.php';

try {
    $sql = "ALTER TABLE sales 
            ADD COLUMN subtotal_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER customer_id,
            ADD COLUMN discount_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER total_amount,
            ADD COLUMN tax_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER discount_amount;";

    $pdo->exec($sql);
    
    // Optional: Populate subtotal_amount with existing total_amount for old records
    $pdo->exec("UPDATE sales SET subtotal_amount = total_amount WHERE subtotal_amount = 0");
    
    echo "Sales table updated successfully with Discount and Tax columns.\n";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
