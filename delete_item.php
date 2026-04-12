<?php
require 'auth.php';
require 'config.php';

// Accept both GET and POST
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    header('Location: inventory.php');
    exit();
}

// Verify item exists before deleting
$stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: inventory.php?error=not_found');
    exit();
}

// Perform deletion
$stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
$stmt->execute([$id]);

header('Location: inventory.php?deleted=1');
exit();