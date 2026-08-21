<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireUser();
$userId = (int)$_SESSION['user_id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partName = trim($_POST['part_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $qty = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $brand = trim($_POST['preferred_brand'] ?? '');
    $country = trim($_POST['preferred_country'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $budgetMin = filter_input(INPUT_POST, 'budget_min', FILTER_VALIDATE_FLOAT);
    $budgetMax = filter_input(INPUT_POST, 'budget_max', FILTER_VALIDATE_FLOAT);
    
    if (empty($partName) || !$qty || $qty < 1) {
        $error = 'Part name and a valid quantity are required.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO product_request 
                 (user_id, part_name, part_description, quantity, preferred_brand, preferred_country, size, budget_min, budget_max, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId, 
                $partName, 
                $description ?: null, 
                $qty, 
                $brand ?: null, 
                $country ?: null, 
                $size ?: null, 
                $budgetMin ?: null, 
                $budgetMax ?: null, 
                'PENDING'
            ]);
            $success = 'Your request has been submitted successfully. Our team will review it and contact you soon.';
        } catch (Exception $e) {
            error_log('Product request error: ' . $e->getMessage());
            $error = 'An error occurred while submitting your request.';
        }
    }
}

// Fetch user's previous requests
$reqStmt = $pdo->prepare('SELECT request_id, part_name, quantity, status, requested_at FROM product_request WHERE user_id = ? ORDER BY requested_at DESC');
$reqStmt->execute([$userId]);
$myRequests = $reqStmt->fetchAll();

$pageTitle = 'Request a Part | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    
    <div style="text-align: center; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">
        <h1 style="font-size: 36px; margin-bottom: 16px;">Can't find what you need?</h1>
        <p style="color: var(--text-muted); font-size: 16px;">Submit a request for a specific spare part and our global sourcing team will find it for you.</p>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
        
        <!-- Request Form -->
        <div style="background: var(--bg-surface); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 20px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">Part Request Form</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="background: var(--success); color: white; padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: var(--danger); color: white; padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <form action="/request-part.php" method="POST">
                
                <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="part_name">Part Name or OEM Number *</label>
                        <input type="text" name="part_name" id="part_name" class="form-control" required placeholder="e.g. Toyota Corolla 2018 Brake Pads">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="quantity">Quantity *</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Detailed Description (Optional)</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Any specific requirements, condition (new/used), vehicle chassis number, etc."></textarea>
                </div>
                
                <h3 style="font-size: 16px; margin-top: 32px; margin-bottom: 16px;">Preferences (Optional)</h3>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="preferred_brand">Preferred Brand</label>
                        <input type="text" name="preferred_brand" id="preferred_brand" class="form-control" placeholder="e.g. Bosch">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="preferred_country">Origin Country</label>
                        <input type="text" name="preferred_country" id="preferred_country" class="form-control" placeholder="e.g. Japan">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="size">Size / Specification</label>
                        <input type="text" name="size" id="size" class="form-control" placeholder="e.g. 15 inch">
                    </div>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="budget_min">Minimum Budget (LKR)</label>
                        <input type="number" name="budget_min" id="budget_min" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="budget_max">Maximum Budget (LKR)</label>
                        <input type="number" name="budget_max" id="budget_max" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 24px; width: 100%; padding: 16px; font-size: 16px;">Submit Request</button>
            </form>
        </div>
        
        <!-- Request History -->
        <div>
            <div style="background: var(--bg-surface); padding: 32px; border-radius: var(--radius-lg); border: 1px solid var(--border); position: sticky; top: 100px;">
                <h2 style="font-size: 18px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">My Recent Requests</h2>
                
                <?php if (empty($myRequests)): ?>
                    <p style="color: var(--text-muted); font-size: 14px; text-align: center; padding: 20px 0;">You haven't submitted any requests yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($myRequests as $req): ?>
                            <div style="border: 1px solid var(--border); padding: 16px; border-radius: var(--radius-md);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <strong style="color: var(--secondary);"><?= e($req['part_name']) ?></strong>
                                    <span class="badge <?= statusClass($req['status']) ?>" style="font-size: 11px; padding: 2px 6px;"><?= e($req['status']) ?></span>
                                </div>
                                <div style="font-size: 13px; color: var(--text-muted);">
                                    Qty: <?= (int)$req['quantity'] ?> &bull; <?= date('M d, Y', strtotime($req['requested_at'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
