<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL AFTER item_name");
    echo "Column 'image' added to 'products' table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'image' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
