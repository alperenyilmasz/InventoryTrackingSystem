<?php
/**
 * Oturum ve rol kontrolleri.
 * role: 1 = yönetici (admin), 2 = normal kullanıcı (user)
 */

declare(strict_types=1);

const ROLE_ADMIN = 1;
const ROLE_USER = 2;

function normalize_role(mixed $role): int
{
    if ($role === ROLE_ADMIN || $role === '1' || $role === 1 || $role === 'admin') {
        return ROLE_ADMIN;
    }
    return ROLE_USER;
}

function role_label(int $role): string
{
    return $role === ROLE_ADMIN ? 'Yönetici' : 'Kullanıcı';
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) ($_SESSION['username'] ?? ''),
        'role' => normalize_role($_SESSION['role'] ?? ROLE_USER),
    ];
}

function require_login(): void
{
    if (current_user() === null) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void
{
    require_login();
    $u = current_user();
    if ($u === null || $u['role'] !== ROLE_ADMIN) {
        flash_set('danger', 'Bu işlem için yönetici yetkisi gerekir.');
        header('Location: dashboard.php');
        exit;
    }
}

function is_admin(): bool
{
    $u = current_user();
    return $u !== null && $u['role'] === ROLE_ADMIN;
}
