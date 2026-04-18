<?php
// Database configuration
$host = 'localhost';
$db   = 'inventory';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Fetch System Settings Globally
    $sys_stmt = $pdo->query("SELECT * FROM system_settings WHERE id = 1");
    $sys = $sys_stmt->fetch() ?: [
        'system_name' => 'Backbenchers Inventory',
        'system_logo' => null,
        'system_details' => ''
    ];

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
