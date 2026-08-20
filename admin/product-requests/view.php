<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';

$requestId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$requestId) {
    http_response_code(400);
    exit('Invalid request ID.');
}

$stmt = $pdo->prepare(
    'SELECT
        pr.*,
        ru.username AS full_name,
        ru.email,
        ru.phone
     FROM product_request pr
     INNER JOIN registered_user ru
        ON ru.user_id = pr.user_id
     WHERE pr.request_id = ?
     LIMIT 1'
);

$stmt->execute([$requestId]);

$request = $stmt->fetch();

if (!$request) {
    http_response_code(404);
    exit('Product request not found.');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Product Request</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>

<body>

<div class="admin-layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="admin-content">

        <h1>
            Product Request #<?= (int) $request['request_id'] ?>
        </h1>

        <p>
            <strong>Customer:</strong>
            <?= htmlspecialchars($request['full_name']) ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?= htmlspecialchars($request['email']) ?>
        </p>

        <p>
            <strong>Phone:</strong>
            <?= htmlspecialchars($request['phone'] ?? '-') ?>
        </p>

        <hr>

        <p>
            <strong>Part Name:</strong>
            <?= htmlspecialchars($request['part_name']) ?>
        </p>

        <p>
            <strong>Description:</strong>
            <?= htmlspecialchars($request['part_description'] ?? '-') ?>
        </p>

        <p>
            <strong>Preferred Brand:</strong>
            <?= htmlspecialchars($request['preferred_brand'] ?? '-') ?>
        </p>

        <p>
            <strong>Preferred Country:</strong>
            <?= htmlspecialchars($request['preferred_country'] ?? '-') ?>
        </p>

        <p>
            <strong>Size:</strong>
            <?= htmlspecialchars($request['size'] ?? '-') ?>
        </p>

        <p>
            <strong>Quantity:</strong>
            <?= (int) $request['quantity'] ?>
        </p>

        <p>
            <strong>Budget:</strong>

            Rs.
            <?= htmlspecialchars($request['budget_min'] ?? '-') ?>

            -

            Rs.
            <?= htmlspecialchars($request['budget_max'] ?? '-') ?>
        </p>

        <p>
            <strong>Current Status:</strong>
            <?= htmlspecialchars($request['status']) ?>
        </p>

        <hr>

        <h2>Update Request</h2>

        <form
            method="POST"
            action="update-status.php"
        >

            <input
                type="hidden"
                name="request_id"
                value="<?= (int) $request['request_id'] ?>"
            >

            <label for="status">
                Status
            </label>

            <select
                id="status"
                name="status"
                required
            >

                <?php

                $statuses = [
                    'PENDING',
                    'REVIEWING',
                    'APPROVED',
                    'REJECTED',
                    'FULFILLED'
                ];

                foreach ($statuses as $status):

                ?>

                    <option
                        value="<?= $status ?>"
                        <?= $request['status'] === $status
                            ? 'selected'
                            : '' ?>
                    >
                        <?= $status ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <br><br>

            <label for="admin_notes">
                Admin Notes
            </label>

            <br>

            <textarea
                id="admin_notes"
                name="admin_notes"
                rows="5"
                cols="50"
            ><?= htmlspecialchars($request['admin_notes'] ?? '') ?></textarea>

            <br><br>

            <button type="submit">
                Update Request
            </button>

        </form>

    </main>

</div>

</body>
</html>
