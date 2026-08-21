<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$requestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$requestId) {
    http_response_code(400);
    exit('Invalid request ID.');
}

$stmt = $pdo->prepare(
    'SELECT
        pr.*,
        ru.username,
        ru.email,
        ru.phone
     FROM product_request pr
     INNER JOIN registered_user ru ON ru.user_id = pr.user_id
     WHERE pr.request_id = ?
     LIMIT 1'
);
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    http_response_code(404);
    exit('Product request not found.');
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus   = $_POST['status'] ?? '';
    $adminNotes  = trim($_POST['admin_notes'] ?? '');
    $allowedStatuses = ['PENDING', 'REVIEWING', 'APPROVED', 'REJECTED', 'FULFILLED'];

    if (!in_array($newStatus, $allowedStatuses, true)) {
        $error = 'Invalid status selected.';
    } else {
        $upd = $pdo->prepare(
            'UPDATE product_request
             SET
                status = ?,
                admin_id = ?,
                admin_notes = ?,
                assigned_at = CASE WHEN assigned_at IS NULL THEN NOW() ELSE assigned_at END
             WHERE request_id = ?'
        );
        $upd->execute([$newStatus, adminId(), $adminNotes, $requestId]);
        $request['status']      = $newStatus;
        $request['admin_notes'] = $adminNotes;
        $success = 'Request updated successfully.';
    }
}

$pageTitle = 'Product Request #' . $requestId;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Product Request #<?= (int) $requestId ?></h1>
        <p>
            <span class="status-badge <?= e(statusClass($request['status'])) ?>">
                <?= e($request['status']) ?>
            </span>
        </p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-secondary">← Back to Requests</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">

    <!-- Customer Info -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Customer Information</span>
        </div>
        <div class="panel-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Username</div>
                    <div class="detail-value"><?= e($request['username']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?= e($request['email']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><?= e($request['phone'] ?? '—') ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Requested At</div>
                    <div class="detail-value"><?= e($request['requested_at']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Part Details -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Requested Part Details</span>
        </div>
        <div class="panel-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Part Name</div>
                    <div class="detail-value"><?= e($request['part_name']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Quantity</div>
                    <div class="detail-value"><?= (int) $request['quantity'] ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Preferred Brand</div>
                    <div class="detail-value"><?= e($request['preferred_brand'] ?? '—') ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Preferred Country</div>
                    <div class="detail-value"><?= e($request['preferred_country'] ?? '—') ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Size</div>
                    <div class="detail-value"><?= e($request['size'] ?? '—') ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Budget Range</div>
                    <div class="detail-value">
                        <?php
                        $bMin = $request['budget_min'];
                        $bMax = $request['budget_max'];
                        if ($bMin !== null && $bMax !== null) {
                            echo formatCurrency((float)$bMin) . ' – ' . formatCurrency((float)$bMax);
                        } elseif ($bMin !== null) {
                            echo 'From ' . formatCurrency((float)$bMin);
                        } elseif ($bMax !== null) {
                            echo 'Up to ' . formatCurrency((float)$bMax);
                        } else {
                            echo '—';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php if ($request['part_description']): ?>
                <div style="margin-top:16px;">
                    <div class="detail-label">Description</div>
                    <div class="detail-value" style="margin-top:4px;"><?= e($request['part_description']) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Update Form -->
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Update Request</span>
    </div>
    <div class="panel-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <?php foreach (['PENDING','REVIEWING','APPROVED','REJECTED','FULFILLED'] as $s): ?>
                            <option value="<?= $s ?>" <?= $request['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned At</label>
                    <div class="form-control" style="background:var(--surface-2); color:var(--text-muted);">
                        <?= e($request['assigned_at'] ?? 'Not yet assigned') ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="admin_notes">Admin Notes</label>
                <textarea id="admin_notes" name="admin_notes" class="form-control" rows="4"><?= e($request['admin_notes'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Request</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
