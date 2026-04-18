<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if ($id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: customers.php?deleted=1');
    } catch (PDOException $e) {
        // If customer has sales, they might not be deletable depending on FK constraints
        header('Location: customers.php?error=1');
    }
} else {
    header('Location: customers.php');
}
exit();
