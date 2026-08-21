<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Fetch current inventory overview
$stmt = $pdo->query(
    'SELECT 
        sp.part_id, sp.part_number, sp.part_name, sp.stock_qty, sp.reorder_level, sp.status,
        b.brand_name
     FROM spare_part sp
     INNER JOIN brand b ON sp.brand_id = b.brand_id
     ORDER BY 
        CASE WHEN sp.stock_qty <= sp.reorder_level THEN 1 ELSE 2 END,
        sp.stock_qty ASC'
);
$inventory = $stmt->fetchAll();

$pageTitle = 'Inventory Management';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Inventory Management</h1>
        <p>Monitor stock levels and reorder alerts.</p>
    </div>
    <div class="page-actions">
        <a href="adjust.php" class="btn btn-primary">+ Adjust Stock</a>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Part No.</th>
                    <th>Part Name</th>
                    <th>Brand</th>
                    <th>Stock Qty</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item): 
                    $isLowStock = (int)$item['stock_qty'] <= (int)$item['reorder_level'];
                ?>
                    <tr class="<?= $isLowStock ? 'low-stock-row' : '' ?>">
                        <td style="font-family:monospace;"><?= e($item['part_number']) ?></td>
                        <td><?= e($item['part_name']) ?></td>
                        <td><?= e($item['brand_name']) ?></td>
                        <td>
                            <span class="<?= $isLowStock ? 'stock-danger' : 'stock-ok' ?>" style="font-weight:700; font-size:16px;">
                                <?= (int)$item['stock_qty'] ?>
                            </span>
                        </td>
                        <td><?= (int)$item['reorder_level'] ?></td>
                        <td>
                            <span class="status-badge <?= $item['status']==='ACTIVE' ? 'status-success' : 'status-danger' ?>">
                                <?= e($item['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="history.php?id=<?= (int)$item['part_id'] ?>" class="btn btn-sm btn-secondary">History</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.low-stock-row { background-color: #fef2f2 !important; }
.stock-danger { color: #dc2626; }
.stock-ok { color: #059669; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
