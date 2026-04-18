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
$pageTitle = 'Registry: Modify Supplier';
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
                                <h3 class="section-title">Supplier Identity</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Vendor / Company Name</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($s['name']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Point of Contact</label>
                                    <div class="input-icon-wrapper">
                                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($s['contact_person']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Supply Line Communication</h3>
                                <div class="form-row">
                                    <div class="form-group flex-1">
                                        <label class="form-label">Official Phone</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($s['phone']) ?>">
                                        </div>
                                    </div>
                                    <div class="form-group flex-1">
                                        <label class="form-label">Support Email</label>
                                        <div class="input-icon-wrapper">
                                            <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($s['email']) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <div class="form-section">
                                <h3 class="section-title">Headquarters Address</h3>
                                <div class="form-group">
                                    <label class="form-label">Office / Billing Location</label>
                                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($s['address']) ?></textarea>
                                </div>
                            </div>

                            <div class="form-footer">
                                <div class="disclaimer-note">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    Modifying supplier details will affect the audit logs of all historical and future procurement transactions associated with this source.
                                </div>
                                <div class="action-buttons">
                                    <a href="delete_supplier.php?id=<?= $s['id'] ?>" class="btn btn-outline btn-red" onclick="return confirm('Archive permanently?')">Archive</a>
                                    <div class="spacer" style="flex:1"></div>
                                    <a href="suppliers.php" class="btn btn-outline">Discard</a>
                                    <button type="submit" class="btn btn-primary">Commit Updates</button>
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
    .action-buttons { display: flex; gap: 1rem; justify-content: flex-end; align-items: center; }
    
    .btn { padding: 1rem 2rem; font-weight: 700; border-radius: 14px; }
    .btn-outline:hover { background: rgba(255, 255, 255, 0.05); color: var(--text-primary); }
    .btn-red:hover { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; border-color: rgba(239, 68, 68, 0.2) !important; }

    @media (max-width: 600px) {
        .form-card { padding: 1.5rem; }
        .form-row { flex-direction: column; gap: 1.5rem; }
        .action-buttons { flex-direction: column; align-items: stretch; }
        .spacer { display: none; }
    }
    </style>
</body>
</html>
