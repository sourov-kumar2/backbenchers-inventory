<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: users.php');
    exit();
}

// 1. Security Check: Cannot delete User ID 1 (Root Admin)
if ($id == 1) {
    header('Location: users.php?error=root_protected');
    exit();
}

// 2. Fetch User to clean up image
$stmt = $pdo->prepare('SELECT image FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($user) {
    // 3. Delete from DB
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    if ($stmt->execute([$id])) {
        // 4. Delete profile image file if exists
        if ($user['image'] && file_exists($user['image'])) {
            unlink($user['image']);
        }
        header('Location: users.php?deleted=1');
        exit();
    }
}

header('Location: users.php?error=delete_failed');
exit();
