<?php
require 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0f1e 0%, #1a1a2e 50%, #16213e 100%);
            color: #e0e0e0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Navbar Styles */
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

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            position: fixed;
            top: 60px;
            left: 0;
            background: linear-gradient(180deg, #0f0f1e 0%, #1a1a2e 100%);
            height: calc(100vh - 60px);
            padding: 0;
            z-index: 999;
            border-right: 1px solid rgba(0, 212, 255, 0.1);
            overflow-y: auto;
            box-shadow: 8px 0 24px rgba(0, 0, 0, 0.3);
        }

        .sidebar-content {
            padding: 30px 0;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            position: relative;
            margin: 8px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 24px;
            color: #a0a0a0;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(0, 212, 255, 0.05);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover::before {
            left: 0;
        }

        .nav-link:hover {
            color: #00d4ff;
            border-left-color: #00d4ff;
            padding-left: 20px;
        }

        .nav-icon {
            width: 22px;
            height: 22px;
            stroke-width: 2;
            flex-shrink: 0;
        }

        .nav-link:hover .nav-icon {
            filter: drop-shadow(0 0 6px rgba(0, 212, 255, 0.5));
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(0, 212, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 212, 255, 0.6);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            margin-top: 60px;
            margin-bottom: 50px;
            padding: 40px 30px;
            min-height: calc(100vh - 110px);
        }

        .page-header {
            margin-bottom: 40px;
            animation: slideInDown 0.6s ease;
        }

        .page-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #00d4ff, #00a8cc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 15px;
            color: #a0a0a0;
            font-weight: 500;
        }

        /* Dashboard Cards Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .dashboard-card {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.8) 0%, rgba(22, 33, 62, 0.8) 100%);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 12px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
            cursor: pointer;
            animation: slideInUp 0.6s ease;
            animation-fill-mode: both;
        }

        .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
        .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
        .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
        .dashboard-card:nth-child(4) { animation-delay: 0.4s; }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transition: all 0.4s ease;
        }

        .dashboard-card:hover::before {
            top: -25%;
            right: -25%;
        }

        .dashboard-card:hover {
            border-color: rgba(0, 212, 255, 0.5);
            transform: translateY(-8px);
            box-shadow: 0 24px 48px rgba(0, 212, 255, 0.15);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.2) 0%, rgba(0, 168, 204, 0.1) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            color: #00d4ff;
        }

        .card-icon svg {
            width: 28px;
            height: 28px;
            stroke-width: 2;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 8px;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #00d4ff, #00a8cc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
        }

        .card-description {
            font-size: 13px;
            color: #888;
            font-weight: 500;
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #a0a0a0;
            padding: 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(0, 212, 255, 0.1);
            z-index: 998;
            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.3);
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 30px;
            margin-left: 260px;
        }

        .footer-left p {
            margin: 0;
            font-size: 13px;
            font-weight: 500;
        }

        .footer-right {
            display: flex;
            gap: 20px;
        }

        .footer-link {
            color: #a0a0a0;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .footer-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #00d4ff;
            transition: width 0.3s ease;
        }

        .footer-link:hover {
            color: #00d4ff;
        }

        .footer-link:hover::after {
            width: 100%;
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
                padding: 24px 16px;
            }

            .page-title {
                font-size: 28px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                margin-left: 70px;
                flex-direction: column;
                gap: 10px;
            }

            .user-greeting {
                display: none;
            }

            .navbar-container {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                <span class="user-greeting">Welcome, <?= $_SESSION['username'] ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-content">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="inventory.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 2H3v6h6V2z"></path>
                            <path d="M21 2h-6v6h6V2z"></path>
                            <path d="M21 14h-6v6h6v-6z"></path>
                            <path d="M9 14H3v6h6v-6z"></path>
                        </svg>
                        <span>Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_item.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Add Item</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back! Here's your inventory overview.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"></path>
                    </svg>
                </div>
                <h3 class="card-title">Total Items</h3>
                <p class="card-value">1,245</p>
                <p class="card-description">Active inventory items</p>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="card-title">In Stock</h3>
                <p class="card-value">892</p>
                <p class="card-description">Items available</p>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="card-title">Low Stock</h3>
                <p class="card-value">42</p>
                <p class="card-description">Needs reordering</p>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm0-11C6.48 3 2 6.48 2 12s4.48 9 10 9 10-4.48 10-10S17.52 3 12 3zm0 16c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"></path>
                    </svg>
                </div>
                <h3 class="card-title">Transactions</h3>
                <p class="card-value">156</p>
                <p class="card-description">This month</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <p>&copy; <span id="year"></span> Inventory System. All rights reserved.</p>
            </div>
            <div class="footer-right">
                <a href="#" class="footer-link">Privacy Policy</a>
                <a href="#" class="footer-link">Terms of Service</a>
                <a href="#" class="footer-link">Contact</a>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>