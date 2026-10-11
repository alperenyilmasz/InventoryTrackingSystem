<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    flash_set('danger', 'Geçersiz kullanıcı.');
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
$stmt->execute([$id]);
$userRow = $stmt->fetch();
if (!$userRow) {
    flash_set('danger', 'Kullanıcı bulunamadı.');
    header('Location: users.php');
    exit;
}

$errors = [];
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = normalize_role($_POST['role'] ?? ROLE_USER);
    if ($role !== ROLE_ADMIN && $role !== ROLE_USER) {
        $errors[] = 'Geçersiz rol.';
    }
    if ($errors === [] && $me && (int) $userRow['id'] === $me['id'] && $role !== ROLE_ADMIN) {
        $errors[] = 'Kendi yönetici yetkinizi kaldıramazsınız.';
    }
    if ($errors === []) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
        if ($me && (int) $userRow['id'] === $me['id']) {
            $_SESSION['role'] = $role;
        }
        flash_set('success', 'Kullanıcı güncellendi.');
        header('Location: users.php');
        exit;
    }
}

$pageTitle = 'Kullanıcı düzenle';
$navActive = 'admin';

require __DIR__ . '/includes/header.php';
?>

<div class="mb-3">
    <a class="text-decoration-none" href="users.php">← Yönetici işlemlerine dön</a>
</div>

<h1 class="h3 mb-3">Kullanıcı: <?= htmlspecialchars($userRow['username'], ENT_QUOTES, 'UTF-8') ?></h1>

<?php if ($errors !== []): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <p class="text-muted small mb-2"><?= htmlspecialchars($userRow['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label" for="role">Rol</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="<?= ROLE_USER ?>"<?= normalize_role($userRow['role']) === ROLE_USER ? ' selected' : '' ?>>Normal kullanıcı (2)</option>
                    <option value="<?= ROLE_ADMIN ?>"<?= normalize_role($userRow['role']) === ROLE_ADMIN ? ' selected' : '' ?>>Yönetici (1)</option>
                </select>
                <div class="form-text">SQL: <code>UPDATE users SET role = 1</code> yönetici, <code>role = 2</code> kullanıcı.</div>
            </div>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
