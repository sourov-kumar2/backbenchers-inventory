<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: suppliers.php'); exit(); }

$stmt = $pdo->prepare('SELECT * FROM suppliers WHERE id = ?');
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { header('Location: suppliers.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name']            ?? '';
    $contact = $_POST['contact_person']  ?? '';
    $phone   = $_POST['phone']           ?? '';
    $email   = $_POST['email']           ?? '';
    $address = $_POST['address']         ?? '';
    
    $stmt  = $pdo->prepare('UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, email = ?, address = ? WHERE id = ?');
    if ($stmt->execute([$name, $contact, $phone, $email, $address, $id])) {
        header('Location: suppliers.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Edit Supplier';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Modify Vendor</h1>
                    <p class="text-muted">Updating information for <span class="active-item"><?= htmlspecialchars($s['name']) ?></span></p>
                </div>
                <div class="header-actions">
                    <a href="suppliers.php" class="btn btn-outline">Discard Changes</a>
                </div>
            </header>

            <div class="form-container animate-fade-in" style="animation-delay: 0.1s">
                <div class="card form-card glass">
                    <form method="POST" action="" class="glass-form">
                        <div class="form-grid">
                            <div class="form-group span-2">
                                <label class="form-label">Vendor / Company Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($s['name']) ?>" required autofocus>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($s['contact_person']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Official Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($s['email']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($s['phone']) ?>">
                            </div>

                            <div class="form-group span-2">
                                <label class="form-label">Office Address</label>
                                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($s['address']) ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="delete_supplier.php?id=<?= $s['id'] ?>" class="btn btn-outline btn-red" onclick="return confirm('Remove permanently?')">Archive Supplier</a>
                            <div class="spacer" style="flex:1"></div>
                            <button type="submit" class="btn btn-primary">Update Details</button>
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
