<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$stmt = $pdo->query(
    'SELECT 
        o.order_id, o.order_date, o.total_amount, o.status as order_status,
        u.username,
        p.payment_status, p.payment_method
     FROM customer_order o
     INNER JOIN registered_user u ON o.user_id = u.user_id
     LEFT JOIN payment p ON o.order_id = p.order_id
     ORDER BY o.order_date DESC'
);
$orders = $stmt->fetchAll();

$pageTitle = 'Order Management';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Order Management</h1>
        <p>View and update customer orders.</p>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= (int)$order['order_id'] ?></td>
                        <td style="white-space:nowrap;"><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                        <td><?= e($order['username']) ?></td>
                        <td style="font-weight:600;"><?= formatCurrency((float)$order['total_amount']) ?></td>
                        <td>
                            <?php 
                                $pStatus = $order['payment_status'] ?? 'N/A';
                                $pClass = match($pStatus) {
                                    'COMPLETED' => 'status-success',
                                    'PENDING' => 'status-warning',
                                    'FAILED', 'REFUNDED' => 'status-danger',
                                    default => 'status-default'
                                };
                            ?>
                            <span class="status-badge <?= $pClass ?>"><?= e($pStatus) ?></span>
                        </td>
                        <td>
                            <span class="status-badge <?= statusClass($order['order_status']) ?>">
                                <?= e($order['order_status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="view.php?id=<?= (int)$order['order_id'] ?>" class="btn btn-sm btn-secondary">View / Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($orders)): ?>
                    <tr><td colspan="7" style="text-align:center; padding: 20px;">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
