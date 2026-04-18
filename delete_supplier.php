<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM suppliers WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: suppliers.php?deleted=1');
exit();
