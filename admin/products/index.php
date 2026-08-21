<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$stmt = $pdo->query(
    'SELECT 
        sp.part_id, sp.part_number, sp.part_name, sp.price, sp.status,
        c.category_name, b.brand_name
     FROM spare_part sp
     INNER JOIN category c ON sp.category_id = c.category_id
     INNER JOIN brand b ON sp.brand_id = b.brand_id
     ORDER BY sp.part_id DESC'
);
$products = $stmt->fetchAll();

$pageTitle = 'Products Management';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Products Management</h1>
        <p>Manage spare parts catalog.</p>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Part No.</th>
                    <th>Part Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td style="font-family:monospace;"><?= e($p['part_number']) ?></td>
                        <td style="font-weight:500;"><?= e($p['part_name']) ?></td>
                        <td><?= e($p['category_name']) ?></td>
                        <td><?= e($p['brand_name']) ?></td>
                        <td><?= formatCurrency((float)$p['price']) ?></td>
                        <td>
                            <span class="status-badge <?= $p['status']==='ACTIVE' ? 'status-success' : 'status-danger' ?>">
                                <?= e($p['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
