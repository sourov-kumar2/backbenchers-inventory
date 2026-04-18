<?php
session_start();
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
$pageTitle = 'Sign In';
include 'partials/head.php'; 
?>
<body class="login-wrapper">
    <div class="login-glass-container animate-fade-in">
        <div class="login-card glass">
            <div class="card-close-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            
            <div class="login-header text-center">
                <div class="brand-logo">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="var(--accent-primary)" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h1 class="font-syne">Sign in</h1>
                <p class="text-secondary">Welcome back to Backbenchers Inventory</p>
            </div>

            <form method="POST" action="" class="login-form">
                <?php if ($error): ?>
                    <div class="alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Email or Username</label>
                    <input type="text" name="username" class="form-control" placeholder="your@email.com" required autofocus>
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label class="form-label">Password</label>
                        <a href="#" class="forgot-link">Forgot?</a>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Continue
                </button>
            </form>

            <div class="login-footer text-center">
                <p class="text-muted">By continuing, you agree to the <a href="#">Terms of Service</a>.</p>
                <div class="footer-divider"></div>
                <p class="text-secondary">Are you an administrator? <a href="#" class="accent-link">Request access</a></p>
            </div>
        </div>
    </div>

    <style>
    .login-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        width: 100vw;
        background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #020617 100%);
        overflow: hidden;
    }

    .login-glass-container {
        width: 100%;
        max-width: 440px;
        padding: 1.5rem;
    }

    .login-card {
        position: relative;
        padding: 3.5rem 2.5rem 3rem;
        border-radius: 28px;
    }

    .card-close-btn {
        position: absolute;
        top: 1.5rem;
        left: 1.5rem;
        color: var(--text-muted);
        cursor: pointer;
        opacity: 0.6;
        transition: 0.2s;
    }

    .card-close-btn:hover { opacity: 1; }

    .brand-logo {
        margin-bottom: 1.25rem;
    }

    .login-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .login-header p {
        font-size: 0.9rem;
        margin-bottom: 2.5rem;
    }

    .label-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .forgot-link {
        font-size: 0.75rem;
        color: var(--accent-primary);
        text-decoration: none;
    }

    .btn-full {
        width: 100%;
        padding: 0.85rem;
        font-size: 1rem;
        margin-top: 1rem;
        border-radius: 12px;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #fca5a5;
        padding: 0.75rem;
        border-radius: 10px;
        font-size: 0.85rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .login-footer {
        margin-top: 2.5rem;
    }

    .login-footer p {
        font-size: 0.8rem;
    }

    .footer-divider {
        height: 1px;
        background: var(--border-color);
        margin: 1.5rem 0;
    }

    .accent-link {
        color: var(--accent-primary);
        text-decoration: none;
        font-weight: 600;
    }

    @media (max-width: 480px) {
        .login-card {
            padding: 3rem 1.5rem 2rem;
            border-radius: 20px;
        }
    }
    </style>
</body>
</html>