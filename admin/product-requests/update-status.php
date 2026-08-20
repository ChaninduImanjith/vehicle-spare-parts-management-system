<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$requestId = filter_input(
    INPUT_POST,
    'request_id',
    FILTER_VALIDATE_INT
);

$status = $_POST['status'] ?? '';

$adminNotes = trim(
    $_POST['admin_notes'] ?? ''
);

$allowedStatuses = [
    'PENDING',
    'REVIEWING',
    'APPROVED',
    'REJECTED',
    'FULFILLED'
];

if (
    !$requestId ||
    !in_array($status, $allowedStatuses, true)
) {
    http_response_code(400);
    exit('Invalid request data.');
}

$stmt = $pdo->prepare(
    'UPDATE product_request
     SET
        status = ?,
        admin_id = ?,
        admin_notes = ?,
        assigned_at =
            CASE
                WHEN assigned_at IS NULL
                THEN NOW()
                ELSE assigned_at
            END
     WHERE request_id = ?'
);

$stmt->execute([
    $status,
    adminId(),
    $adminNotes,
    $requestId
]);

header(
    'Location: view.php?id=' . $requestId
);

exit;
