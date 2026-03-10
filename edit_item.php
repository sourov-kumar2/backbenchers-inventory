<?php
require 'auth.php';
require 'config.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: inventory.php'); exit(); }
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { header('Location: inventory.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['item_name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $qty = $_POST['quantity'] ?? 0;
    $price = $_POST['price'] ?? 0.00;
    $stmt = $pdo->prepare('UPDATE products SET item_name = ?, description = ?, quantity = ?, price = ? WHERE id = ?');
    $stmt->execute([$name, $desc, $qty, $price, $id]);
    header('Location: inventory.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Item</title>
</head>
<body>
<?php include 'partials/navbar.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div style="margin-left:200px; padding:20px;">
    <h2>Edit Item</h2>
    <form method="post">
        <input type="text" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required><br>
        <textarea name="description"><?= htmlspecialchars($item['description']) ?></textarea><br>
        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" required><br>
        <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required><br>
        <button type="submit">Update Item</button>
    </form>
</div>
<?php include 'partials/footer.php'; ?>
</body>
</html>
