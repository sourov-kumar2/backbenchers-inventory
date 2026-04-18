<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: customers.php'); exit(); }

$stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header('Location: customers.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name']    ?? '';
    $phone   = $_POST['phone']   ?? '';
    $email   = $_POST['email']   ?? '';
    $address = $_POST['address'] ?? '';
    
    $stmt  = $pdo->prepare('UPDATE customers SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?');
    if ($stmt->execute([$name, $phone, $email, $address, $id])) {
        header('Location: customers.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Edit Customer';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Edit Customer</h1>
                    <p class="text-muted">Updating profile for <span class="active-item"><?= htmlspecialchars($c['name']) ?></span></p>
                </div>
                <div class="header-actions">
                    <a href="customers.php" class="btn btn-outline">Discard Changes</a>
                </div>
            </header>

            <div class="form-container animate-fade-in" style="animation-delay: 0.1s">
                <div class="card form-card glass">
                    <form method="POST" action="" class="glass-form">
                        <div class="form-grid">
                            <div class="form-group span-2">
                                <label class="form-label">Customer Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($c['name']) ?>" required autofocus>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($c['phone']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($c['email']) ?>">
                            </div>

                            <div class="form-group span-2">
                                <label class="form-label">Address Information</label>
                                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($c['address']) ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="delete_customer.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-red" onclick="return confirm('Archive permanently?')">Archive Profile</a>
                            <div class="spacer" style="flex:1"></div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; }
    .header-main { font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem; }
    .active-item { color: var(--accent-primary); font-weight: 600; }
    .form-container { max-width: 800px; margin-bottom: 3rem; }
    .form-card { padding: 2.5rem; border-radius: 28px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .span-2 { grid-column: span 2; }
    .form-actions { display: flex; gap: 1rem; margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--border-color); }
    .btn-red { color: var(--danger); }
    .btn-red:hover { background: rgba(239, 68, 68, 0.1); border-color: var(--danger); }
    </style>
</body>
</html>
