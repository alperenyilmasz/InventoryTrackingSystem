<?php
/**
 * Oturum + PDO + yardımcı dosyalar.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

$pdo = null;
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Veritabanı bağlantısı kurulamadı. includes/config.php içindeki ayarları kontrol edin.';
    exit;
}

require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/auth.php';
