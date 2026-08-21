<?php
/**
 * Main site header for customer-facing pages.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Vehicle Spare Parts Management System';

// Calculate cart count
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartCount += (int)$qty;
    }
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/store.css">
</head>
<body>

    <header class="site-header">
        <div class="container">
            <nav class="navbar">
                <a href="/index.php" class="brand">
                    <div class="brand-icon">⚙</div>
                    SpareHub
                </a>

                <button class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Toggle navigation">
                    ☰
                </button>

                <ul class="nav-links" id="navLinks">
                    <li><a href="/index.php" class="nav-link">Home</a></li>
                    <li><a href="/shop.php" class="nav-link">Shop</a></li>
                    
                    <?php if ($isLoggedIn): ?>
                        <li><a href="/request-part.php" class="nav-link">Request Part</a></li>
                        <li><a href="/my-orders.php" class="nav-link">My Orders</a></li>
                    <?php endif; ?>
                </ul>

                <div class="nav-actions">
                    <a href="/cart.php" class="cart-btn" aria-label="Shopping Cart">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if ($isLoggedIn): ?>
                        <a href="/profile.php" class="btn btn-outline" style="padding:8px 16px; border-radius: var(--radius-sm);">Profile</a>
                        <a href="/logout.php" class="btn btn-primary" style="padding:8px 16px; border-radius: var(--radius-sm);">Logout</a>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-outline" style="padding:8px 16px; border-radius: var(--radius-sm);">Login</a>
                        <a href="/register.php" class="btn btn-primary" style="padding:8px 16px; border-radius: var(--radius-sm);">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="main-content">
