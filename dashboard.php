<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Özet panel';
$navActive = 'dashboard';

$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$stockValue = $pdo->query('SELECT COALESCE(SUM(quantity * unit_price), 0) AS v FROM products')->fetch();
$totalValue = (float) ($stockValue['v'] ?? 0);

$stmt = $pdo->query(
    'SELECT c.id, c.name,
            COUNT(p.id) AS product_count,
            COALESCE(SUM(p.quantity), 0) AS qty_sum
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.name'
);
$categorySummary = $stmt->fetchAll();

$criticalTotal = (int) $pdo->query(
    'SELECT COUNT(*) FROM products WHERE quantity <= critical_threshold'
)->fetchColumn();

$stmt = $pdo->query(
    'SELECT p.id, p.name, p.quantity, p.critical_threshold, p.unit_price, c.name AS category_name
     FROM products p
     INNER JOIN categories c ON c.id = p.category_id
     WHERE p.quantity <= p.critical_threshold
     ORDER BY p.quantity ASC, p.name
     LIMIT 20'
);
$criticalList = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-metric card-metric--products border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Ürün sayısı</div>
                <div class="fs-3 fw-semibold text-primary"><?= $totalProducts ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-metric card-metric--categories border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Kategori sayısı</div>
                <div class="fs-3 fw-semibold text-primary"><?= $totalCategories ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-metric card-metric--value border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Toplam stok değeri</div>
                <div class="fs-3 fw-semibold text-success"><?= number_format($totalValue, 2, ',', '.') ?> ₺</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-metric card-metric--critical border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Kritik ürün</div>
                <div class="fs-3 fw-semibold metric-critical-pulse"><?= $criticalTotal ?></div>
                <a class="small metric-critical-link" href="products.php?critical=1">Listeye git</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm card-dashboard-panel">
            <div class="card-header bg-white fw-semibold">Kategori bazlı özet</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kategori</th>
                                <th class="text-end">Ürün</th>
                                <th class="text-end">Toplam miktar</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($categorySummary === []): ?>
                            <tr><td colspan="3" class="text-muted text-center py-4">Henüz kategori yok.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categorySummary as $r):
                                $qtySum = (int) $r['qty_sum'];
                                if ($qtySum === 0) {
                                    $rowClass = 'stock-row--empty';
                                } elseif ($qtySum < 10) {
                                    $rowClass = 'stock-row--low';
                                } else {
                                    $rowClass = '';
                                }
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end"><?= (int) $r['product_count'] ?></td>
                                <td class="text-end">
                                    <?php if ($qtySum === 0): ?>
                                        <span class="badge stock-badge stock-badge--empty">Stokta yok</span>
                                    <?php elseif ($qtySum < 10): ?>
                                        <span class="badge stock-badge stock-badge--low">Stok azalıyor</span>
                                    <?php else: ?>
                                        <span class="badge stock-badge stock-badge--ok">Stokta var</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm card-dashboard-panel card-critical-list">
            <div class="card-header fw-semibold">Kritik stok listesi</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ürün</th>
                                <th class="text-end">Stok</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($criticalList === []): ?>
                            <tr><td colspan="3" class="text-muted text-center py-3">Kritik ürün yok.</td></tr>
                        <?php else: ?>
                            <?php foreach ($criticalList as $p): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($p['category_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="text-end"><?= (int) $p['quantity'] ?></td>
                                <td class="text-end">
                                    <?php if (is_admin()): ?>
                                    <a class="btn btn-sm btn-outline-primary" href="product_form.php?id=<?= (int) $p['id'] ?>">Düzenle</a>
                                    <?php else: ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="products.php?q=<?= urlencode($p['name']) ?>">Gör</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php';
