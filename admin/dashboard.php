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

$requestCount = (int) $pdo
    ->query(
        "SELECT COUNT(*)
         FROM product_request
         WHERE status = 'PENDING'"
    )
    ->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<main class="dashboard-container">

    <header class="dashboard-header">

        <div>

            <h1>Admin Dashboard</h1>

            <p>
                Welcome
                <?= htmlspecialchars(
                    $_SESSION['admin_name'] ?? 'Admin'
                ) ?>
            </p>

        </div>

        <a href="logout.php">
            Logout
        </a>

    </header>

    <p>
    <a href="/admin/product-requests/index.php">
        Manage Product Requests
    </a>
</p>

    <section class="dashboard-grid">

        <div class="dashboard-card">

            <h2><?= $productCount ?></h2>
            <p>Spare Parts</p>

        </div>

        <div class="dashboard-card">

            <h2><?= $userCount ?></h2>
            <p>Registered Customers</p>

        </div>

        <div class="dashboard-card">

            <h2><?= $orderCount ?></h2>
            <p>Orders</p>

        </div>

        <div class="dashboard-card">

            <h2><?= $requestCount ?></h2>
            <p>Pending Requests</p>

        </div>

    </section>

</main>

</body>
</html>
