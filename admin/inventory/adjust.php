<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
requireAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partId = filter_input(INPUT_POST, 'part_id', FILTER_VALIDATE_INT);
    $movementType = $_POST['movement_type'] ?? '';
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $reference = trim($_POST['reference'] ?? '');
    
    $allowedTypes = ['IN', 'OUT', 'ADJUSTMENT', 'RETURN'];
    
    if (!$partId || !$quantity || !in_array($movementType, $allowedTypes)) {
        $error = 'Please fill in all required fields correctly.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // 1. Record movement
            $insMove = $pdo->prepare('INSERT INTO stock_movement (part_id, movement_type, quantity, reference_note, admin_id) VALUES (?, ?, ?, ?, ?)');
            $insMove->execute([$partId, $movementType, $quantity, $reference, adminId()]);
            
            // 2. Adjust stock
            // If movement is OUT or ADJUSTMENT (with negative quantity), subtract.
            // Wait, the schema says: "quantity INT NOT NULL (positive for IN/RETURN, negative for OUT/ADJUSTMENT reductions)"
            // So we just ADD the quantity to stock_qty.
            $updStock = $pdo->prepare('UPDATE spare_part SET stock_qty = stock_qty + ? WHERE part_id = ?');
            $updStock->execute([$quantity, $partId]);
            
            $pdo->commit();
            $success = 'Stock adjusted successfully.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to adjust stock. Ensure sufficient quantity exists.';
        }
    }
}

// Fetch parts for dropdown
$parts = $pdo->query('SELECT part_id, part_number, part_name, stock_qty FROM spare_part ORDER BY part_name ASC')->fetchAll();

$pageTitle = 'Stock Adjustment';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Stock Adjustment</h1>
        <p>Manually adjust inventory levels (Receive stock, corrections, damages).</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-secondary">Back to Inventory</a>
    </div>
</div>

<div class="panel" style="max-width: 600px;">
    <div class="panel-body">
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Select Part</label>
                <select name="part_id" class="form-control" required>
                    <option value="">-- Choose a part --</option>
                    <?php foreach ($parts as $p): ?>
                        <option value="<?= $p['part_id'] ?>">
                            <?= e($p['part_number']) ?> - <?= e($p['part_name']) ?> (Current: <?= $p['stock_qty'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Movement Type</label>
                    <select name="movement_type" class="form-control" required>
                        <option value="IN">IN (Receive Stock)</option>
                        <option value="OUT">OUT (Manual Reduction)</option>
                        <option value="ADJUSTMENT">ADJUSTMENT (Correction)</option>
                        <option value="RETURN">RETURN (Customer Return)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Quantity (+/-)</label>
                    <input type="number" name="quantity" class="form-control" required placeholder="e.g. 10 or -5">
                    <div class="form-hint">Use negative numbers to reduce stock.</div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Reference Note / Reason</label>
                <input type="text" name="reference" class="form-control" placeholder="PO Number, Damage report, etc.">
            </div>
            
            <button type="submit" class="btn btn-primary">Process Adjustment</button>
        </form>
        
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
