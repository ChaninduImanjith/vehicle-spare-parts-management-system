<?php
/**
 * PayHere Server-to-Server Notification (Webhook)
 * Handles asynchronous payment confirmations.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$merchantId     = $_POST['merchant_id'] ?? '';
$orderId        = $_POST['order_id'] ?? '';
$payhereAmount  = $_POST['payhere_amount'] ?? '';
$payhereCurrency= $_POST['payhere_currency'] ?? '';
$statusCode     = (int)($_POST['status_code'] ?? 0);
$md5sig         = $_POST['md5sig'] ?? '';

$merchantSecret = defined('PAYHERE_MERCHANT_SECRET') ? PAYHERE_MERCHANT_SECRET : '';

// Validate signature
$localMd5sig = strtoupper(
    md5(
        $merchantId . 
        $orderId . 
        $payhereAmount . 
        $payhereCurrency . 
        $statusCode . 
        strtoupper(md5($merchantSecret))
    )
);

if (($localMd5sig === $md5sig) && ($statusCode === 2)) {
    // Payment is SUCCESS
    try {
        $pdo->beginTransaction();
        
        // Update Payment record
        $updPay = $pdo->prepare('UPDATE payment SET status = ?, paid_at = NOW() WHERE order_id = ?');
        $updPay->execute(['PAID', $orderId]);
        
        // Update Order record
        $updOrder = $pdo->prepare('UPDATE customer_order SET status = ? WHERE order_id = ? AND status = ?');
        $updOrder->execute(['PROCESSING', $orderId, 'PENDING']);
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('PayHere Notify Error: ' . $e->getMessage());
    }
} elseif (($localMd5sig === $md5sig) && ($statusCode < 0)) {
    // Payment FAILED or CANCELED
    try {
        $pdo->beginTransaction();
        
        // Update Payment
        $updPay = $pdo->prepare('UPDATE payment SET status = ? WHERE order_id = ?');
        $updPay->execute(['FAILED', $orderId]);
        
        // Update Order
        $updOrder = $pdo->prepare('UPDATE customer_order SET status = ? WHERE order_id = ? AND status = ?');
        $updOrder->execute(['CANCELLED', $orderId, 'PENDING']);
        
        // We must RESTORE stock since order failed
        $items = $pdo->prepare('SELECT part_id, quantity FROM order_item WHERE order_id = ?');
        $items->execute([$orderId]);
        $updStock = $pdo->prepare('UPDATE spare_part SET stock_qty = stock_qty + ? WHERE part_id = ?');
        
        foreach ($items->fetchAll() as $item) {
            $updStock->execute([$item['quantity'], $item['part_id']]);
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('PayHere Notify Cancel Error: ' . $e->getMessage());
    }
}

// Return 200 OK so PayHere knows we received it
http_response_code(200);
