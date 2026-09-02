<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirect('/admin/orders/index.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    $allowedStatuses = ['PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'];
    
    if (!in_array($newStatus, $allowedStatuses, true)) {
        $error = 'Invalid status selected.';
    } else {
        $upd = $pdo->prepare('UPDATE customer_order SET status = ? WHERE order_id = ?');
        $upd->execute([$newStatus, $orderId]);
        $success = 'Order status updated to ' . e($newStatus) . '.';
    }
}

// Fetch Order
$stmt = $pdo->prepare(
    "SELECT 
        o.*,
        u.username, u.email, u.phone as user_phone,
        pg.gateway_name AS payment_method, p.status AS payment_status, p.paid_at AS payment_date
     FROM customer_order o
     INNER JOIN registered_user u ON o.user_id = u.user_id
     LEFT JOIN payment p ON o.order_id = p.order_id
     LEFT JOIN payment_gateway pg ON p.gateway_id = pg.gateway_id
     WHERE o.order_id = ?"
);
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    die("Order not found.");
}

// Fetch Items
$itemStmt = $pdo->prepare(
    "SELECT oi.quantity, oi.unit_price, sp.part_name, sp.part_number 
     FROM order_item oi
     INNER JOIN spare_part sp ON oi.part_id = sp.part_id
     WHERE oi.order_id = ?"
);
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();

$pageTitle = 'Order #' . $orderId;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Order #<?= $orderId ?></h1>
        <p>Placed by <?= e($order['username']) ?> on <?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-secondary">Back to Orders</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px; margin-bottom:24px;">
    
    <!-- Left Column: Items & Details -->
    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Order Items</span>
            </div>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th>Part No.</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($items as $item): 
                            $lineTotal = $item['quantity'] * $item['unit_price'];
                            $subtotal += $lineTotal;
                        ?>
                            <tr>
                                <td><?= e($item['part_name']) ?></td>
                                <td><?= e($item['part_number']) ?></td>
                                <td><?= formatCurrency((float)$item['unit_price']) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td style="font-weight:600;"><?= formatCurrency($lineTotal) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Shipping Address</span>
            </div>
            <div class="panel-body">
                <p><?= nl2br(e($order['shipping_address'])) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Status & Financials -->
    <div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Update Status</span>
            </div>
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-control" style="margin-bottom:12px;">
                            <?php foreach (['PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'] as $st): ?>
                                <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Save Status</button>
                </form>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Financial Summary</span>
            </div>
            <div class="panel-body">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:14px;">
                    <span style="color:var(--text-muted)">Subtotal</span>
                    <span><?= formatCurrency($subtotal) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:14px;">
                    <span style="color:var(--text-muted)">Shipping</span>
                    <span><?= formatCurrency((float)$order['total_amount'] - $subtotal) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:16px; padding-top:16px; border-top:1px solid var(--border); font-size:18px; font-weight:700;">
                    <span>Total</span>
                    <span><?= formatCurrency((float)$order['total_amount']) ?></span>
                </div>
                
                <div style="margin-top:24px; padding-top:24px; border-top:1px solid var(--border);">
                    <div style="margin-bottom:8px;">
                        <span style="color:var(--text-muted); font-size:13px; display:block;">Payment Status</span>
                        <?php 
                            $pStatus = $order['payment_status'] ?? 'UNKNOWN';
                            $pClass = match($pStatus) {
                                'PAID' => 'status-success',
                                'PENDING' => 'status-warning',
                                'FAILED', 'REFUNDED', 'CANCELLED' => 'status-danger',
                                default => 'status-default'
                            };
                        ?>
                        <span class="status-badge <?= $pClass ?>"><?= e($pStatus) ?></span>
                    </div>
                    <div>
                        <span style="color:var(--text-muted); font-size:13px; display:block;">Payment Method</span>
                        <strong><?= e($order['payment_method'] ?? 'N/A') ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Customer Contact</span>
            </div>
            <div class="panel-body">
                <p style="margin-bottom:4px;"><strong><?= e($order['username']) ?></strong></p>
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:4px;">Email: <?= e($order['email']) ?></p>
                <p style="color:var(--text-muted); font-size:14px;">Phone: <?= e($order['user_phone'] ?? 'N/A') ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
