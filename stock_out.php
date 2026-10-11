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
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT quantity FROM products WHERE id = ? FOR UPDATE');
            $stmt->execute([$productId]);
            $currentRow = $stmt->fetch();
            if (!$currentRow) {
                $pdo->rollBack();
                $errors[] = 'Ürün bulunamadı.';
            } else {
                $current = (int) $currentRow['quantity'];
                if ($current < $qty) {
                    $pdo->rollBack();
                    $errors[] = 'Yetersiz stok. Mevcut: ' . $current . ', istenen: ' . $qty . '.';
                } else {
                    $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?')->execute([$qty, $productId]);
                    $pdo->prepare(
                        'INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, note) VALUES (?, ?, ?, ?, ?)'
                    )->execute([$productId, $user['id'], 'out', $qty, $note === '' ? null : $note]);
                    $pdo->commit();
                    flash_set('success', 'Stok çıkışı kaydedildi.');
                    header('Location: movements.php');
                    exit;
                }
            }
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

$pageTitle = 'Stok çıkış';
$navActive = 'stock_out';

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Stok çıkış</h1>

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
            <button type="submit" class="btn btn-danger">Çıkışı kaydet</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
