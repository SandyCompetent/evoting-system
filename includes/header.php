<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>e-Voting System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo htmlspecialchars(($appRoot === '/' || $appRoot === '\\' || $appRoot === '') ? '/assets/site-icon.svg' : $appRoot . '/assets/site-icon.svg', ENT_QUOTES, 'UTF-8'); ?>">
    <!-- Bootswatch Zephyr Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/zephyr/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php
    $appRoot = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if (preg_match('#/(admin|organiser|voter)$#', $appRoot)) {
        $appRoot = dirname($appRoot);
    }
    $cssPath = ($appRoot === '/' || $appRoot === '\\' || $appRoot === '') ? '/assets/css/style.css' : $appRoot . '/assets/css/style.css';
    ?>
    <!-- Application Custom & Bridge CSS -->
    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath, ENT_QUOTES, 'UTF-8') ?>">
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <?php ob_start(); ?>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
