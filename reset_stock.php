<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_reset']) || !is_admin()) {
    header('Location: users.php');
    exit;
}

try {
    $pdo->beginTransaction();
    $pdo->exec('DELETE FROM stock_movements');
    $pdo->exec('DELETE FROM products');
    $pdo->exec('DELETE FROM categories');
    $pdo->commit();
    flash_set('success', 'Tüm stok bilgileri sıfırlandı. Site temiz bir duruma getirildi.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('danger', 'Sıfırlama sırasında bir hata oluştu.');
}

header('Location: users.php');
exit;
