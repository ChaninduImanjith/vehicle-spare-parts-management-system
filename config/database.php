<?php

declare(strict_types=1);

$localConfig = __DIR__ . '/config.local.php';

if (!file_exists($localConfig)) {
    http_response_code(500);
    exit('Missing config/config.local.php. Copy config.example.php to config.local.php and set your local DB credentials.');
}

require_once $localConfig;

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);

    // Do not expose credentials or detailed DB errors to normal users.
    error_log('Database connection failed: ' . $e->getMessage());
    exit('Database connection failed.');
}
