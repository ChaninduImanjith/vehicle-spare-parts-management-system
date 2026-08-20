<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../config/database.php';

$productCount = (int) $pdo
    ->query('SELECT COUNT(*) FROM spare_part')
    ->fetchColumn();

$userCount = (int) $pdo
    ->query('SELECT COUNT(*) FROM registered_user')
    ->fetchColumn();

$orderCount = (int) $pdo
    ->query('SELECT COUNT(*) FROM customer_order')
    ->fetchColumn();

$pendingRequestCount = (int) $pdo
    ->query(
        "SELECT COUNT(*)
         FROM product_request
         WHERE status = 'PENDING'"
    )
    ->fetchColumn();

$lowStockCount = (int) $pdo
    ->query(
        "SELECT COUNT(*)
         FROM spare_part
         WHERE stock_qty <= reorder_level
         AND status = 'ACTIVE'"
    )
    ->fetchColumn();

$totalSales = (float) $pdo
    ->query(
        "SELECT COALESCE(SUM(amount), 0)
         FROM payment
         WHERE status = 'PAID'"
    )
    ->fetchColumn();

$recentOrdersStmt = $pdo->query(
    "SELECT
        co.order_id,
        ru.username,
        co.final_amount,
        co.status,
        co.order_date
     FROM customer_order co
     INNER JOIN registered_user ru
        ON ru.user_id = co.user_id
     ORDER BY co.order_date DESC
     LIMIT 5"
);

$recentOrders = $recentOrdersStmt->fetchAll();

$pageTitle = 'Dashboard';

require_once __DIR__ . '/includes/header.php';

?>

<div class="page-header">

    <div>

        <h1>Admin Dashboard</h1>

        <p>
            Welcome,
            <?= e($_SESSION['admin_name'] ?? 'Admin') ?>
        </p>

    </div>

</div>


<section class="dashboard-grid">

    <div class="dashboard-card">

        <span class="card-label">
            Spare Parts
        </span>

        <strong>
            <?= $productCount ?>
        </strong>

    </div>


    <div class="dashboard-card">

        <span class="card-label">
            Registered Customers
        </span>

        <strong>
            <?= $userCount ?>
        </strong>

    </div>


    <div class="dashboard-card">

        <span class="card-label">
            Orders
        </span>

        <strong>
            <?= $orderCount ?>
        </strong>

    </div>


    <div class="dashboard-card">

        <span class="card-label">
            Pending Requests
        </span>

        <strong>
            <?= $pendingRequestCount ?>
        </strong>

    </div>


    <div class="dashboard-card">

        <span class="card-label">
            Low Stock Parts
        </span>

        <strong>
            <?= $lowStockCount ?>
        </strong>

    </div>


    <div class="dashboard-card">

        <span class="card-label">
            Total Sales
        </span>

        <strong>
            <?= formatCurrency($totalSales) ?>
        </strong>

    </div>

</section>


<section class="dashboard-section">

    <div class="section-heading">

        <h2>Recent Orders</h2>

    </div>


    <?php if (empty($recentOrders)): ?>

        <div class="empty-state">

            <p>No orders available yet.</p>

        </div>

    <?php else: ?>

        <div class="table-wrapper">

            <table class="admin-table">

                <thead>

                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>

                </thead>

                <tbody>

                <?php foreach ($recentOrders as $order): ?>

                    <tr>

                        <td>
                            #<?= (int) $order['order_id'] ?>
                        </td>

                        <td>
                            <?= e($order['username']) ?>
                        </td>

                        <td>
                            <?= formatCurrency(
                                (float) $order['final_amount']
                            ) ?>
                        </td>

                        <td>

                            <span
                                class="status-badge
                                <?= e(statusClass($order['status'])) ?>"
                            >

                                <?= e($order['status']) ?>

                            </span>

                        </td>

                        <td>
                            <?= e($order['order_date']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>