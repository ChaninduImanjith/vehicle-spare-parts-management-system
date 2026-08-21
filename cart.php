<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$partId = filter_input(INPUT_POST, 'part_id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_GET, 'part_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;

// Handle Cart Actions
if ($action && $partId) {
    // Re-fetch part to ensure it exists, is active, and check stock
    $stmt = $pdo->prepare('SELECT stock_qty, status FROM spare_part WHERE part_id = ?');
    $stmt->execute([$partId]);
    $part = $stmt->fetch();
    
    if ($part && $part['status'] === 'ACTIVE') {
        $availableStock = (int)$part['stock_qty'];
        
        if ($action === 'add') {
            $currentQty = $_SESSION['cart'][$partId] ?? 0;
            $newQty = $currentQty + $quantity;
            $_SESSION['cart'][$partId] = min($newQty, $availableStock); // Cap at available stock
            redirect('/cart.php');
        } elseif ($action === 'update') {
            if ($quantity > 0) {
                $_SESSION['cart'][$partId] = min($quantity, $availableStock);
            } else {
                unset($_SESSION['cart'][$partId]);
            }
            redirect('/cart.php');
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$partId]);
            redirect('/cart.php');
        }
    }
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    redirect('/cart.php');
}

// Fetch Cart Items Details
$cartItems = [];
$subtotal = 0.0;

if (!empty($_SESSION['cart'])) {
    $partIds = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($partIds) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT part_id, part_name, image_url, price, stock_qty, status FROM spare_part WHERE part_id IN ($placeholders)");
    $stmt->execute($partIds);
    $results = $stmt->fetchAll();
    
    foreach ($results as $item) {
        // If part became inactive or out of stock after adding to cart, handle it gracefully
        if ($item['status'] !== 'ACTIVE') continue;
        
        $pid = $item['part_id'];
        $reqQty = $_SESSION['cart'][$pid];
        
        // Auto-adjust if stock reduced
        $availableQty = (int)$item['stock_qty'];
        if ($availableQty <= 0) {
            unset($_SESSION['cart'][$pid]);
            continue;
        }
        $actualQty = min($reqQty, $availableQty);
        if ($actualQty !== $reqQty) {
            $_SESSION['cart'][$pid] = $actualQty;
        }
        
        $itemTotal = $item['price'] * $actualQty;
        $subtotal += $itemTotal;
        
        $cartItems[] = [
            'part_id' => $pid,
            'part_name' => $item['part_name'],
            'image_url' => $item['image_url'],
            'price' => (float)$item['price'],
            'quantity' => $actualQty,
            'max_qty' => $availableQty,
            'total' => $itemTotal
        ];
    }
}

$pageTitle = 'Shopping Cart | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <h1 style="font-size: 32px; margin-bottom: 24px;">Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div style="background: var(--bg-surface); border-radius: var(--radius-lg); padding: 80px 20px; text-align: center; border: 1px solid var(--border);">
            <div style="font-size: 64px; margin-bottom: 20px;">🛒</div>
            <h2 style="margin-bottom: 12px;">Your cart is empty</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Looks like you haven't added any spare parts yet.</p>
            <a href="/shop.php" class="btn btn-primary">Browse Parts</a>
        </div>
    <?php else: ?>
        
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px;">
            <!-- Cart Items -->
            <div>
                <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border); overflow: hidden;">
                    <div style="display: flex; justify-content: space-between; padding: 16px 24px; background: var(--bg-surface-alt); border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 600;">Item</span>
                        <span style="font-weight: 600;">Total</span>
                    </div>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; padding: 24px; border-bottom: 1px solid var(--border); align-items: center; gap: 20px;">
                            
                            <div style="width: 100px; height: 100px; background: var(--bg-surface-alt); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['part_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="font-size: 32px; color: var(--text-light);">⚙️</div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="flex: 1;">
                                <h3 style="font-size: 16px; margin-bottom: 8px;">
                                    <a href="/product.php?id=<?= $item['part_id'] ?>" style="color: var(--text-main);"><?= e($item['part_name']) ?></a>
                                </h3>
                                <div style="color: var(--text-muted); font-size: 14px; margin-bottom: 12px;"><?= formatCurrency($item['price']) ?> each</div>
                                
                                <form action="/cart.php" method="POST" style="display: flex; align-items: center; gap: 12px;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="part_id" value="<?= $item['part_id'] ?>">
                                    
                                    <div style="display: flex; border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; width: 120px;">
                                        <button type="button" onclick="this.nextElementSibling.stepDown(); this.form.submit();" style="width: 36px; height: 36px; background: transparent; border: none; cursor: pointer;">-</button>
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['max_qty'] ?>" onchange="this.form.submit()" style="flex: 1; border: none; text-align: center; font-weight: 600; width: 40px; outline: none; background: transparent;" readonly>
                                        <button type="button" onclick="this.previousElementSibling.stepUp(); this.form.submit();" style="width: 36px; height: 36px; background: transparent; border: none; cursor: pointer;">+</button>
                                    </div>
                                    
                                    <a href="/cart.php?action=remove&part_id=<?= $item['part_id'] ?>" style="color: var(--danger); font-size: 14px; text-decoration: underline;">Remove</a>
                                </form>
                            </div>
                            
                            <div style="font-size: 18px; font-weight: 700; color: var(--secondary);">
                                <?= formatCurrency($item['total']) ?>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="padding: 16px 24px; text-align: right;">
                        <form action="/cart.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; text-decoration: underline;">Clear Cart</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div>
                <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 24px; position: sticky; top: 100px;">
                    <h3 style="font-size: 18px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">Order Summary</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="color: var(--text-muted);">Subtotal</span>
                        <span style="font-weight: 500;"><?= formatCurrency($subtotal) ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="color: var(--text-muted);">Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); font-size: 20px; font-weight: 700; color: var(--secondary);">
                        <span>Estimated Total</span>
                        <span><?= formatCurrency($subtotal) ?></span>
                    </div>
                    
                    <div style="margin-top: 32px;">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/checkout.php" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; padding: 16px;">Proceed to Checkout</a>
                        <?php else: ?>
                            <a href="/login.php" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; padding: 16px;">Login to Checkout</a>
                            <div style="text-align: center; margin-top: 12px; font-size: 13px; color: var(--text-muted);">
                                You must be logged in to place an order.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="/shop.php" style="color: var(--primary); text-decoration: underline; font-size: 14px;">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
