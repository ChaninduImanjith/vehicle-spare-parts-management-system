<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/cart.php');
}

$userId = (int)$_SESSION['user_id'];

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$address   = trim($_POST['address'] ?? '');
$city      = trim($_POST['city'] ?? '');
$country   = trim($_POST['country'] ?? '');

if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($address) || empty($city)) {
    die("Missing required shipping fields.");
}

// Recalculate totals from DB
if (empty($_SESSION['cart'])) {
    redirect('/cart.php');
}

$cartItems = [];
$subtotal = 0.0;
$partIds = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($partIds) - 1) . '?';

$q = $pdo->prepare("SELECT part_id, part_name, price, stock_qty FROM spare_part WHERE part_id IN ($placeholders) AND status = 'ACTIVE' FOR UPDATE");

// Transaction start for order creation
try {
    $pdo->beginTransaction();
    
    $q->execute($partIds);
    $results = $q->fetchAll();

    foreach ($results as $item) {
        $pid = $item['part_id'];
        $reqQty = $_SESSION['cart'][$pid];
        $actualQty = min($reqQty, (int)$item['stock_qty']);
        
        if ($actualQty <= 0) continue;
        
        $itemTotal = $item['price'] * $actualQty;
        $subtotal += $itemTotal;
        
        $cartItems[] = [
            'id' => $pid,
            'name' => $item['part_name'],
            'price' => (float)$item['price'],
            'qty' => $actualQty
        ];
    }

    if (empty($cartItems)) {
        throw new Exception("Items are out of stock.");
    }

    $shippingFee = 500.00;
    $totalAmount = $subtotal + $shippingFee;

    // 1. Create Order record (Status = PENDING)
    $shippingAddress = "$address, $city, $country";
    $insOrder = $pdo->prepare('INSERT INTO customer_order (user_id, total_amount, shipping_address, status) VALUES (?, ?, ?, ?)');
    $insOrder->execute([$userId, $totalAmount, $shippingAddress, 'PENDING']);
    $orderId = (int)$pdo->lastInsertId();

    // 2. Create Order Items and decrease stock
    $insItem = $pdo->prepare('INSERT INTO order_item (order_id, part_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
    $updStock = $pdo->prepare('UPDATE spare_part SET stock_qty = stock_qty - ? WHERE part_id = ?');

    $itemNames = [];
    foreach ($cartItems as $c) {
        $insItem->execute([$orderId, $c['id'], $c['qty'], $c['price']]);
        $updStock->execute([$c['qty'], $c['id']]);
        $itemNames[] = $c['name'];
    }

    // 3. Create initial Payment record (Status = PENDING)
    $insPayment = $pdo->prepare('INSERT INTO payment (order_id, gateway_id, amount, status) VALUES (?, ?, ?, ?)');
    $insPayment->execute([$orderId, 1, $totalAmount, 'PENDING']);

    $pdo->commit();
    
    // Clear cart
    $_SESSION['cart'] = [];

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing order: " . $e->getMessage());
}

// -------------------------------------------------------------
// PayHere Integration
// -------------------------------------------------------------
$merchantId = defined('PAYHERE_MERCHANT_ID') ? PAYHERE_MERCHANT_ID : '';
$merchantSecret = defined('PAYHERE_MERCHANT_SECRET') ? PAYHERE_MERCHANT_SECRET : '';
$baseUrl = defined('APP_URL') ? APP_URL : 'http://localhost';
$currency = 'LKR';

$orderIdStr = (string)$orderId;
$amountStr = number_format($totalAmount, 2, '.', '');

// Generate Hash: strtoupper(md5(merchant_id + order_id + amount + currency + strtoupper(md5(merchant_secret))))
$hash = strtoupper(
    md5(
        $merchantId . 
        $orderIdStr . 
        $amountStr . 
        $currency . 
        strtoupper(md5($merchantSecret))
    )
);

// PayHere endpoint
$payHereUrl = defined('PAYHERE_SANDBOX') && PAYHERE_SANDBOX 
    ? 'https://sandbox.payhere.lk/pay/checkout' 
    : 'https://www.payhere.lk/pay/checkout';

// Auto-submit form to PayHere
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Secure Payment...</title>
    <style>
        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background: #f8fafc; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #0ea5e9; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body onload="document.getElementById('payhere-form').submit();">
    <div class="loader"></div>
    <h2>Redirecting to Secure Payment Gateway...</h2>
    <p>Please do not close or refresh this page.</p>
    
    <form id="payhere-form" method="post" action="<?= $payHereUrl ?>" style="display:none;">
        <input type="hidden" name="merchant_id" value="<?= htmlspecialchars($merchantId) ?>">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($baseUrl . '/payment/return.php?order_id=' . $orderId) ?>">
        <input type="hidden" name="cancel_url" value="<?= htmlspecialchars($baseUrl . '/payment/cancel.php?order_id=' . $orderId) ?>">
        <input type="hidden" name="notify_url" value="<?= htmlspecialchars($baseUrl . '/payment/notify.php') ?>">  
        
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderIdStr) ?>">
        <input type="hidden" name="items" value="<?= htmlspecialchars(implode(', ', $itemNames)) ?>">
        <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amountStr) ?>">  
        
        <input type="hidden" name="first_name" value="<?= htmlspecialchars($firstName) ?>">
        <input type="hidden" name="last_name" value="<?= htmlspecialchars($lastName) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
        <input type="hidden" name="address" value="<?= htmlspecialchars($address) ?>">
        <input type="hidden" name="city" value="<?= htmlspecialchars($city) ?>">
        <input type="hidden" name="country" value="<?= htmlspecialchars($country) ?>">
        
        <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">
    </form>
</body>
</html>
