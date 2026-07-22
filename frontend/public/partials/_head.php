<?php
/**
 * Shared <head> + opening <body> tag.
 * Caller must set $pageTitle before requiring this file.
 * Optional: $bodyClass to override the default app-shell body class.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php
        require_once dirname(__DIR__, 3) . '/security/middleware.php';
        startSecureSession();
        echo generateCsrfToken();
    ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Pricing Tool', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234f46e5'%3E%3Crect width='24' height='24' rx='6'/%3E%3Cpath stroke='white' stroke-width='2' stroke-linecap='round' d='M12 6v6m0 0v6m0-6h6m-6 0H6'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="/assets/css/output.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="<?= htmlspecialchars($bodyClass ?? 'bg-slate-50 min-h-screen', ENT_QUOTES, 'UTF-8') ?>">
