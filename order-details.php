<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireUser();

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirect('/my-orders.php');
}

$userId = (int)$_SESSION['user_id'];

// Fetch Order
$stmt = $pdo->prepare(
    "SELECT 
        o.*,
        p.payment_method, p.payment_status, p.payment_date
     FROM customer_order o
     LEFT JOIN payment p ON o.order_id = p.order_id
     WHERE o.order_id = ? AND o.user_id = ?"
);
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    die("Order not found or access denied.");
}

// Fetch Order Items
$itemStmt = $pdo->prepare(
    "SELECT 
        oi.quantity, oi.unit_price,
        sp.part_name, sp.image_url, sp.part_id
     FROM order_item oi
     INNER JOIN spare_part sp ON oi.part_id = sp.part_id
     WHERE oi.order_id = ?"
);
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();

$pageTitle = 'Order #' . $orderId . ' | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 32px; margin-bottom: 8px;">Order #<?= $orderId ?></h1>
            <p style="color: var(--text-muted);">Placed on <?= date('F d, Y \a\t H:i', strtotime($order['order_date'])) ?></p>
        </div>
        <a href="/my-orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>
    
    <!-- Status Banner -->
    <div style="background: var(--bg-surface); padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--border); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span style="font-size: 14px; color: var(--text-muted); display: block; margin-bottom: 4px;">Order Status</span>
            <span class="badge <?= statusClass($order['status']) ?>" style="font-size: 14px; padding: 6px 12px;"><?= e($order['status']) ?></span>
        </div>
        <div>
            <span style="font-size: 14px; color: var(--text-muted); display: block; margin-bottom: 4px;">Payment Status</span>
            <?php 
                $pStatus = $order['payment_status'] ?? 'UNKNOWN';
                $pClass = match($pStatus) {
                    'COMPLETED' => 'badge-success',
                    'PENDING' => 'badge-warning',
                    'FAILED', 'REFUNDED' => 'badge-danger',
                    default => ''
                };
            ?>
            <span class="badge <?= $pClass ?>" style="font-size: 14px; padding: 6px 12px;"><?= e($pStatus) ?></span>
        </div>
        <div>
            <span style="font-size: 14px; color: var(--text-muted); display: block; margin-bottom: 4px;">Total Amount</span>
            <span style="font-size: 20px; font-weight: 700; color: var(--secondary);"><?= formatCurrency((float)$order['total_amount']) ?></span>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        
        <!-- Order Items -->
        <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border); overflow: hidden;">
            <h3 style="padding: 24px; border-bottom: 1px solid var(--border); font-size: 18px; margin: 0;">Items Ordered</h3>
            
            <div style="padding: 24px;">
                <?php 
                $subtotal = 0;
                foreach ($items as $item): 
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $itemTotal;
                ?>
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
                        <div style="width: 80px; height: 80px; background: var(--bg-surface-alt); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                            <?php if ($item['image_url']): ?>
                                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['part_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="font-size: 24px; color: var(--text-light);">⚙️</div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="flex: 1;">
                            <h4 style="font-size: 16px; margin-bottom: 4px;">
                                <a href="/product.php?id=<?= $item['part_id'] ?>" style="color: var(--text-main);"><?= e($item['part_name']) ?></a>
                            </h4>
                            <div style="color: var(--text-muted); font-size: 14px;">
                                <?= (int)$item['quantity'] ?> x <?= formatCurrency((float)$item['unit_price']) ?>
                            </div>
                        </div>
                        
                        <div style="font-weight: 600;">
                            <?= formatCurrency($itemTotal) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Totals -->
                <div style="margin-top: 24px; width: 100%; max-width: 300px; margin-left: auto;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted);">
                        <span>Subtotal</span>
                        <span><?= formatCurrency($subtotal) ?></span>
                    </div>
                    <?php 
                        // Derive shipping (Total - Subtotal)
                        $shipping = (float)$order['total_amount'] - $subtotal;
                    ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted);">
                        <span>Shipping</span>
                        <span><?= formatCurrency($shipping) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 18px; font-weight: 700;">
                        <span>Order Total</span>
                        <span style="color: var(--primary);"><?= formatCurrency((float)$order['total_amount']) ?></span>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Shipping & Payment Details -->
        <div style="display: flex; flex-direction: column; gap: 30px;">
            <div style="background: var(--bg-surface); padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--border);">
                <h3 style="font-size: 18px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">Shipping Address</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    <?= nl2br(e($order['shipping_address'])) ?>
                </p>
            </div>
            
            <div style="background: var(--bg-surface); padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--border);">
                <h3 style="font-size: 18px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">Payment Details</h3>
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--text-muted); display: block; font-size: 13px; margin-bottom: 4px;">Method</span>
                    <strong><?= e($order['payment_method'] ?? 'N/A') ?></strong>
                </div>
                <?php if (!empty($order['payment_date'])): ?>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 13px; margin-bottom: 4px;">Processed On</span>
                    <strong><?= date('M d, Y H:i', strtotime($order['payment_date'])) ?></strong>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
