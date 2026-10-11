<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $pid = (int) $_POST['delete_product_id'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM stock_movements WHERE product_id = ?');
    $stmt->execute([$pid]);
    if ((int) $stmt->fetchColumn() > 0) {
        flash_set('danger', 'Bu ürüne ait stok hareketleri olduğu için silinemez. Hareket geçmişini koruyoruz.');
    } else {
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$pid]);
        flash_set('success', 'Ürün silindi.');
    }
    header('Location: products.php');
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$catFilter = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
$criticalOnly = isset($_GET['critical']) && $_GET['critical'] === '1';

$sql = 'SELECT p.id, p.name, p.quantity, p.unit_price, p.critical_threshold, c.id AS category_id, c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND p.name LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($catFilter > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $catFilter;
}
if ($criticalOnly) {
    $sql .= ' AND p.quantity <= p.critical_threshold';
}
$sql .= ' ORDER BY p.name';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Ürünler';
$navActive = 'products';

require __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Ürünler</h1>
    <?php if (is_admin()): ?>
    <a class="btn btn-primary" href="product_form.php">Yeni ürün</a>
    <?php endif; ?>
</div>

<form class="card card-body shadow-sm mb-3" method="get" action="products.php">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-0" for="q">Ürün adı ara</label>
            <input class="form-control" id="q" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="İsim…">
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-0" for="category_id">Kategori</label>
            <select class="form-select" id="category_id" name="category_id">
                <option value="0">Tümü</option>
                <?php foreach ($cats as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= $catFilter === (int) $c['id'] ? ' selected' : '' ?>><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="critical" value="1" id="critical"<?= $criticalOnly ? ' checked' : '' ?>>
                <label class="form-check-label" for="critical">Yalnız kritik</label>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrele</button>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ürün</th>
                        <th>Kategori</th>
                        <th class="text-end">Stok</th>
                        <th class="text-end">Eşik</th>
                        <th class="text-end">Birim fiyat</th>
                        <?php if (is_admin()): ?>
                        <th class="text-end" style="min-width: 140px;">İşlem</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if ($products === []): ?>
                    <tr><td colspan="<?= is_admin() ? 6 : 5 ?>" class="text-muted text-center py-4">Kayıt bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p):
                        $qty = (int) $p['quantity'];
                        $thr = (int) $p['critical_threshold'];
                        $rowClass = '';
                        if ($qty <= $thr) {
                            $rowClass = $qty === 0 ? 'table-critical-empty' : 'table-critical-low';
                        }
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($p['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end"><?= $qty ?></td>
                        <td class="text-end"><?= $thr ?></td>
                        <td class="text-end"><?= number_format((float) $p['unit_price'], 2, ',', '.') ?> ₺</td>
                        <?php if (is_admin()): ?>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="product_form.php?id=<?= (int) $p['id'] ?>">Düzenle</a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#pdel<?= (int) $p['id'] ?>">Sil</button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (is_admin() && $products !== []): ?>
            <?php foreach ($products as $p): ?>
            <div class="modal fade" id="pdel<?= (int) $p['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title h5">Ürün silinsin mi?</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <strong><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></strong> silinirse ilişkili hareketler varsa işlem yapılamaz.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="delete_product_id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="btn btn-danger">Sil</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
