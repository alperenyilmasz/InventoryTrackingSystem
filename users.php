<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$pageTitle = 'Yönetici işlemleri';
$navActive = 'admin';

$stmt = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

$movementCount = (int) $pdo->query('SELECT COUNT(*) FROM stock_movements')->fetchColumn();
$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-4">Yönetici işlemleri</h1>

<h2 class="h5 mb-3">Kullanıcı yönetimi</h2>
<div class="card shadow-sm mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kullanıcı adı</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th>Kayıt</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= role_label(normalize_role($u['role'])) ?> (<?= normalize_role($u['role']) ?>)</td>
                        <td class="small text-muted"><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="user_form.php?id=<?= (int) $u['id'] ?>">Düzenle</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<h2 class="h5 mb-3 text-danger">Stok sıfırlama</h2>
<div class="card shadow-sm border-danger">
    <div class="card-body">
        <p class="text-muted small mb-3">
            Tüm kategoriler, ürünler ve stok hareket geçmişi kalıcı olarak silinir.
            Kullanıcı hesapları etkilenmez.
        </p>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#resetStockModal">
            Tüm stok bilgilerini sıfırla
        </button>
    </div>
</div>

<div class="modal fade" id="resetStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h2 class="modal-title h5 text-danger">Emin misiniz?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Bu işlem geri alınamaz. Aşağıdaki veriler kalıcı olarak silinecek:</p>
                <ul class="mb-0">
                    <li><?= $categoryCount ?> kategori</li>
                    <li><?= $productCount ?> ürün</li>
                    <li><?= $movementCount ?> stok hareketi</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hayır, vazgeç</button>
                <form method="post" action="reset_stock.php" class="d-inline">
                    <input type="hidden" name="confirm_reset" value="1">
                    <button type="submit" class="btn btn-danger">Evet, sıfırla</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
