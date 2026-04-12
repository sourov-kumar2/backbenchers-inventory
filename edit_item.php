<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: inventory.php'); exit(); }

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { header('Location: inventory.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['item_name']   ?? '';
    $desc  = $_POST['description'] ?? '';
    $qty   = $_POST['quantity']    ?? 0;
    $price = $_POST['price']       ?? 0.00;
    $stmt  = $pdo->prepare('UPDATE products SET item_name = ?, description = ?, quantity = ?, price = ? WHERE id = ?');
    $stmt->execute([$name, $desc, $qty, $price, $id]);
    header('Location: inventory.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item – Inventory System</title>
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
            --accent-dim:    rgba(0,188,212,0.12);
            --accent-glow:   rgba(0,188,212,0.25);
            --danger:        #ff5370;
            --danger-dim:    rgba(255,83,112,0.12);
            --danger-border: rgba(255,83,112,0.30);
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

        /* ── MAIN ── */
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

        /* item ID pill */
        .item-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 20px;
            background: var(--accent-dim); border: 1px solid var(--border-hover);
            font-size: 12px; color: var(--accent); font-weight: 600; font-family: monospace;
            margin-bottom: 6px;
        }

        .btn-back {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: var(--radius-sm);
            background: transparent; color: var(--text-muted);
            border: 1px solid var(--border); font-size: 14px; font-weight: 500;
            text-decoration: none; transition: border-color .2s, color .2s;
        }
        .btn-back:hover { border-color: var(--border-hover); color: var(--accent); }

        /* ── FORM CARD ── */
        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px 40px;
            max-width: 680px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--text-muted);
        }

        input[type="text"],
        input[type="number"],
        textarea {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text-primary);
            outline: none;
            width: 100%;
            transition: border-color .2s, box-shadow .2s;
        }
        input::placeholder, textarea::placeholder { color: var(--text-dim); }
        input:focus, textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }
        textarea { resize: vertical; min-height: 110px; }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; }

        .input-prefix-wrap { position: relative; }
        .input-prefix {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--accent); font-weight: 600; font-size: 14px; pointer-events: none;
        }
        .input-prefix-wrap input { padding-left: 28px; }

        .form-divider {
            grid-column: 1 / -1;
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        /* ── DANGER ZONE ── */
        .danger-zone {
            grid-column: 1 / -1;
            background: var(--danger-dim);
            border: 1px solid var(--danger-border);
            border-radius: var(--radius-sm);
            padding: 16px 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
        }
        .danger-zone-text { font-size: 13px; color: var(--text-muted); line-height: 1.5; }
        .danger-zone-text strong { display: block; color: var(--danger); font-size: 14px; margin-bottom: 2px; }
        .btn-delete {
            flex-shrink: 0;
            display: flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: var(--radius-sm);
            background: var(--danger-dim); color: var(--danger);
            border: 1px solid var(--danger-border);
            font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif;
            cursor: pointer; text-decoration: none;
            transition: background .2s, box-shadow .2s;
        }
        .btn-delete:hover { background: rgba(255,83,112,.22); box-shadow: 0 0 12px rgba(255,83,112,.2); }

        /* ── FORM ACTIONS ── */
        .form-actions {
            grid-column: 1 / -1;
            display: flex; align-items: center; justify-content: flex-end; gap: 14px;
            margin-top: 6px;
        }
        .btn-cancel {
            padding: 11px 24px; border-radius: var(--radius-sm);
            background: transparent; color: var(--text-muted);
            border: 1px solid var(--border); font-size: 14px; font-weight: 500;
            font-family: 'DM Sans', sans-serif; cursor: pointer;
            text-decoration: none; transition: border-color .2s, color .2s;
        }
        .btn-cancel:hover { border-color: var(--border-hover); color: var(--text-primary); }
        .btn-submit {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 28px; border-radius: var(--radius-sm);
            background: var(--accent); color: var(--bg-base);
            border: none; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px;
            cursor: pointer; transition: box-shadow .2s, transform .15s;
        }
        .btn-submit:hover { box-shadow: 0 0 18px 4px var(--accent-glow); transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        /* ── DELETE MODAL ── */
        .modal-backdrop {
            display: none; position: fixed; inset: 0; z-index: 200;
            background: rgba(0,0,0,.6); backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            background: var(--bg-card);
            border: 1px solid var(--danger-border);
            border-radius: var(--radius);
            padding: 36px 32px; max-width: 420px; width: 90%;
            animation: fadeUp .25s ease both;
            text-align: center;
        }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--danger-dim); border: 1px solid var(--danger-border);
            display: grid; place-items: center; margin: 0 auto 20px;
            color: var(--danger); font-size: 22px;
        }
        .modal h3 { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 10px; }
        .modal p { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 28px; }
        .modal p strong { color: var(--text-primary); }
        .modal-actions { display: flex; gap: 12px; justify-content: center; }
        .modal-cancel {
            padding: 10px 24px; border-radius: var(--radius-sm);
            background: transparent; color: var(--text-muted);
            border: 1px solid var(--border); font-size: 14px;
            font-family: 'DM Sans', sans-serif; cursor: pointer;
            transition: border-color .2s, color .2s;
        }
        .modal-cancel:hover { border-color: var(--border-hover); color: var(--text-primary); }
        .modal-confirm {
            padding: 10px 24px; border-radius: var(--radius-sm);
            background: var(--danger); color: #fff;
            border: none; font-size: 14px; font-weight: 600;
            font-family: 'Syne', sans-serif; cursor: pointer;
            transition: opacity .2s;
        }
        .modal-confirm:hover { opacity: .85; }

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

<!-- DELETE MODAL -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h3>Delete Item?</h3>
        <p>You're about to permanently delete <strong><?= htmlspecialchars($item['item_name']) ?></strong>. This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="modal-cancel" onclick="closeModal()">Cancel</button>
            <a href="delete_item.php?id=<?= $item['id'] ?>" class="modal-confirm">Yes, Delete</a>
        </div>
    </div>
</div>

<!-- MAIN -->
<main>
    <div class="page-header">
        <div>
            <div class="item-pill">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>
                ID #<?= str_pad($item['id'], 4, '0', STR_PAD_LEFT) ?>
            </div>
            <!-- <div class="page-title">Edit Item</div>
            <div class="page-sub">Update the details for this inventory product.</div> -->
        </div>
        <a href="inventory.php" class="btn-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Inventory
        </a>
    </div>

    <div class="form-card">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group full">
                    <label for="item_name">Item Name</label>
                    <input type="text" id="item_name" name="item_name"
                           value="<?= htmlspecialchars($item['item_name']) ?>" required>
                </div>

                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($item['description']) ?></textarea>
                </div>

                <div class="form-divider"></div>

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity"
                           value="<?= $item['quantity'] ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="price">Price (USD)</label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">$</span>
                        <input type="number" id="price" name="price"
                               value="<?= $item['price'] ?>" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-divider"></div>

                <!-- DANGER ZONE -->
                <div class="danger-zone">
                    <div class="danger-zone-text">
                        <strong>Delete this item</strong>
                        Permanently remove this product from your inventory. This cannot be undone.
                    </div>
                    <button type="button" class="btn-delete" onclick="openModal()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                        Delete Item
                    </button>
                </div>

                <div class="form-actions">
                    <a href="inventory.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Save Changes
                    </button>
                </div>

            </div>
        </form>
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
    function openModal()  { document.getElementById('deleteModal').classList.add('open'); }
    function closeModal() { document.getElementById('deleteModal').classList.remove('open'); }
    // Close on backdrop click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
</body>
</html>