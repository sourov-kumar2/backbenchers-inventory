<?php
require 'auth.php';
require 'config.php';
$items = $pdo->query('SELECT * FROM products')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List – Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-base:       #0d1117;
            --bg-surface:    #131c27;
            --bg-card:       #182031;
            --bg-card-hover: #1e2c42;
            --border:        rgba(0,188,212,0.12);
            --border-hover:  rgba(0,188,212,0.35);
            --accent:        #00bcd4;
            --accent-dim:    rgba(0,188,212,0.15);
            --accent-glow:   rgba(0,188,212,0.25);
            --danger:        #ff5370;
            --success:       #00e676;
            --warning:       #ffd740;
            --text-primary:  #e8f0fe;
            --text-muted:    #6b85a3;
            --text-dim:      #3d5168;
            --nav-width:     240px;
            --header-h:      64px;
            --radius:        14px;
            --radius-sm:     8px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── HEADER ── */
        header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: var(--header-h);
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
        }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: var(--accent-dim); border: 1px solid var(--border-hover);
            display: grid; place-items: center; color: var(--accent); font-size: 18px;
        }
        .logo-text { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--accent); letter-spacing: -0.3px; }
        .header-right { display: flex; align-items: center; gap: 18px; }
        .welcome { font-size: 14px; color: var(--text-muted); }
        .btn-logout {
            padding: 8px 20px; border-radius: var(--radius-sm);
            background: var(--danger); color: #fff;
            border: none; font-family: 'DM Sans', sans-serif; font-weight: 500; font-size: 14px;
            cursor: pointer; transition: opacity .2s;
        }
        .btn-logout:hover { opacity: .85; }

        /* ── SIDEBAR ── */
        aside {
            position: fixed; top: var(--header-h); left: 0; bottom: 0;
            width: var(--nav-width);
            background: var(--bg-surface);
            border-right: 1px solid var(--border);
            padding: 28px 16px;
            display: flex; flex-direction: column; gap: 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: var(--radius-sm);
            text-decoration: none; color: var(--text-muted);
            font-size: 14px; font-weight: 500;
            transition: background .2s, color .2s;
        }
        .nav-item:hover { background: var(--accent-dim); color: var(--accent); }
        .nav-item.active { background: var(--accent-dim); color: var(--accent); border: 1px solid var(--border-hover); }
        .nav-item svg { flex-shrink: 0; }

        /* ── MAIN CONTENT ── */
        main {
            margin-left: var(--nav-width);
            margin-top: var(--header-h);
            padding: 36px 40px;
            flex: 1;
            animation: fadeUp .4s ease both;
        }
        @keyframes fadeUp { from { opacity:0; transform:translateY(16px);} to { opacity:1; transform:none;} }

        .page-header {
            display: flex; align-items: flex-end; justify-content: space-between;
            margin-bottom: 32px;
        }
        .page-title { font-family: 'Syne', sans-serif; font-size: 32px; font-weight: 800; color: var(--accent); }
        .page-sub { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

        .btn-add {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 22px; border-radius: var(--radius-sm);
            background: var(--accent); color: var(--bg-base);
            border: none; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px;
            text-decoration: none; cursor: pointer;
            transition: box-shadow .2s, transform .15s;
            box-shadow: 0 0 0 0 var(--accent-glow);
        }
        .btn-add:hover { box-shadow: 0 0 18px 4px var(--accent-glow); transform: translateY(-1px); }

        /* ── TABLE CARD ── */
        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table-toolbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
        }
        .table-count { font-size: 13px; color: var(--text-muted); }
        .table-count span { color: var(--accent); font-weight: 600; }

        .search-wrap { position: relative; }
        .search-wrap input {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 8px 14px 8px 36px;
            font-family: 'DM Sans', sans-serif; font-size: 13px;
            color: var(--text-primary);
            outline: none; width: 220px;
            transition: border-color .2s;
        }
        .search-wrap input::placeholder { color: var(--text-dim); }
        .search-wrap input:focus { border-color: var(--accent); }
        .search-icon {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); pointer-events: none; font-size: 15px;
        }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--bg-surface); }
        th {
            padding: 14px 20px;
            text-align: left; font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px;
            color: var(--text-muted); white-space: nowrap;
        }
        th:first-child { border-radius: 0; }
        tbody tr {
            border-top: 1px solid var(--border);
            transition: background .18s;
        }
        tbody tr:hover { background: var(--bg-card-hover); }
        td {
            padding: 16px 20px; font-size: 14px; color: var(--text-primary);
            vertical-align: middle;
        }
        td.id-cell { font-family: monospace; font-size: 12px; color: var(--text-muted); }
        td.name-cell { font-weight: 500; }

        .qty-badge {
            display: inline-block; padding: 3px 10px;
            border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .qty-ok  { background: rgba(0,230,118,.12); color: var(--success); border: 1px solid rgba(0,230,118,.25); }
        .qty-low { background: rgba(255,215,64,.10); color: var(--warning);  border: 1px solid rgba(255,215,64,.25); }
        .qty-out { background: rgba(255,83,112,.12); color: var(--danger);   border: 1px solid rgba(255,83,112,.25); }

        .price-cell { color: var(--accent); font-weight: 600; font-size: 14px; }

        .action-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: var(--radius-sm);
            background: var(--accent-dim); color: var(--accent);
            border: 1px solid var(--border-hover);
            font-size: 13px; font-weight: 500; text-decoration: none;
            transition: background .18s, box-shadow .18s;
        }
        .action-btn:hover { background: rgba(0,188,212,.25); box-shadow: 0 0 10px var(--accent-glow); }

        .empty-state {
            text-align: center; padding: 64px 32px;
            color: var(--text-muted);
        }
        .empty-state .icon { font-size: 40px; margin-bottom: 12px; opacity: .4; }
        .empty-state p { font-size: 15px; }

        /* ── FOOTER ── */
        footer {
            margin-left: var(--nav-width);
            padding: 20px 40px;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13px; color: var(--text-dim);
        }
        .footer-links { display: flex; gap: 20px; }
        .footer-links a { color: var(--text-dim); text-decoration: none; transition: color .2s; }
        .footer-links a:hover { color: var(--accent); }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="dashboard.php" class="logo">
        <div class="logo-icon">⌂</div>
        <span class="logo-text">Inventory System</span>
    </a>
    <div class="header-right">
        <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</span>
        <form method="POST" action="logout.php" style="margin:0">
            <button class="btn-logout" type="submit">Logout</button>
        </form>
    </div>
</header>

<!-- SIDEBAR -->
<aside>
    <a href="dashboard.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
    </a>
    <a href="inventory.php" class="nav-item active">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Inventory
    </a>
    <a href="add_item.php" class="nav-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Add Item
    </a>
</aside>

<!-- MAIN -->
<main>
    <div class="page-header">
        <div>
            <div class="page-title">Inventory</div>
            <div class="page-sub">Manage and track all your stock items.</div>
        </div>
        <a href="add_item.php" class="btn-add">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New Item
        </a>
    </div>

    <div class="table-card">
        <div class="table-toolbar">
            <p class="table-count">Showing <span><?= count($items) ?></span> items</p>
            <div class="search-wrap">
                <span class="search-icon">⌕</span>
                <input type="text" id="searchInput" placeholder="Search items…">
            </div>
        </div>

        <table id="inventoryTable">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="icon">📦</div>
                        <p>No inventory items found. <a href="add_item.php" style="color:var(--accent)">Add one now.</a></p>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($items as $item):
                    $qty = (int)$item['quantity'];
                    $qtyClass = $qty === 0 ? 'qty-out' : ($qty < 10 ? 'qty-low' : 'qty-ok');
                ?>
                <tr>
                    <td class="id-cell">#<?= str_pad($item['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="name-cell"><?= htmlspecialchars($item['item_name']) ?></td>
                    <td style="color:var(--text-muted); max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <?= htmlspecialchars($item['description']) ?>
                    </td>
                    <td>
                        <span class="qty-badge <?= $qtyClass ?>"><?= $qty ?></span>
                    </td>
                    <td class="price-cell">$<?= number_format((float)$item['price'], 2) ?></td>
                    <td>
                        <a href="edit_item.php?id=<?= $item['id'] ?>" class="action-btn">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- FOOTER -->
<footer>
    <span>© 2026 Inventory System. All rights reserved.</span>
    <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Contact</a>
    </div>
</footer>

<script>
    // Live search filter
    document.getElementById('searchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
</body>
</html>