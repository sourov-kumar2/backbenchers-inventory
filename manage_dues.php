<?php
require 'auth.php';
require 'config.php';

// Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_due') {
    $customer_id = $_POST['customer_id'];
    $pay_amount = $_POST['amount'];
    $note = $_POST['note'] ?? 'Debt settlement';

    try {
        $pdo->beginTransaction();

        // 1. Record payment in history
        $stmt = $pdo->prepare('INSERT INTO due_payments (customer_id, amount_paid, note) VALUES (?, ?, ?)');
        $stmt->execute([$customer_id, $pay_amount, $note]);

        // 2. Deduct from customer total_due
        $stmt = $pdo->prepare('UPDATE customers SET total_due = total_due - ? WHERE id = ?');
        $stmt->execute([$pay_amount, $customer_id]);

        $pdo->commit();
        header('Location: manage_dues.php?success=payment_recorded');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Payment failed: " . $e->getMessage());
    }
}

// Fetch customers with dues
$stmt = $pdo->query('SELECT * FROM customers WHERE total_due > 0 ORDER BY total_due DESC');
$dues = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Manage Customer Dues';
include 'partials/head.php'; 
?>
<body>
    <div class="app-container">
        <?php include 'partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/navbar.php'; ?>

            <div class="dues-summary-cards animate-fade-in">
                <?php
                $total_global_due = array_sum(array_column($dues, 'total_due'));
                ?>
                <div class="summary-card glass">
                    <div class="s-info">
                        <span class="s-label">Total Outstanding Receivable</span>
                        <h2 class="s-value">৳<?= number_format($total_global_due, 2) ?></h2>
                    </div>
                    <div class="s-icon danger">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                </div>
            </div>

            <div class="table-card glass animate-fade-in" style="animation-delay: 0.1s">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer Profile</th>
                                <th>Contact</th>
                                <th>Total Debt</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dues)): ?>
                                <tr>
                                    <td colspan="4" class="empty-row text-center">
                                        <div class="empty-state">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                            <p>All accounts are clear. No outstanding dues detected.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dues as $d): ?>
                                    <tr>
                                        <td>
                                            <div class="customer-cell">
                                                <span class="c-name"><?= htmlspecialchars($d['name']) ?></span>
                                                <span class="c-id">ID: CU-<?= $d['id'] ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-mini">
                                                <span><?= htmlspecialchars($d['phone']) ?></span>
                                                <span class="email-mini"><?= htmlspecialchars($d['email'] ?: 'No email') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="due-amount">৳<?= number_format($d['total_due'], 2) ?></span>
                                        </td>
                                        <td>
                                            <div class="action-cell">
                                                <button onclick="payDue(<?= $d['id'] ?>, '<?= htmlspecialchars($d['name']) ?>', <?= $d['total_due'] ?>)" class="btn btn-payment">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                                    Record Payment
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include 'partials/footer.php'; ?>
        </div>
    </div>

    <!-- Hidden form for payment submission -->
    <form id="payForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="pay_due">
        <input type="hidden" name="customer_id" id="formCustomerId">
        <input type="hidden" name="amount" id="formAmount">
        <input type="hidden" name="note" id="formNote">
    </form>

    <script>
    function payDue(id, name, balance) {
        Swal.fire({
            title: 'Settling Debt for ' + name,
            html: `
                <div class="swal-payment-info">
                    <p>Current Balance: <strong>৳${balance.toFixed(2)}</strong></p>
                    <input type="number" id="swalAmount" class="swal2-input" placeholder="Amount to pay" step="0.01" max="${balance}">
                    <input type="text" id="swalNote" class="swal2-input" placeholder="Note (Optional)">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirm Payment',
            confirmButtonColor: 'var(--accent-primary)',
            preConfirm: () => {
                const amount = document.getElementById('swalAmount').value;
                if (!amount || amount <= 0) {
                    Swal.showValidationMessage('Please enter a valid amount');
                    return false;
                }
                if (amount > balance) {
                    Swal.showValidationMessage('Amount exceeds outstanding balance');
                    return false;
                }
                return {
                    amount: amount,
                    note: document.getElementById('swalNote').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formCustomerId').value = id;
                document.getElementById('formAmount').value = result.value.amount;
                document.getElementById('formNote').value = result.value.note;
                document.getElementById('payForm').submit();
            }
        });
    }
    </script>

    <style>
    .dues-summary-cards { margin-bottom: 2rem; }
    .summary-card { padding: 2rem; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; max-width: 400px; }
    .s-label { color: var(--text-dim); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
    .s-value { font-size: 1.75rem; font-weight: 800; color: var(--danger); margin-top: 0.5rem; }
    .s-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .s-icon.danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

    .customer-cell { display: flex; flex-direction: column; }
    .c-name { font-weight: 700; color: var(--text-primary); }
    .c-id { font-size: 0.75rem; color: var(--text-dim); }
    
    .contact-mini { display: flex; flex-direction: column; font-size: 0.85rem; }
    .email-mini { font-size: 0.75rem; color: var(--accent-primary); }

    .due-amount { font-weight: 800; color: var(--danger); font-size: 1.1rem; }

    .btn-payment { background: rgba(139, 92, 246, 0.1); color: var(--accent-primary); border: 1px solid rgba(139, 92, 246, 0.2); font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 10px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: 0.2s; }
    .btn-payment:hover { background: var(--accent-primary); color: white; transform: translateY(-1px); }

    .empty-state { padding: 2rem; color: var(--text-dim); display: flex; flex-direction: column; align-items: center; gap: 1rem; }
    
    .swal-payment-info { padding: 1rem 0; }
    .swal-payment-info p { margin-bottom: 1.5rem; color: #4b5563; }
    </style>
</body>
</html>
