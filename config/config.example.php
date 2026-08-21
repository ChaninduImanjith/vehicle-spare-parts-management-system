<?php

declare(strict_types=1);

// ─────────────────────────────────────────────────────────────
// Database credentials — copy this file to config.local.php
// and fill in your own local values.
// NEVER commit config.local.php to version control.
// ─────────────────────────────────────────────────────────────

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'vehicle_spare_parts');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// ─────────────────────────────────────────────────────────────
// PayHere Sandbox credentials
// Get from https://sandbox.payhere.lk/merchant/settings
// ─────────────────────────────────────────────────────────────

define('PAYHERE_MERCHANT_ID',     'your_payhere_merchant_id');
define('PAYHERE_MERCHANT_SECRET', 'your_payhere_merchant_secret');
define('PAYHERE_SANDBOX',         true);   // false = production

// ─────────────────────────────────────────────────────────────
// Application base URL (no trailing slash)
// ─────────────────────────────────────────────────────────────
define('APP_URL', 'http://localhost');
