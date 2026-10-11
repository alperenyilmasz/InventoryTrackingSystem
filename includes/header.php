<?php
declare(strict_types=1);
/** @var string $pageTitle */
/** @var string|null $navActive */
$navActive = $navActive ?? '';
$user = current_user();
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
        <a class="navbar-brand navbar-brand-app d-flex align-items-center py-2" href="dashboard.php">
            <img src="assets/img/logo.png" alt="Stockin" class="brand-logo" width="88" height="48">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Menü">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <?php if ($user): ?>
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'dashboard' ? ' active' : '' ?>" href="dashboard.php">Özet</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'products' ? ' active' : '' ?>" href="products.php">Ürünler</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'stock_in' ? ' active' : '' ?>" href="stock_in.php">Stok Giriş</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'stock_out' ? ' active' : '' ?>" href="stock_out.php">Stok Çıkış</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'movements' ? ' active' : '' ?>" href="movements.php">Hareketler</a>
                </li>
                <?php if (is_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'categories' ? ' active' : '' ?>" href="categories.php">Kategoriler</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $navActive === 'admin' ? ' active' : '' ?>" href="users.php">Yönetici işlemleri</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= role_label($user['role']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Çıkış</a></li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container pb-5 app-main">
<?php
$flash = flash_take();
if ($flash):
?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
