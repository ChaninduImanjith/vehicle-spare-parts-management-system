<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Status filter
$filterStatus = $_GET['status'] ?? '';
$allowedStatuses = ['', 'PENDING', 'REVIEWING', 'APPROVED', 'REJECTED', 'FULFILLED'];
if (!in_array($filterStatus, $allowedStatuses, true)) {
    $filterStatus = '';
}

if ($filterStatus !== '') {
    $stmt = $pdo->prepare(
        'SELECT
            pr.request_id,
            pr.part_name,
            pr.preferred_brand,
            pr.quantity,
            pr.status,
            pr.requested_at,
            ru.username,
            ru.email
         FROM product_request pr
         INNER JOIN registered_user ru ON ru.user_id = pr.user_id
         WHERE pr.status = ?
         ORDER BY pr.requested_at DESC'
    );
    $stmt->execute([$filterStatus]);
} else {
    $stmt = $pdo->query(
        'SELECT
            pr.request_id,
            pr.part_name,
            pr.preferred_brand,
            pr.quantity,
            pr.status,
            pr.requested_at,
            ru.username,
            ru.email
         FROM product_request pr
         INNER JOIN registered_user ru ON ru.user_id = pr.user_id
         ORDER BY pr.requested_at DESC'
    );
}

$requests = $stmt->fetchAll();

$pageTitle = 'Product Requests';
require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <div>
        <h1>Product Requests</h1>
        <p>Customer-submitted spare part requests.</p>
    </div>
</div>

<!-- Status Filter -->
<form method="GET" class="filter-bar" style="margin-bottom:20px;">
    <div class="filter-group">
        <label class="filter-label" for="status">Filter by Status</label>
        <select id="status" name="status" class="form-control" style="min-width:160px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach (['PENDING','REVIEWING','APPROVED','REJECTED','FULFILLED'] as $s): ?>
                <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="panel">

    <?php if (empty($requests)): ?>

        <div class="empty-state">
            <p>No product requests found.</p>
        </div>

    <?php else: ?>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Part Name</th>
                        <th>Brand Pref.</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>#<?= (int) $req['request_id'] ?></td>
                            <td>
                                <?= e($req['username']) ?><br>
                                <small style="color:var(--text-muted);"><?= e($req['email']) ?></small>
                            </td>
                            <td><?= e($req['part_name']) ?></td>
                            <td><?= e($req['preferred_brand'] ?? '—') ?></td>
                            <td><?= (int) $req['quantity'] ?></td>
                            <td>
                                <span class="status-badge <?= e(statusClass($req['status'])) ?>">
                                    <?= e($req['status']) ?>
                                </span>
                            </td>
                            <td style="white-space:nowrap;"><?= e(date('Y-m-d', strtotime($req['requested_at']))) ?></td>
                            <td>
                                <a href="view.php?id=<?= (int) $req['request_id'] ?>" class="btn btn-sm btn-secondary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
