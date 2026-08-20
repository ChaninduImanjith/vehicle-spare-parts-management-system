<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';

$stmt = $pdo->query(
    'SELECT
        pr.request_id,
        pr.part_name,
        pr.preferred_brand,
        pr.quantity,
        pr.status,
        pr.requested_at,
        ru.username AS full_name,
        ru.email
     FROM product_request pr
     INNER JOIN registered_user ru
        ON ru.user_id = pr.user_id
     ORDER BY pr.requested_at DESC'
);

$requests = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Product Requests</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>

<body>

<div class="admin-layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="admin-content">

        <h1>Product Requests</h1>

        <p>
            Customer requested spare parts.
        </p>

        <?php if (empty($requests)): ?>

            <p>No product requests available.</p>

        <?php else: ?>

            <table class="admin-table">

                <thead>

                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Part</th>
                    <th>Brand</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>

                </thead>

                <tbody>

                <?php foreach ($requests as $request): ?>

                    <tr>

                        <td>
                            <?= (int) $request['request_id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($request['full_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($request['part_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $request['preferred_brand'] ?? '-'
                            ) ?>
                        </td>

                        <td>
                            <?= (int) $request['quantity'] ?>
                        </td>

                        <td>
                            <span class="status">
                                <?= htmlspecialchars($request['status']) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($request['requested_at']) ?>
                        </td>

                        <td>

                            <a
                                class="action-link"
                                href="view.php?id=<?= (int) $request['request_id'] ?>"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </main>

</div>

</body>
</html>
