<?php
/**
 * Admin sidebar navigation.
 * Included by admin/includes/header.php
 */

// Derive the current page path for active-link highlighting
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
?>

<aside class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-brand">

        <div class="brand-icon">⚙</div>

        <div>
            <h2>SpareHub</h2>
            <span>Admin Panel</span>
        </div>

    </div>

    <nav class="sidebar-nav" aria-label="Admin navigation">

        <div class="nav-group">
            <span class="nav-group-label">Overview</span>
            <a href="/admin/dashboard.php"
               class="nav-link <?= str_contains($currentPath, 'dashboard') ? 'active' : '' ?>">
                <span class="nav-icon">◈</span> Dashboard
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-group-label">Catalogue</span>
            <a href="/admin/products/index.php"
               class="nav-link <?= str_contains($currentPath, '/products/') ? 'active' : '' ?>">
                <span class="nav-icon">⊞</span> Products
            </a>
            <a href="/admin/categories/index.php"
               class="nav-link <?= str_contains($currentPath, '/categories/') ? 'active' : '' ?>">
                <span class="nav-icon">≡</span> Categories
            </a>
            <a href="/admin/brands/index.php"
               class="nav-link <?= str_contains($currentPath, '/brands/') ? 'active' : '' ?>">
                <span class="nav-icon">◎</span> Brands
            </a>
            <a href="/admin/countries/index.php"
               class="nav-link <?= str_contains($currentPath, '/countries/') ? 'active' : '' ?>">
                <span class="nav-icon">⊕</span> Countries
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-group-label">Vehicles</span>
            <a href="/admin/vehicles/index.php"
               class="nav-link <?= str_contains($currentPath, '/vehicles/') ? 'active' : '' ?>">
                <span class="nav-icon">⬡</span> Vehicle Management
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-group-label">Supply Chain</span>
            <a href="/admin/suppliers/index.php"
               class="nav-link <?= str_contains($currentPath, '/suppliers/') ? 'active' : '' ?>">
                <span class="nav-icon">⊗</span> Suppliers
            </a>
            <a href="/admin/inventory/index.php"
               class="nav-link <?= str_contains($currentPath, '/inventory/') ? 'active' : '' ?>">
                <span class="nav-icon">⊟</span> Inventory
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-group-label">Commerce</span>
            <a href="/admin/orders/index.php"
               class="nav-link <?= str_contains($currentPath, '/orders/') ? 'active' : '' ?>">
                <span class="nav-icon">⊙</span> Orders
            </a>
            <a href="/admin/payments/index.php"
               class="nav-link <?= str_contains($currentPath, '/payments/') ? 'active' : '' ?>">
                <span class="nav-icon">◫</span> Payments
            </a>
            <a href="/admin/customers/index.php"
               class="nav-link <?= str_contains($currentPath, '/customers/') ? 'active' : '' ?>">
                <span class="nav-icon">⊛</span> Customers
            </a>
            <a href="/admin/product-requests/index.php"
               class="nav-link <?= str_contains($currentPath, '/product-requests/') ? 'active' : '' ?>">
                <span class="nav-icon">◉</span> Product Requests
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-group-label">Analytics</span>
            <a href="/admin/reports/index.php"
               class="nav-link <?= str_contains($currentPath, '/reports/') ? 'active' : '' ?>">
                <span class="nav-icon">⊡</span> Reports
            </a>
        </div>

        <div class="nav-group nav-bottom">
            <a href="/admin/logout.php" class="nav-link nav-link-danger">
                <span class="nav-icon">⊘</span> Logout
            </a>
        </div>

    </nav>

</aside>

<!-- Mobile overlay backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>
