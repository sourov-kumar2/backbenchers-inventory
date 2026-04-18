<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name']            ?? '';
    $contact = $_POST['contact_person']  ?? '';
    $phone   = $_POST['phone']           ?? '';
    $email   = $_POST['email']           ?? '';
    $address = $_POST['address']         ?? '';
    
    $stmt  = $pdo->prepare('INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)');
    if ($stmt->execute([$name, $contact, $phone, $email, $address])) {
        header('Location: suppliers.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Add Supplier';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Register Supplier</h1>
                    <p class="text-muted">Enter vendor details to streamline your procurement process.</p>
                </div>
                <div class="header-actions">
                    <a href="suppliers.php" class="btn btn-outline">Discard Entry</a>
                </div>
            </header>

            <div class="form-container animate-fade-in" style="animation-delay: 0.1s">
                <div class="card form-card glass">
                    <form method="POST" action="" class="glass-form">
                        <div class="form-grid">
                            <div class="form-group span-2">
                                <label class="form-label">Vendor / Company Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Global Tech Solutions" required autofocus>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" placeholder="Point of contact name">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Official Email</label>
                                <input type="email" name="email" class="form-control" placeholder="vendor@example.com">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                            </div>

                            <div class="form-group span-2">
                                <label class="form-label">Office Address</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Full physical address..."></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Supplier</button>
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
    .form-container { max-width: 800px; margin-bottom: 3rem; }
    .form-card { padding: 2.5rem; border-radius: 28px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .span-2 { grid-column: span 2; }
    .form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--border-color); }
    </style>
</body>
</html>
