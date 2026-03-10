<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['item_name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $qty = $_POST['quantity'] ?? 0;
    $price = $_POST['price'] ?? 0.00;
    $stmt = $pdo->prepare('INSERT INTO products (item_name, description, quantity, price) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $desc, $qty, $price]);
    header('Location: inventory.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Item</title>
</head>
<body>
<?php include 'partials/navbar.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div style="margin-left:200px; padding:20px;">
    <h2>Add New Item</h2>
    <form method="post">
        <input type="text" name="item_name" placeholder="Item Name" required><br>
        <textarea name="description" placeholder="Description"></textarea><br>
        <input type="number" name="quantity" placeholder="Quantity" required><br>
        <input type="number" step="0.01" name="price" placeholder="Price" required><br>
        <button type="submit">Add Item</button>
    </form>
</div>
<?php include 'partials/footer.php'; ?>
</body>
</html>
