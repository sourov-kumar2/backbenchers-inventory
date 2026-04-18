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
$pageTitle = 'Registry: New Customer';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>
            
            <div class="inventory-view animate-fade-in">
                <!-- Header intentionally omitted as per user request -->

                <div class="form-container">
                    <div class="card form-card glass">
                        <form method="POST" action="" class="intelligence-form">
                            
                            <div class="form-section">
                                <h3 class="section-title">Client Identity</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required autofocus>
                                    </div>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Communication Channels</h3>
                                <div class="form-row">
                                    <div class="form-group flex-1">
                                        <label class="form-label">Contact Number</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                                        </div>
                                    </div>
                                    <div class="form-group flex-1">
                                        <label class="form-label">Email System</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                            <input type="email" name="email" class="form-control" placeholder="client@example.com">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Physical Logistics</h3>
                                <div class="form-group">
                                    <label class="form-label">Service Address</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Street, City, Postal Code..."></textarea>
                                </div>
                            </div>

                            <div class="form-footer">
                                <div class="disclaimer-note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    Initialize this profile to enable streamlined sales tracking and personalized invoicing.
                                </div>
                                <div class="action-buttons">
                                    <a href="customers.php" class="btn btn-outline">Discard</a>
                                    <button type="submit" class="btn btn-primary">Initialize Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <style>
    .inventory-view { padding-bottom: 4rem; padding-top: 2rem; }
    .form-container { max-width: 650px; margin: 0 auto; }
    .form-card { padding: 3rem; border-radius: 28px; border: 1px solid var(--border-color); }
    
    .form-section { margin-bottom: 2rem; }
    .section-title { font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 1.5rem; }

    .form-group { margin-bottom: 1.5rem; }
    .form-row { display: flex; gap: 1.5rem; }
    .flex-1 { flex: 1; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; }
    
    .input-icon-wrapper { position: relative; }
    .field-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }
    
    .form-control { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1.25rem; color: white; transition: 0.25s; font-size: 0.95rem; }
    .input-icon-wrapper .form-control { padding-left: 3.5rem; }
    .form-control:focus { outline: none; border-color: var(--accent-primary); background: rgba(0, 0, 0, 0.3); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    
    .form-divider { height: 1px; background: linear-gradient(to right, transparent, var(--border-color), transparent); margin: 2rem 0; }

    .form-footer { margin-top: 3rem; display: flex; flex-direction: column; gap: 2rem; }
    .disclaimer-note { display: flex; align-items: center; gap: 0.8rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 14px; font-size: 0.78rem; color: var(--text-dim); border: 1px solid var(--border-color); line-height: 1.4; }
    .action-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
    
    .btn { padding: 1rem 2rem; font-weight: 700; border-radius: 14px; }
    .btn-outline:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    @media (max-width: 600px) {
        .form-card { padding: 1.5rem; }
        .form-row { flex-direction: column; gap: 1.5rem; }
        .action-buttons { flex-direction: column; }
    }
    </style>
</body>
</html>
