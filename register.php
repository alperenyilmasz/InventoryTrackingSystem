<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');

    if ($username === '' || strlen($username) < 3) {
        $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta girin.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Şifre en az 6 karakter olmalıdır.';
    }
    if ($password !== $password2) {
        $errors[] = 'Şifreler eşleşmiyor.';
    }

    if ($errors === []) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$username, $email, $hash, ROLE_USER]);
            flash_set('success', 'Kayıt tamamlandı. Giriş yapabilirsiniz.');
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            if ((int) $e->errorInfo[1] === 1062) {
                $errors[] = 'Bu kullanıcı adı veya e-posta zaten kayıtlı.';
            } else {
                $errors[] = 'Kayıt sırasında bir hata oluştu.';
            }
        }
    }
}

$pageTitle = 'Kayıt';
$navActive = 'register';
require __DIR__ . '/includes/header_public.php';

if ($errors !== []): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3 text-center">Yeni hesap</h1>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="username">Kullanıcı adı</label>
                        <input class="form-control" id="username" name="username" required minlength="3" maxlength="64"
                               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">E-posta</label>
                        <input class="form-control" type="email" id="email" name="email" required maxlength="191"
                               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Şifre</label>
                        <input class="form-control" type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password2">Şifre (tekrar)</label>
                        <input class="form-control" type="password" id="password2" name="password2" required minlength="6" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Kayıt ol</button>
                </form>
                <div class="auth-switch">
                    <span class="auth-switch-label">Zaten bir hesabınız var mı?</span>
                    <a class="auth-switch-link" href="login.php">Zaten hesabım var</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer_public.php';
