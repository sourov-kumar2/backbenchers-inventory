<?php
// Database configuration
$host = 'localhost';
// $db   = 'inveentory';
$db   = 'inventory';
$user = 'root';
$pass = 'root';
// $user = 'sourov';
// $pass = '8oTHg9YHDa2Q8LdcZv27';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Fetch System Settings Globally
    $sys_stmt = $pdo->query("SELECT * FROM system_settings WHERE id = 1");
    $sys = $sys_stmt->fetch() ?: [
        'system_name' => 'Backbenchers Inventory',
        'system_logo' => null,
        'system_details' => '',
        'groq_api_key' => '',
        'groq_model' => 'llama-3.3-70b-versatile'
    ];

    // AI Intelligence Configuration (Dynamic)
    define('GROQ_API_KEY', $sys['groq_api_key'] ?? '');
    define('GROQ_MODEL', $sys['groq_model'] ?? 'llama-3.3-70b-versatile');

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
