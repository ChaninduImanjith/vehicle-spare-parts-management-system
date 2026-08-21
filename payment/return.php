<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$pageTitle = 'Payment Successful';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 100px 20px; text-align: center;">
    <div style="font-size: 80px; color: var(--success); margin-bottom: 20px;">✅</div>
    <h1 style="margin-bottom: 16px;">Payment Successful!</h1>
    
    <p style="color: var(--text-muted); margin-bottom: 32px;">
        Thank you for your order. Your payment has been processed successfully.
        <?php if ($orderId): ?>
            <br>Your Order ID is: <strong>#<?= $orderId ?></strong>
        <?php endif; ?>
    </p>
    
    <div style="display: flex; gap: 16px; justify-content: center;">
        <a href="/my-orders.php" class="btn btn-primary">View My Orders</a>
        <a href="/shop.php" class="btn btn-outline">Continue Shopping</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
