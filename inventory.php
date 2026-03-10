<?php
require 'auth.php';
require 'config.php';
$items = $pdo->query('SELECT * FROM products')->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory List</title>
</head>
<body>
<?php include 'partials/navbar.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div style="margin-left:200px; padding:20px;">
    <h2>Inventory List</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= $item['id'] ?></td>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= htmlspecialchars($item['description']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= $item['price'] ?></td>
            <td>
                <a href="edit_item.php?id=<?= $item['id'] ?>">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="add_item.php">Add New Item</a>
</div>
<?php include 'partials/footer.php'; ?>
</body>
</html>
