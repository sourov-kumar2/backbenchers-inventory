<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <svg class="navbar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span class="brand-text">Inventory System</span>
        </div>
        <div class="navbar-end">
            <span class="user-greeting">Welcome, <?= $_SESSION['username'] ?? 'Guest' ?>!</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<style>
.navbar {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: #fff;
    padding: 0;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border-bottom: 2px solid #00d4ff;
}

.navbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 30px;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 700;
    font-size: 20px;
    letter-spacing: 0.5px;
}

.navbar-icon {
    width: 28px;
    height: 28px;
    color: #00d4ff;
    filter: drop-shadow(0 0 8px rgba(0, 212, 255, 0.4));
}

.brand-text {
    background: linear-gradient(135deg, #00d4ff, #0099cc);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.navbar-end {
    display: flex;
    align-items: center;
    gap: 25px;
}

.user-greeting {
    font-size: 14px;
    color: #a0a0a0;
    font-weight: 500;
}

.logout-btn {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(255, 107, 107, 0.4);
}

@media (max-width: 768px) {
    .navbar-container {
        padding: 12px 15px;
    }
    
    .navbar-brand {
        font-size: 18px;
    }
    
    .navbar-icon {
        width: 24px;
        height: 24px;
    }
    
    .user-greeting {
        display: none;
    }
}
</style>