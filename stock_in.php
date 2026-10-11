<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$errors = [];
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $qty = (int) ($_POST['quantity'] ?? 0);
    $note = trim((string) ($_POST['note'] ?? ''));
    if ($note !== '' && mb_strlen($note) > 255) {
        $errors[] = 'Açıklama en fazla 255 karakter olabilir.';
    }
    if ($productId <= 0) {
        $errors[] = 'Ürün seçin.';
    }
    if ($qty <= 0) {
        $errors[] = 'Miktar sıfırdan büyük olmalıdır.';
    }

    if ($errors === []) {
        $chk = $pdo->prepare('SELECT id FROM products WHERE id = ?');
        $chk->execute([$productId]);
        if (!$chk->fetch()) {
            $errors[] = 'Ürün bulunamadı.';
        }
    }

    if ($errors === []) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE products SET quantity = quantity + ? WHERE id = ?')->execute([$qty, $productId]);
            $ins = $pdo->prepare(
                'INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, note) VALUES (?, ?, ?, ?, ?)'
            );
            $ins->execute([$productId, $user['id'], 'in', $qty, $note === '' ? null : $note]);
            $pdo->commit();
            flash_set('success', 'Stok girişi kaydedildi.');
            header('Location: movements.php');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'İşlem sırasında hata oluştu.';
        }
    }
}

$productList = $pdo->query(
    'SELECT p.id, p.name, p.quantity, c.name AS category_name
     FROM products p
     INNER JOIN categories c ON c.id = p.category_id
     ORDER BY c.name, p.name'
)->fetchAll();

$pageTitle = 'Stok giriş';
$navActive = 'stock_in';

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Stok giriş</h1>

<?php if ($errors !== []): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 520px;">
    <div class="card-body">
        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label" for="product_id">Ürün</label>
                <select class="form-select" id="product_id" name="product_id" required>
                    <option value="0">Seçin…</option>
                    <?php foreach ($productList as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"<?= (isset($_POST['product_id']) && (int) $_POST['product_id'] === (int) $p['id']) ? ' selected' : '' ?>>
                        <?= htmlspecialchars($p['category_name'] . ' — ' . $p['name'] . ' (stok: ' . $p['quantity'] . ')', ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="quantity">Miktar</label>
                <input class="form-control" type="number" id="quantity" name="quantity" min="1" required value="<?= htmlspecialchars((string) ($_POST['quantity'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="note">Açıklama (isteğe bağlı)</label>
                <input class="form-control" id="note" name="note" maxlength="255" value="<?= htmlspecialchars((string) ($_POST['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button type="submit" class="btn btn-success">Girişi kaydet</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
