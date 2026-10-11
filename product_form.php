<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = [
    'category_id' => 0,
    'name' => '',
    'quantity' => 0,
    'unit_price' => '0',
    'critical_threshold' => 5,
];

if ($id > 0) {
    $stmt = $pdo->prepare(
        'SELECT id, category_id, name, quantity, unit_price, critical_threshold FROM products WHERE id = ?'
    );
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('danger', 'Ürün bulunamadı.');
        header('Location: products.php');
        exit;
    }
    $row = $found;
    $row['unit_price'] = (string) $row['unit_price'];
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
if ($categories === [] && $id === 0) {
    flash_set('warning', 'Önce en az bir kategori ekleyin.');
    header('Location: categories.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $row['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $row['name'] = trim((string) ($_POST['name'] ?? ''));
    $row['quantity'] = (int) ($_POST['quantity'] ?? 0);
    $row['unit_price'] = str_replace(',', '.', trim((string) ($_POST['unit_price'] ?? '0')));
    $row['critical_threshold'] = (int) ($_POST['critical_threshold'] ?? 0);

    if ($row['name'] === '') {
        $errors[] = 'Ürün adı zorunludur.';
    }
    if ($row['category_id'] <= 0) {
        $errors[] = 'Kategori seçin.';
    }
    $price = filter_var($row['unit_price'], FILTER_VALIDATE_FLOAT);
    if ($price === false || $price < 0) {
        $errors[] = 'Geçerli bir birim fiyat girin.';
    }
    if ($row['quantity'] < 0) {
        $errors[] = 'Stok negatif olamaz.';
    }
    if ($row['critical_threshold'] < 0) {
        $errors[] = 'Kritik eşik negatif olamaz.';
    }

    if ($errors === []) {
        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE products SET category_id = ?, name = ?, quantity = ?, unit_price = ?, critical_threshold = ? WHERE id = ?'
            );
            $stmt->execute([
                $row['category_id'],
                $row['name'],
                $row['quantity'],
                $price,
                $row['critical_threshold'],
                $id,
            ]);
            flash_set('success', 'Ürün güncellendi.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO products (category_id, name, quantity, unit_price, critical_threshold)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $row['category_id'],
                $row['name'],
                $row['quantity'],
                $price,
                $row['critical_threshold'],
            ]);
            flash_set('success', 'Ürün eklendi.');
        }
        header('Location: products.php');
        exit;
    }
}

$pageTitle = $id ? 'Ürün düzenle' : 'Yeni ürün';
$navActive = 'products';

require __DIR__ . '/includes/header.php';
?>

<div class="mb-3">
    <a class="text-decoration-none" href="products.php">← Ürünlere dön</a>
</div>

<h1 class="h3 mb-3"><?= $id ? 'Ürün düzenle' : 'Yeni ürün' ?></h1>

<?php if ($errors !== []): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 560px;">
    <div class="card-body">
        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label" for="category_id">Kategori</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="0">Seçin…</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"<?= (int) $row['category_id'] === (int) $c['id'] ? ' selected' : '' ?>>
                        <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="name">Ürün adı</label>
                <input class="form-control" id="name" name="name" required maxlength="191" value="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="quantity">Stok miktarı</label>
                    <input class="form-control" type="number" id="quantity" name="quantity" min="0" required value="<?= (int) $row['quantity'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="unit_price">Birim fiyat (₺)</label>
                    <input class="form-control" id="unit_price" name="unit_price" inputmode="decimal" required value="<?= htmlspecialchars((string) $row['unit_price'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="critical_threshold">Kritik eşik</label>
                    <input class="form-control" type="number" id="critical_threshold" name="critical_threshold" min="0" required value="<?= (int) $row['critical_threshold'] ?>">
                    <div class="form-text">Stok bu değerin altına veya eşit olunca uyarı gösterilir.</div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
