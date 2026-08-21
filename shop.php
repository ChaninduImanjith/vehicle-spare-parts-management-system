<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------
// Filters Collection & Validation
// -------------------------------------------------------------
$searchKeyword = trim($_GET['q'] ?? '');
$categoryId    = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$brandId       = filter_input(INPUT_GET, 'brand', FILTER_VALIDATE_INT);
$countryId     = filter_input(INPUT_GET, 'country', FILTER_VALIDATE_INT);
$makeId        = filter_input(INPUT_GET, 'make', FILTER_VALIDATE_INT);
$modelId       = filter_input(INPUT_GET, 'model', FILTER_VALIDATE_INT);
$sizeFilter    = trim($_GET['size'] ?? '');

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
if ($page < 1) $page = 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// -------------------------------------------------------------
// Build Query safely with PDO
// -------------------------------------------------------------
$whereClauses = ["sp.status = 'ACTIVE'"];
$params = [];

if ($searchKeyword !== '') {
    // Search part name, part number, OEM number
    $whereClauses[] = "(sp.part_name LIKE ? OR sp.part_number LIKE ? OR sp.oem_number LIKE ?)";
    $like = '%' . $searchKeyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($categoryId) {
    $whereClauses[] = "sp.category_id = ?";
    $params[] = $categoryId;
}

if ($brandId) {
    $whereClauses[] = "sp.brand_id = ?";
    $params[] = $brandId;
}

// Country filtering requires joining brand or supplier. We'll use brand's country.
if ($countryId) {
    $whereClauses[] = "b.country_id = ?";
    $params[] = $countryId;
}

if ($sizeFilter !== '') {
    $whereClauses[] = "sp.size = ?";
    $params[] = $sizeFilter;
}

// Vehicle Compatibility filtering
if ($modelId) {
    // If specific model selected
    $whereClauses[] = "sp.part_id IN (SELECT part_id FROM part_vehicle_compatibility WHERE model_id = ?)";
    $params[] = $modelId;
} elseif ($makeId) {
    // If only make selected, get parts for any model of that make
    $whereClauses[] = "sp.part_id IN (
        SELECT pvc.part_id 
        FROM part_vehicle_compatibility pvc 
        INNER JOIN vehicle_model vm ON pvc.model_id = vm.model_id 
        WHERE vm.make_id = ?
    )";
    $params[] = $makeId;
}

$whereSql = implode(' AND ', $whereClauses);

// Count total for pagination
$countSql = "SELECT COUNT(DISTINCT sp.part_id) 
             FROM spare_part sp
             INNER JOIN brand b ON sp.brand_id = b.brand_id
             WHERE $whereSql";
             
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalResults = (int)$countStmt->fetchColumn();

// Fetch products
$sql = "SELECT 
            sp.part_id, sp.part_name, sp.price, sp.image_url, sp.stock_qty,
            b.brand_name
        FROM spare_part sp
        INNER JOIN brand b ON sp.brand_id = b.brand_id
        WHERE $whereSql
        ORDER BY sp.part_id DESC
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$totalPages = ceil($totalResults / $perPage);

// -------------------------------------------------------------
// Search Logging (Only log if there's actual filter criteria)
// -------------------------------------------------------------
$hasFilters = $searchKeyword || $categoryId || $brandId || $countryId || $makeId || $modelId || $sizeFilter;
$pageIsFirst = ($page === 1);

if ($hasFilters && $pageIsFirst) {
    try {
        $logUserId = $_SESSION['user_id'] ?? null;
        $logSessionId = null;
        
        if (!$logUserId) {
            // Guest session handling
            $logSessionId = session_id();
            
            // Ensure guest exists in guest_user table to satisfy FK
            $guestStmt = $pdo->prepare('INSERT IGNORE INTO guest_user (session_id) VALUES (?)');
            $guestStmt->execute([$logSessionId]);
        }
        
        // Resolve filter names for logging
        $logCatName = null;
        if ($categoryId) {
            $catQ = $pdo->prepare('SELECT category_name FROM category WHERE category_id = ?');
            $catQ->execute([$categoryId]);
            $logCatName = $catQ->fetchColumn() ?: null;
        }
        
        $logBrandName = null;
        if ($brandId) {
            $brandQ = $pdo->prepare('SELECT brand_name FROM brand WHERE brand_id = ?');
            $brandQ->execute([$brandId]);
            $logBrandName = $brandQ->fetchColumn() ?: null;
        }
        
        $logCountryName = null;
        if ($countryId) {
            $countryQ = $pdo->prepare('SELECT country_name FROM country WHERE country_id = ?');
            $countryQ->execute([$countryId]);
            $logCountryName = $countryQ->fetchColumn() ?: null;
        }
        
        $logMakeName = null;
        if ($makeId) {
            $makeQ = $pdo->prepare('SELECT make_name FROM vehicle_make WHERE make_id = ?');
            $makeQ->execute([$makeId]);
            $logMakeName = $makeQ->fetchColumn() ?: null;
        }
        
        $logModelName = null;
        if ($modelId) {
            $modelQ = $pdo->prepare('SELECT model_name FROM vehicle_model WHERE model_id = ?');
            $modelQ->execute([$modelId]);
            $logModelName = $modelQ->fetchColumn() ?: null;
        }

        $logStmt = $pdo->prepare(
            'INSERT INTO search_log 
            (user_id, session_id, search_keyword, filter_category, filter_brand, filter_country, filter_vehicle_make, filter_vehicle_model, filter_size, results_count)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $logStmt->execute([
            $logUserId,
            $logSessionId,
            $searchKeyword ?: null,
            $logCatName,
            $logBrandName,
            $logCountryName,
            $logMakeName,
            $logModelName,
            $sizeFilter ?: null,
            $totalResults
        ]);
    } catch (Exception $e) {
        // Do not break the page if logging fails
        error_log('Search logging failed: ' . $e->getMessage());
    }
}

// -------------------------------------------------------------
// Fetch Data for Sidebar Filters
// -------------------------------------------------------------
$categories = $pdo->query("SELECT category_id, category_name FROM category ORDER BY category_name")->fetchAll();
$brands     = $pdo->query("SELECT brand_id, brand_name FROM brand ORDER BY brand_name")->fetchAll();
$countries  = $pdo->query("SELECT country_id, country_name FROM country ORDER BY country_name")->fetchAll();
$makes      = $pdo->query("SELECT make_id, make_name FROM vehicle_make ORDER BY make_name")->fetchAll();
// Fetch sizes (distinct)
$sizes = $pdo->query("SELECT DISTINCT size FROM spare_part WHERE size IS NOT NULL AND size != '' ORDER BY size")->fetchAll(PDO::FETCH_COLUMN);

// Generate query string for pagination links (preserves filters)
$queryString = $_GET;
unset($queryString['page']); // Remove page so we can append it
$baseUrl = '?' . http_build_query($queryString) . (!empty($queryString) ? '&' : '');

$pageTitle = 'Shop Spare Parts | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="shop-layout">
        
        <!-- Sidebar Filters -->
        <aside class="shop-sidebar">
            <form action="shop.php" method="GET" id="filterForm">
                
                <div class="filter-widget">
                    <h3 class="filter-title">Search</h3>
                    <div style="display:flex;">
                        <input type="text" name="q" class="form-control" value="<?= e($searchKeyword) ?>" placeholder="Keywords...">
                        <button type="submit" class="btn btn-primary" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0; padding: 0 12px;">🔍</button>
                    </div>
                </div>

                <div class="filter-widget">
                    <h3 class="filter-title">Vehicle Compatibility</h3>
                    
                    <div class="form-group">
                        <label class="form-label" style="font-size:13px;">Make</label>
                        <select name="make" id="filter_make" class="form-control">
                            <option value="">All Makes</option>
                            <?php foreach ($makes as $m): ?>
                                <option value="<?= $m['make_id'] ?>" <?= $makeId == $m['make_id'] ? 'selected' : '' ?>>
                                    <?= e($m['make_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" style="font-size:13px;">Model</label>
                        <select name="model" id="filter_model" class="form-control" data-selected="<?= e((string)$modelId) ?>">
                            <option value="">All Models</option>
                            <!-- Populated by store.js -->
                        </select>
                    </div>
                </div>

                <div class="filter-widget">
                    <h3 class="filter-title">Category</h3>
                    <div class="filter-list">
                        <label class="form-check">
                            <input type="radio" name="category" value="" onchange="this.form.submit()" <?= empty($categoryId) ? 'checked' : '' ?>>
                            <span>All Categories</span>
                        </label>
                        <?php foreach ($categories as $cat): ?>
                            <label class="form-check">
                                <input type="radio" name="category" value="<?= $cat['category_id'] ?>" onchange="this.form.submit()" <?= $categoryId == $cat['category_id'] ? 'checked' : '' ?>>
                                <span><?= e($cat['category_name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-widget">
                    <h3 class="filter-title">Brand</h3>
                    <select name="brand" class="form-control" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['brand_id'] ?>" <?= $brandId == $b['brand_id'] ? 'selected' : '' ?>>
                                <?= e($b['brand_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-widget">
                    <h3 class="filter-title">Origin Country</h3>
                    <select name="country" class="form-control" onchange="this.form.submit()">
                        <option value="">All Countries</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= $c['country_id'] ?>" <?= $countryId == $c['country_id'] ? 'selected' : '' ?>>
                                <?= e($c['country_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($sizes)): ?>
                <div class="filter-widget">
                    <h3 class="filter-title">Size</h3>
                    <select name="size" class="form-control" onchange="this.form.submit()">
                        <option value="">All Sizes</option>
                        <?php foreach ($sizes as $s): ?>
                            <option value="<?= e($s) ?>" <?= $sizeFilter === $s ? 'selected' : '' ?>>
                                <?= e($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="filter-widget" style="padding:16px;">
                    <a href="shop.php" class="btn btn-outline" style="width:100%;">Clear All Filters</a>
                </div>

            </form>
        </aside>

        <!-- Main Product Area -->
        <div class="shop-main">
            
            <div class="section-header">
                <div>
                    <h1 class="section-title">Spare Parts</h1>
                    <p class="section-desc">Showing <?= count($products) ?> of <?= $totalResults ?> results</p>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div style="text-align:center; padding: 60px 20px; background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border);">
                    <div style="font-size: 48px; margin-bottom: 16px;">🔍</div>
                    <h3 style="margin-bottom: 8px;">No parts found</h3>
                    <p style="color: var(--text-muted); margin-bottom: 24px;">Try adjusting your search or filters to find what you're looking for.</p>
                    <a href="shop.php" class="btn btn-primary">Clear Filters</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $part): ?>
                        <div class="product-card">
                            <a href="/product.php?id=<?= (int)$part['part_id'] ?>" class="product-image">
                                <?php if ($part['image_url']): ?>
                                    <img src="<?= e($part['image_url']) ?>" alt="<?= e($part['part_name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-light); font-size: 40px;">⚙️</div>
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
                                    <a href="/product.php?id=<?= (int)$part['part_id'] ?>" class="btn btn-primary btn-sm" style="padding: 8px 16px;">Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?= $baseUrl ?>page=<?= $page - 1 ?>" class="page-link">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php 
                                // Simple sliding window pagination
                                if ($totalPages > 7 && ($i < $page - 2 || $i > $page + 2) && $i !== 1 && $i !== $totalPages) {
                                    if ($i === 2 || $i === $totalPages - 1) echo '<span style="padding: 0 4px; color: var(--text-muted);">...</span>';
                                    continue;
                                }
                            ?>
                            <a href="<?= $baseUrl ?>page=<?= $i ?>" class="page-link <?= $i === $page ? 'current' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= $baseUrl ?>page=<?= $page + 1 ?>" class="page-link">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
