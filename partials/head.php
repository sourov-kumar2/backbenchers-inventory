<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Inventory System' : 'Inventory System'; ?></title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            background: rgba(30, 41, 59, 0.95) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: var(--radius-lg) !important;
            color: var(--text-primary) !important;
        }
        .swal2-title, .swal2-html-container {
            color: var(--text-primary) !important;
        }
        .swal2-confirm {
            background-color: var(--accent-primary) !important;
            border-radius: var(--radius-md) !important;
        }
    </style>
    <!-- Favicon or SVG Icon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2300f2ff' stroke-width='2'><path d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'></path><polyline points='9 22 9 12 15 12 15 22'></polyline></svg>">
    <!-- Google Fonts are already imported in style.css -->
    <!-- Main CSS -->
    <?php $base_url = $base_url ?? ''; ?>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
