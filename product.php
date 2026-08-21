<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$partId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$partId) {
    redirect('/shop.php');
}

// Fetch part details
$stmt = $pdo->prepare(
    "SELECT 
        sp.*, 
        c.category_name, 
        b.brand_name, b.is_authorized,
        co.country_name,
        s.supplier_name
     FROM spare_part sp
     INNER JOIN category c ON sp.category_id = c.category_id
     INNER JOIN brand b ON sp.brand_id = b.brand_id
     INNER JOIN country co ON b.country_id = co.country_id
     INNER JOIN supplier s ON sp.supplier_id = s.supplier_id
     WHERE sp.part_id = ? AND sp.status = 'ACTIVE'
     LIMIT 1"
);
$stmt->execute([$partId]);
$part = $stmt->fetch();

if (!$part) {
    http_response_code(404);
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding: 100px 0; text-align: center;">';
    echo '<h2>Part Not Found</h2>';
    echo '<p>The spare part you are looking for does not exist or is no longer active.</p>';
    echo '<a href="/shop.php" class="btn btn-primary" style="margin-top:20px;">Return to Shop</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch compatible vehicles
$compatStmt = $pdo->prepare(
    "SELECT 
        vmk.make_name,
        vmd.model_name,
        vmd.year_from,
        vmd.year_to
     FROM part_vehicle_compatibility pvc
     INNER JOIN vehicle_model vmd ON pvc.model_id = vmd.model_id
     INNER JOIN vehicle_make vmk ON vmd.make_id = vmk.make_id
     WHERE pvc.part_id = ?
     ORDER BY vmk.make_name, vmd.model_name"
);
$compatStmt->execute([$partId]);
$compatibilities = $compatStmt->fetchAll();

$pageTitle = e($part['part_name']) . ' | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    
    <!-- Breadcrumb -->
    <nav style="margin-top: 20px; font-size: 14px; color: var(--text-muted);">
        <a href="/">Home</a> &rsaquo; 
        <a href="/shop.php">Shop</a> &rsaquo; 
        <a href="/shop.php?category=<?= (int)$part['category_id'] ?>"><?= e($part['category_name']) ?></a> &rsaquo; 
        <span style="color: var(--text-main); font-weight: 500;"><?= e($part['part_name']) ?></span>
    </nav>

    <div class="product-details-container">
        
        <!-- Left: Image Gallery -->
        <div class="pd-image-gallery">
            <?php if ($part['image_url']): ?>
                <img src="<?= e($part['image_url']) ?>" alt="<?= e($part['part_name']) ?>">
            <?php else: ?>
                <div style="font-size: 80px; color: var(--text-light);">⚙️</div>
            <?php endif; ?>
        </div>
        
        <!-- Right: Info -->
        <div class="pd-info">
            <div class="pd-brand">
                <?= e($part['brand_name']) ?>
                <?php if ((int)$part['is_authorized'] === 1): ?>
                    <span style="display:inline-flex; align-items:center; color:var(--success); margin-left:8px; font-size:12px;" title="Authorized Dealer">✓ Authorized</span>
                <?php endif; ?>
            </div>
            
            <h1 class="pd-title"><?= e($part['part_name']) ?></h1>
            
            <div class="pd-price-wrap">
                <div class="pd-price"><?= formatCurrency((float)$part['price']) ?></div>
                
                <?php if ($part['stock_qty'] > 5): ?>
                    <span class="badge badge-success">In Stock</span>
                <?php elseif ($part['stock_qty'] > 0): ?>
                    <span class="badge badge-warning">Only <?= (int)$part['stock_qty'] ?> left</span>
                <?php else: ?>
                    <span class="badge badge-danger">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <div class="pd-desc">
                <?= nl2br(e($part['description'] ?: 'No description available for this part.')) ?>
            </div>
            
            <div class="pd-meta-grid">
                <div class="pd-meta-item">
                    <span class="pd-meta-label">Part Number</span>
                    <span class="pd-meta-value"><?= e($part['part_number']) ?></span>
                </div>
                <div class="pd-meta-item">
                    <span class="pd-meta-label">OEM Number</span>
                    <span class="pd-meta-value"><?= e($part['oem_number'] ?: '—') ?></span>
                </div>
                <div class="pd-meta-item">
                    <span class="pd-meta-label">Category</span>
                    <span class="pd-meta-value"><?= e($part['category_name']) ?></span>
                </div>
                <div class="pd-meta-item">
                    <span class="pd-meta-label">Origin</span>
                    <span class="pd-meta-value"><?= e($part['country_name']) ?></span>
                </div>
                <div class="pd-meta-item">
                    <span class="pd-meta-label">Size/Spec</span>
                    <span class="pd-meta-value"><?= e($part['size'] ?: 'Standard') ?></span>
                </div>
            </div>
            
            <!-- Add to Cart Form -->
            <form action="/cart.php" method="POST" class="pd-actions">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="part_id" value="<?= (int)$part['part_id'] ?>">
                
                <div class="qty-input" <?= $part['stock_qty'] <= 0 ? 'style="opacity:0.5; pointer-events:none;"' : '' ?>>
                    <button type="button" class="qty-btn" id="qty-minus">−</button>
                    <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= (int)$part['stock_qty'] ?>" readonly>
                    <button type="button" class="qty-btn" id="qty-plus">+</button>
                </div>
                
                <?php if ($part['stock_qty'] > 0): ?>
                    <button type="submit" class="btn btn-primary" style="flex: 1; border-radius: var(--radius-full); font-size: 16px;">
                        Add to Cart
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" style="flex: 1; border-radius: var(--radius-full);" disabled>
                        Out of Stock
                    </button>
                <?php endif; ?>
            </form>
            
        </div>
    </div>
    
    <!-- Vehicle Compatibility Section -->
    <?php if (!empty($compatibilities)): ?>
    <div class="compatibility-section">
        <div class="section-header">
            <h2 class="section-title">Compatible Vehicles</h2>
        </div>
        
        <div class="compatibility-grid">
            <?php foreach ($compatibilities as $comp): ?>
                <div class="compat-item">
                    <div style="font-size:24px;">🚗</div>
                    <div>
                        <div style="font-weight:600; color:var(--text-main);"><?= e($comp['make_name']) ?> <?= e($comp['model_name']) ?></div>
                        <div style="font-size:13px; color:var(--text-muted);">
                            <?php 
                            if ($comp['year_from'] && $comp['year_to']) {
                                echo $comp['year_from'] . ' - ' . $comp['year_to'];
                            } elseif ($comp['year_from']) {
                                echo $comp['year_from'] . ' onwards';
                            } else {
                                echo 'All years';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
