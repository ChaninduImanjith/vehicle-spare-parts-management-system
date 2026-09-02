<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home | SpareHub Vehicle Parts';

// Category image & icon mapping
$categoryMeta = [
    'Brakes'       => ['img' => '/assets/images/categories/brakes.jpg',       'icon' => '🛑'],
    'Engine'       => ['img' => '/assets/images/categories/engine.jpg',       'icon' => '⚙️'],
    'Electrical'   => ['img' => '/assets/images/categories/electrical.jpg',   'icon' => '⚡'],
    'Filters'      => ['img' => '/assets/images/categories/filters.jpg',      'icon' => '🔩'],
    'Suspension'   => ['img' => '/assets/images/categories/suspension.jpg',   'icon' => '🚗'],
    'Transmission' => ['img' => '/assets/images/categories/transmission.jpg', 'icon' => '🔧'],
    'Lighting'     => ['img' => '/assets/images/categories/lighting.jpg',     'icon' => '💡'],
    'Body'         => ['img' => '/assets/images/categories/body.jpg',         'icon' => '🚘'],
];

// Fetch featured parts – newest 8 with images prioritised
$stmt = $pdo->query(
    "SELECT
        sp.part_id, sp.part_name, sp.price, sp.image_url, sp.stock_qty,
        b.brand_name, c.category_name
     FROM spare_part sp
     INNER JOIN brand b ON sp.brand_id = b.brand_id
     INNER JOIN category c ON sp.category_id = c.category_id
     WHERE sp.status = 'ACTIVE'
     ORDER BY (sp.image_url IS NOT NULL) DESC, sp.part_id DESC
     LIMIT 8"
);
$featuredParts = $stmt->fetchAll();

// Fetch all categories with their product count
$catStmt = $pdo->query(
    "SELECT c.category_id, c.category_name,
            COUNT(sp.part_id) AS part_count
     FROM category c
     LEFT JOIN spare_part sp ON sp.category_id = c.category_id AND sp.status = 'ACTIVE'
     WHERE c.parent_category_id IS NULL
     GROUP BY c.category_id, c.category_name
     ORDER BY c.category_name ASC"
);
$categories = $catStmt->fetchAll();

// Fetch vehicle makes for the compatibility search
$makesStmt = $pdo->query("SELECT make_id, make_name FROM vehicle_make ORDER BY make_name ASC");
$makes = $makesStmt->fetchAll();

// Fetch brand names for the brand strip
$brandsStmt = $pdo->query("SELECT brand_name FROM brand WHERE is_authorized = 1 ORDER BY brand_name ASC");
$brands = $brandsStmt->fetchAll();

// Total parts in store
$totalPartsStmt = $pdo->query("SELECT COUNT(*) FROM spare_part WHERE status = 'ACTIVE'");
$totalParts = (int)$totalPartsStmt->fetchColumn();

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

<!-- Trust / Stats Bar -->
<section class="trust-bar">
    <div class="container">
        <div class="trust-bar-inner">
            <div>
                <span class="trust-item-icon">📦</span>
                <span class="trust-item-value"><?= $totalParts ?>+</span>
                <span class="trust-item-label">Parts in Stock</span>
            </div>
            <div>
                <span class="trust-item-icon">🚚</span>
                <span class="trust-item-value">48h</span>
                <span class="trust-item-label">Express Delivery</span>
            </div>
            <div>
                <span class="trust-item-icon">✅</span>
                <span class="trust-item-value">OEM</span>
                <span class="trust-item-label">Quality Guaranteed</span>
            </div>
            <div>
                <span class="trust-item-icon">🔄</span>
                <span class="trust-item-value">30-Day</span>
                <span class="trust-item-label">Easy Returns</span>
            </div>
        </div>
    </div>
</section>

<!-- Vehicle Compatibility Quick Search -->
<section style="background: var(--bg-surface); padding: 40px 0; border-bottom: 1px solid var(--border);">
    <div class="container">
        <h3 style="text-align: center; margin-bottom: 8px;">Search by Vehicle Compatibility</h3>
        <p style="text-align:center; color: var(--text-muted); margin-bottom: 24px; font-size:14px;">Select your car to see only the parts that fit.</p>
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

<!-- Brand Logos Strip -->
<section class="brands-strip">
    <div class="container">
        <p style="text-align:center; font-size:12px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-bottom:20px;">Authorised Brands</p>
        <div class="brands-strip-inner">
            <?php foreach ($brands as $b): ?>
                <a href="/shop.php?brand=<?= urlencode($b['brand_name']) ?>" class="brand-logo-pill"><?= e($b['brand_name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="container" style="padding-bottom: 60px;">

    <!-- Browse Categories with Real Images -->
    <div class="section-header">
        <div>
            <h2 class="section-title">Browse Categories</h2>
            <p class="section-desc">Find exactly what you need — <?= count($categories) ?> system categories available.</p>
        </div>
        <a href="/shop.php" class="btn btn-outline">View All Parts</a>
    </div>

    <div class="categories-grid">
        <?php foreach ($categories as $cat):
            $name = $cat['category_name'];
            $meta = $categoryMeta[$name] ?? ['img' => null, 'icon' => '🔧'];
            $count = (int)$cat['part_count'];
        ?>
            <a href="/shop.php?category=<?= (int)$cat['category_id'] ?>" class="category-card">
                <?php if ($meta['img']): ?>
                    <img src="<?= e($meta['img']) ?>" alt="<?= e($name) ?>" loading="lazy">
                <?php else: ?>
                    <div style="width:100%;height:100%;background:var(--bg-surface-alt);"></div>
                <?php endif; ?>
                <div class="category-card-overlay">
                    <span class="category-card-icon"><?= $meta['icon'] ?></span>
                    <span class="category-card-name"><?= e($name) ?></span>
                    <span class="category-card-count"><?= $count ?> parts</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Featured Products -->
    <div class="section-header">
        <div>
            <h2 class="section-title">Featured Parts</h2>
            <p class="section-desc">Top quality components for your vehicle — hand-picked for you.</p>
        </div>
        <a href="/shop.php" class="btn btn-outline">Shop All</a>
    </div>
    
    <div class="product-grid">
        <?php foreach ($featuredParts as $part): ?>
            <div class="product-card">
                <a href="/product.php?id=<?= (int)$part['part_id'] ?>" class="product-image">
                    <?php if ($part['image_url']): ?>
                        <img src="<?= e($part['image_url']) ?>" alt="<?= e($part['part_name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-light); font-size: 48px; background: var(--bg-surface-alt);">
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
                    <div class="product-brand"><?= e($part['brand_name']) ?> · <span style="color:var(--text-muted);font-weight:400;"><?= e($part['category_name']) ?></span></div>
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

    <!-- Category Spotlight Rows (one row per category, 3 products each) -->
    <?php foreach ($categories as $cat):
        $catName = $cat['category_name'];
        $meta    = $categoryMeta[$catName] ?? ['img' => null, 'icon' => '🔧'];

        $spotStmt = $pdo->prepare(
            "SELECT sp.part_id, sp.part_name, sp.price, sp.image_url, sp.stock_qty, b.brand_name
             FROM spare_part sp
             INNER JOIN brand b ON sp.brand_id = b.brand_id
             WHERE sp.category_id = ? AND sp.status = 'ACTIVE'
             ORDER BY (sp.image_url IS NOT NULL) DESC, sp.part_id ASC
             LIMIT 3"
        );
        $spotStmt->execute([(int)$cat['category_id']]);
        $spotParts = $spotStmt->fetchAll();
        if (empty($spotParts)) continue;
    ?>
        <div style="margin-top: 60px;">
            <div class="section-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:28px;"><?= $meta['icon'] ?></span>
                    <div>
                        <h2 class="section-title" style="margin-bottom:4px;"><?= e($catName) ?></h2>
                        <p class="section-desc"><?= (int)$cat['part_count'] ?> parts available</p>
                    </div>
                </div>
                <a href="/shop.php?category=<?= (int)$cat['category_id'] ?>" class="btn btn-outline">View All <?= e($catName) ?></a>
            </div>
            <div class="product-grid">
                <?php foreach ($spotParts as $part): ?>
                    <div class="product-card">
                        <a href="/product.php?id=<?= (int)$part['part_id'] ?>" class="product-image">
                            <?php if ($part['image_url']): ?>
                                <img src="<?= e($part['image_url']) ?>" alt="<?= e($part['part_name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:48px;background:var(--bg-surface-alt);">⚙️</div>
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
                                <a href="/product.php?id=<?= (int)$part['part_id'] ?>" class="btn btn-primary btn-sm" style="padding:8px 16px;">Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
