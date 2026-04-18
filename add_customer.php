<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name']    ?? '';
    $phone   = $_POST['phone']   ?? '';
    $email   = $_POST['email']   ?? '';
    $address = $_POST['address'] ?? '';
    
    $stmt  = $pdo->prepare('INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)');
    if ($stmt->execute([$name, $phone, $email, $address])) {
        header('Location: customers.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Register Customer';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <header class="page-header animate-fade-in">
                <div class="header-titles">
                    <h1 class="header-main">Register Customer</h1>
                    <p class="text-muted">Add a new client to the system for faster POS checkouts.</p>
                </div>
                <div class="header-actions">
                    <a href="customers.php" class="btn btn-outline">Back to List</a>
                </div>
            </header>

            <div class="form-container animate-fade-in" style="animation-delay: 0.1s">
                <div class="card form-card glass">
                    <form method="POST" action="" class="glass-form">
                        <div class="form-grid">
                            <div class="form-group span-2">
                                <label class="form-label">Customer Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required autofocus>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="client@example.com">
                            </div>

                            <div class="form-group span-2">
                                <label class="form-label">Shipping / Billing Address</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Street, City, Postcode..."></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Create Profile</button>
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
