<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireUser();

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    redirect('/cart.php');
}

$userId = (int)$_SESSION['user_id'];

// Get user info for form defaults
$stmt = $pdo->prepare('SELECT username, email, phone, address FROM registered_user WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Calculate Totals & Build Items Array
$cartItems = [];
$subtotal = 0.0;
$partIds = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($partIds) - 1) . '?';

$q = $pdo->prepare("SELECT part_id, part_name, price, stock_qty FROM spare_part WHERE part_id IN ($placeholders) AND status = 'ACTIVE'");
$q->execute($partIds);
$results = $q->fetchAll();

foreach ($results as $item) {
    $pid = $item['part_id'];
    $reqQty = $_SESSION['cart'][$pid];
    $actualQty = min($reqQty, (int)$item['stock_qty']);
    
    if ($actualQty <= 0) continue; // Out of stock
    
    $itemTotal = $item['price'] * $actualQty;
    $subtotal += $itemTotal;
    
    $cartItems[] = [
        'id' => $pid,
        'name' => $item['part_name'],
        'price' => (float)$item['price'],
        'qty' => $actualQty,
        'total' => $itemTotal
    ];
}

if (empty($cartItems)) {
    // All items went out of stock
    $_SESSION['cart'] = [];
    redirect('/cart.php');
}

$shippingFee = 500.00; // Flat rate for demo
$totalAmount = $subtotal + $shippingFee;

$pageTitle = 'Checkout | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <h1 style="font-size: 32px; margin-bottom: 24px;">Checkout</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 40px;">
        
        <!-- Shipping & Payment Details Form -->
        <div>
            <div style="background: var(--bg-surface); padding: 32px; border-radius: var(--radius-lg); border: 1px solid var(--border);">
                <h2 style="font-size: 20px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">Shipping Information</h2>
                
                <form action="/payment/create-payment.php" method="POST" id="checkoutForm">
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="first_name">First Name *</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" value="<?= e(explode(' ', $user['username'])[0] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="last_name">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" value="<?= e(explode(' ', $user['username'])[1] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address *</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= e($user['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number *</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="address">Delivery Address *</label>
                        <textarea name="address" id="address" class="form-control" rows="3" required><?= e($user['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="city">City *</label>
                            <input type="text" name="city" id="city" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="country">Country *</label>
                            <input type="text" name="country" id="country" class="form-control" value="Sri Lanka" required>
                        </div>
                    </div>
                    
                    <h2 style="font-size: 20px; margin-top: 32px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">Payment Method</h2>
                    
                    <div class="form-group">
                        <label class="form-check" style="background: var(--bg-surface-alt); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--primary);">
                            <input type="radio" name="payment_method" value="ONLINE" checked>
                            <span style="font-weight: 500; margin-left: 8px;">Pay Online via PayHere (Credit/Debit Card)</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 18px; margin-top: 24px;">Proceed to Payment</button>
                </form>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div>
            <div style="background: var(--bg-surface); padding: 32px; border-radius: var(--radius-lg); border: 1px solid var(--border); position: sticky; top: 100px;">
                <h2 style="font-size: 20px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">Order Summary</h2>
                
                <div style="margin-bottom: 24px;">
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px;">
                            <div style="color: var(--text-muted);">
                                <?= (int)$item['qty'] ?>x <?= e($item['name']) ?>
                            </div>
                            <div style="font-weight: 500;">
                                <?= formatCurrency($item['total']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                        <span style="color: var(--text-muted);">Subtotal</span>
                        <span><?= formatCurrency($subtotal) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                        <span style="color: var(--text-muted);">Shipping</span>
                        <span><?= formatCurrency($shippingFee) ?></span>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 20px;">
                    <span style="font-size: 18px; font-weight: 700; color: var(--secondary);">Total</span>
                    <span style="font-size: 24px; font-weight: 700; color: var(--primary);"><?= formatCurrency($totalAmount) ?></span>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
