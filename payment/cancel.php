<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

// Note: notify.php handles the actual DB status updates and stock restoration asynchronously.
// This is just the user-facing view.

$pageTitle = 'Payment Cancelled';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 100px 20px; text-align: center;">
    <div style="font-size: 80px; color: var(--danger); margin-bottom: 20px;">❌</div>
    <h1 style="margin-bottom: 16px;">Payment Failed or Cancelled</h1>
    
    <p style="color: var(--text-muted); margin-bottom: 32px;">
        Your payment was not completed. If any funds were deducted, they will be reversed by your bank.
        <?php if ($orderId): ?>
            <br>Order <strong>#<?= $orderId ?></strong> has been cancelled.
        <?php endif; ?>
    </p>
    
    <div style="display: flex; gap: 16px; justify-content: center;">
        <a href="/cart.php" class="btn btn-primary">Return to Cart</a>
        <a href="/shop.php" class="btn btn-outline">Continue Shopping</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
