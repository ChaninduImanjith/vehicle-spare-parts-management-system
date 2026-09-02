<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireUser();
$userId = (int)$_SESSION['user_id'];

// Fetch orders
$stmt = $pdo->prepare(
    "SELECT 
        o.order_id, o.order_date, o.total_amount, o.status,
        p.status AS payment_status
     FROM customer_order o
     LEFT JOIN payment p ON o.order_id = p.order_id
     WHERE o.user_id = ?
     ORDER BY o.order_date DESC"
);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 32px; margin-bottom: 8px;">My Orders</h1>
            <p style="color: var(--text-muted);">View your order history and track status.</p>
        </div>
        <a href="/profile.php" class="btn btn-secondary">Back to Profile</a>
    </div>

    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border); overflow: hidden;">
        <?php if (empty($orders)): ?>
            <div style="padding: 60px 20px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
                <h3 style="margin-bottom: 8px;">No orders yet</h3>
                <p style="color: var(--text-muted); margin-bottom: 24px;">You haven't placed any orders with us yet.</p>
                <a href="/shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-surface-alt); border-bottom: 2px solid var(--border);">
                            <th style="padding: 16px; font-weight: 600; color: var(--text-muted);">Order ID</th>
                            <th style="padding: 16px; font-weight: 600; color: var(--text-muted);">Date</th>
                            <th style="padding: 16px; font-weight: 600; color: var(--text-muted);">Total Amount</th>
                            <th style="padding: 16px; font-weight: 600; color: var(--text-muted);">Payment Status</th>
                            <th style="padding: 16px; font-weight: 600; color: var(--text-muted);">Order Status</th>
                            <th style="padding: 16px; font-weight: 600; color: var(--text-muted);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 16px; font-weight: 500;">#<?= (int)$order['order_id'] ?></td>
                                <td style="padding: 16px; color: var(--text-muted);"><?= date('M d, Y H:i', strtotime($order['order_date'])) ?></td>
                                <td style="padding: 16px; font-weight: 600;"><?= formatCurrency((float)$order['total_amount']) ?></td>
                                <td style="padding: 16px;">
                                    <?php 
                                        $pStatus = $order['payment_status'] ?? 'UNKNOWN';
                                        $pClass = match($pStatus) {
                                            'PAID' => 'badge-success',
                                            'PENDING' => 'badge-warning',
                                            'FAILED', 'REFUNDED', 'CANCELLED' => 'badge-danger',
                                            default => ''
                                        };
                                    ?>
                                    <span class="badge <?= $pClass ?>"><?= e($pStatus) ?></span>
                                </td>
                                <td style="padding: 16px;">
                                    <span class="badge <?= statusClass($order['status']) ?>"><?= e($order['status']) ?></span>
                                </td>
                                <td style="padding: 16px;">
                                    <a href="/order-details.php?id=<?= (int)$order['order_id'] ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 13px;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
