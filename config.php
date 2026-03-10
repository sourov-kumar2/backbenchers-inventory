<?php
// Database configuration
$host = 'localhost';
$db   = 'inveentory';
$user = 'sourov';
$pass = 'M0nPMsEN3B7ealJ9N7xM';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
