<?php
require 'config.php';

$products = [
    ['item_name' => 'Master CPU Core i7', 'description' => 'High-performance processor for gaming and professional tasks.'],
    ['item_name' => 'DDR4 RAM 16GB', 'description' => 'High-speed desktop memory module for seamless multitasking.'],
    ['item_name' => 'NVMe SSD 1TB', 'description' => 'Ultra-fast storage solution with lightning-quick boot times.'],
    ['item_name' => 'Mechanical Keyboard RGB', 'description' => 'Tactile feedback with customizable lighting effects.'],
    ['item_name' => 'Precision Gaming Mouse', 'description' => 'High-DPI optical sensor for extreme accuracy.'],
    ['item_name' => '27-inch 4K Monitor', 'description' => 'Crisp Ultra HD display with vibrant color reproduction.'],
    ['item_name' => 'Wireless Router AX3000', 'description' => 'Latest Wi-Fi 6 technology for high-speed connectivity.'],
    ['item_name' => 'External Hard Drive 2TB', 'description' => 'Portable storage for backups and large media files.'],
    ['item_name' => 'Webcam 1080p', 'description' => 'Full HD video camera for streaming and conferencing.'],
    ['item_name' => 'Bluetooth Headset', 'description' => 'Extended battery life and crystal-clear audio quality.']
];

try {
    $stmt = $pdo->prepare('INSERT INTO products (item_name, description, quantity, price, image) VALUES (?, ?, 0, 0, NULL)');
    
    foreach ($products as $p) {
        $stmt->execute([$p['item_name'], $p['item_name'] . ' - ' . $p['description']]);
    }
    
    echo "10 products added successfully with 0 stock and no images.\n";
} catch (PDOException $e) {
    die("Error populating products: " . $e->getMessage());
}
?>
