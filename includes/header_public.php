<?php
declare(strict_types=1);
/** @var string $pageTitle */
/** @var string|null $navActive */
$navActive = $navActive ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Stok Takip</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="app-page">
<nav class="navbar navbar-expand-lg navbar-dark navbar-app shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand navbar-brand-app d-flex align-items-center py-2" href="index.php">
            <img src="assets/img/logo.png" alt="Stockin" class="brand-logo" width="88" height="48">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Menü">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'login' ? ' active' : '' ?>" href="login.php">Giriş</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'register' ? ' active' : '' ?>" href="register.php">Kayıt</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-5 app-main">
