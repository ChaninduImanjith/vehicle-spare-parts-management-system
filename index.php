<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home | SpareHub Vehicle Parts';

// Fetch featured parts (random active items for demo)
$stmt = $pdo->query(
    "SELECT 
        sp.part_id, sp.part_name, sp.price, sp.image_url, sp.stock_qty,
        b.brand_name
     FROM spare_part sp
     INNER JOIN brand b ON sp.brand_id = b.brand_id
     WHERE sp.status = 'ACTIVE'
     ORDER BY sp.part_id DESC
     LIMIT 8"
);
$featuredParts = $stmt->fetchAll();

// Fetch categories for browse section
$catStmt = $pdo->query(
    "SELECT category_id, category_name 
     FROM category 
     WHERE parent_category_id IS NULL
     ORDER BY category_name ASC
     LIMIT 6"
);
$categories = $catStmt->fetchAll();

// Fetch Vehicle Makes for search dropdown
$makesStmt = $pdo->query("SELECT make_id, make_name FROM vehicle_make ORDER BY make_name ASC");
$makes = $makesStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Find the Perfect Parts for Your Vehicle</h1>
            <p>Premium quality spare parts for all makes and models. Fast delivery and guaranteed fitment.</p>
            
            <form action="/shop.php" method="GET" class="hero-search">
                <input type="text" name="q" placeholder="Search by part name, OEM number...">
                <button type="submit" class="btn btn-primary">Search Parts</button>
            </form>
        </div>
    </div>
</section>

<!-- Vehicle Compatibility Quick Search -->
<section style="background: var(--bg-surface); padding: 40px 0; border-bottom: 1px solid var(--border);">
    <div class="container">
        <h3 style="text-align: center; margin-bottom: 24px;">Search by Vehicle Compatibility</h3>
        <form action="/shop.php" method="GET" style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; max-width: 800px; margin: 0 auto;">
            <select name="make" id="filter_make" class="form-control" style="width: auto; min-width: 200px;">
                <option value="">Select Make</option>
                <?php foreach ($makes as $make): ?>
                    <option value="<?= (int)$make['make_id'] ?>"><?= e($make['make_name']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="model" id="filter_model" class="form-control" style="width: auto; min-width: 200px;" disabled>
                <option value="">Select Model</option>
            </select>
            
            <button type="submit" class="btn btn-secondary">Find Parts</button>
        </form>
    </div>
</section>

<div class="container" style="padding-top: 60px; padding-bottom: 60px;">

    <!-- Browse Categories -->
    <div class="section-header">
        <div>
            <h2 class="section-title">Browse Categories</h2>
            <p class="section-desc">Find exactly what you need by system.</p>
        </div>
        <a href="/shop.php" class="btn btn-outline">View All</a>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; margin-bottom: 60px;">
        <?php foreach ($categories as $cat): ?>
            <a href="/shop.php?category=<?= (int)$cat['category_id'] ?>" 
               style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; text-align: center; transition: var(--transition);">
                <div style="width: 60px; height: 60px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px;">
                    🔧
                </div>
                <h3 style="font-size: 16px; color: var(--text-main);"><?= e($cat['category_name']) ?></h3>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Featured Products -->
    <div class="section-header">
        <div>
            <h2 class="section-title">Featured Parts</h2>
            <p class="section-desc">Top quality components for your vehicle.</p>
        </div>
    </div>
    
    <div class="product-grid">
        <?php foreach ($featuredParts as $part): ?>
            <div class="product-card">
                <a href="/product.php?id=<?= (int)$part['part_id'] ?>" class="product-image">
                    <?php if ($part['image_url']): ?>
                        <img src="<?= e($part['image_url']) ?>" alt="<?= e($part['part_name']) ?>" loading="lazy">
                    <?php else: ?>
                        <!-- Fallback placeholder -->
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-light); font-size: 40px;">
                            ⚙️
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-badges">
                        <?php if ($part['stock_qty'] <= 0): ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php elseif ($part['stock_qty'] <= 5): ?>
                            <span class="badge badge-warning">Low Stock</span>
                        <?php endif; ?>
                    </div>
                </a>
                
                <div class="product-info">
                    <div class="product-brand"><?= e($part['brand_name']) ?></div>
                    <h3 class="product-title">
                        <a href="/product.php?id=<?= (int)$part['part_id'] ?>"><?= e($part['part_name']) ?></a>
                    </h3>
                    
                    <div class="product-footer">
                        <div class="product-price"><?= formatCurrency((float)$part['price']) ?></div>
                        <a href="/product.php?id=<?= (int)$part['part_id'] ?>" class="btn btn-primary btn-sm" style="padding: 8px 16px;">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
