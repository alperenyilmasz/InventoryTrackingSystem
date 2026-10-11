<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $error = 'Kullanıcı adı ve şifre zorunludur.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = (int) $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = normalize_role($row['role']);
            flash_set('success', 'Hoş geldiniz.');
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Kullanıcı adı veya şifre hatalı.';
    }
}

$pageTitle = 'Giriş';
$navActive = 'login';
require __DIR__ . '/includes/header_public.php';

if ($error !== ''): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3 text-center">Giriş</h1>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="username">Kullanıcı adı</label>
                        <input class="form-control" id="username" name="username" required maxlength="64" autocomplete="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Şifre</label>
                        <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Giriş yap</button>
                </form>
                <div class="auth-switch">
                    <span class="auth-switch-label">Hesabınız yok mu?</span>
                    <a class="auth-switch-link" href="register.php">Hesap oluştur</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer_public.php';
