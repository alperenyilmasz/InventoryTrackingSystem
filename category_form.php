<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = ['name' => ''];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('danger', 'Kategori bulunamadı.');
        header('Location: categories.php');
        exit;
    }
    $row = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    if ($name === '') {
        $errors[] = 'Kategori adı zorunludur.';
    } elseif (mb_strlen($name) > 128) {
        $errors[] = 'Ad en fazla 128 karakter olabilir.';
    }

    if ($errors === []) {
        if ($id > 0) {
            $check = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id <> ?');
            $check->execute([$name, $id]);
        } else {
            $check = $pdo->prepare('SELECT id FROM categories WHERE name = ?');
            $check->execute([$name]);
        }
        if ($check->fetch()) {
            $errors[] = 'Bu isimde bir kategori zaten var.';
        }
    }

    if ($errors === []) {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
            $stmt->execute([$name, $id]);
            flash_set('success', 'Kategori güncellendi.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
            flash_set('success', 'Kategori eklendi.');
        }
        header('Location: categories.php');
        exit;
    }
    $row['name'] = $name;
}

$pageTitle = $id ? 'Kategori düzenle' : 'Yeni kategori';
$navActive = 'categories';

require __DIR__ . '/includes/header.php';
?>

<div class="mb-3">
    <a class="text-decoration-none" href="categories.php">← Kategorilere dön</a>
</div>

<h1 class="h3 mb-3"><?= $id ? 'Kategori düzenle' : 'Yeni kategori' ?></h1>

<?php if ($errors !== []): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 520px;">
    <div class="card-body">
        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label" for="name">Kategori adı</label>
                <input class="form-control" id="name" name="name" required maxlength="128"
                       value="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
