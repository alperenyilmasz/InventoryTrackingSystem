<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
    $stmt->execute([$id]);
    $cnt = (int) $stmt->fetchColumn();
    if ($cnt > 0) {
        flash_set('danger', 'Bu kategoride ürün bulunduğu için silinemez. Önce ürünleri taşıyın veya silin.');
    } else {
        $del = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $del->execute([$id]);
        flash_set('success', 'Kategori silindi.');
    }
    header('Location: categories.php');
    exit;
}

$pageTitle = 'Kategoriler';
$navActive = 'categories';

$stmt = $pdo->query(
    'SELECT c.id, c.name,
            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c
     ORDER BY c.name'
);
$rows = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Kategoriler</h1>
    <a class="btn btn-primary" href="category_form.php">Yeni kategori</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ad</th>
                        <th class="text-end">Ürün sayısı</th>
                        <th class="text-end" style="width: 200px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="3" class="text-muted text-center py-4">Kayıt yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end"><?= (int) $r['product_count'] ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="category_form.php?id=<?= (int) $r['id'] ?>">Düzenle</a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delModal<?= (int) $r['id'] ?>">Sil</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($rows !== []): ?>
            <?php foreach ($rows as $r): ?>
            <div class="modal fade" id="delModal<?= (int) $r['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title h5">Kategori silinsin mi?</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <strong><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></strong> kalıcı olarak silinecek.
                            <?php if ((int) $r['product_count'] > 0): ?>
                                <p class="text-danger mb-0 mt-2">Bu kategoride <?= (int) $r['product_count'] ?> ürün var; silme engellenecek.</p>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                            <?php if ((int) $r['product_count'] === 0): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="delete_id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-danger">Sil</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
