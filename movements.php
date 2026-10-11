<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$productFilter = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$typeFilter = (string) ($_GET['type'] ?? '');
if ($typeFilter !== 'in' && $typeFilter !== 'out') {
    $typeFilter = '';
}
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));

$sql = 'SELECT m.id, m.movement_type, m.quantity, m.note, m.created_at,
               p.name AS product_name, p.id AS product_id,
               u.username
        FROM stock_movements m
        INNER JOIN products p ON p.id = m.product_id
        INNER JOIN users u ON u.id = m.user_id
        WHERE 1=1';
$params = [];

if ($productFilter > 0) {
    $sql .= ' AND m.product_id = ?';
    $params[] = $productFilter;
}
if ($typeFilter !== '') {
    $sql .= ' AND m.movement_type = ?';
    $params[] = $typeFilter;
}
if ($dateFrom !== '') {
    $sql .= ' AND DATE(m.created_at) >= ?';
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $sql .= ' AND DATE(m.created_at) <= ?';
    $params[] = $dateTo;
}

$sql .= ' ORDER BY m.created_at DESC, m.id DESC LIMIT 500';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$products = $pdo->query('SELECT id, name FROM products ORDER BY name')->fetchAll();

$pageTitle = 'Hareket geçmişi';
$navActive = 'movements';

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Hareket geçmişi</h1>

<form class="card card-body shadow-sm mb-3" method="get">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-0" for="product_id">Ürün</label>
            <select class="form-select" id="product_id" name="product_id">
                <option value="0">Tümü</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>"<?= $productFilter === (int) $p['id'] ? ' selected' : '' ?>><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0" for="type">İşlem</label>
            <select class="form-select" id="type" name="type">
                <option value="">Tümü</option>
                <option value="in"<?= $typeFilter === 'in' ? ' selected' : '' ?>>Giriş</option>
                <option value="out"<?= $typeFilter === 'out' ? ' selected' : '' ?>>Çıkış</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0" for="date_from">Başlangıç</label>
            <input class="form-control" type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0" for="date_to">Bitiş</label>
            <input class="form-control" type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrele</button>
        </div>
        <div class="col-md-1">
            <a class="btn btn-outline-secondary w-100" href="movements.php">Sıfırla</a>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tarih</th>
                        <th>Ürün</th>
                        <th>İşlem</th>
                        <th class="text-end">Miktar</th>
                        <th>Kullanıcı</th>
                        <th>Not</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="6" class="text-muted text-center py-4">Kayıt yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="text-nowrap"><?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($r['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if ($r['movement_type'] === 'in'): ?>
                                <span class="badge text-bg-success">Giriş</span>
                            <?php else: ?>
                                <span class="badge text-bg-danger">Çıkış</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= (int) $r['quantity'] ?></td>
                        <td><?= htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small text-muted"><?= htmlspecialchars((string) ($r['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted small">Son 500 kayıt listelenir.</div>
</div>

<?php require __DIR__ . '/includes/footer.php';
