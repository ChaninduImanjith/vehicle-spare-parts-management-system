<?php
/**
 * Admin Creation Script
 * =====================
 * Run from command line:
 *   php scripts/create-admin.php
 *
 * Do NOT run this through a browser.
 */

declare(strict_types=1);

// Safety: only allow CLI execution
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

// Load database connection
require_once __DIR__ . '/../config/database.php';

echo "\n=== Vehicle Spare Parts Management System ===\n";
echo "          Admin Account Creation Tool\n\n";

// Collect admin details interactively
$username  = prompt('Enter admin username : ');
$email     = prompt('Enter admin email    : ');
$fullName  = prompt('Enter full name      : ');
$password  = promptHidden('Enter password       : ');
$confirm   = promptHidden('Confirm password     : ');

echo "\n";

// Validate
$errors = [];

if (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
}

if (strlen($fullName) < 2) {
    $errors[] = 'Full name must be at least 2 characters.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}

if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    echo "Validation failed:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
    exit(1);
}

// Check for duplicates
$checkStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM admin WHERE username = ? OR email = ?'
);
$checkStmt->execute([$username, $email]);

if ((int) $checkStmt->fetchColumn() > 0) {
    echo "Error: An admin with that username or email already exists.\n";
    exit(1);
}

// Hash password and insert
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$insertStmt = $pdo->prepare(
    'INSERT INTO admin (username, password_hash, email, full_name, is_active)
     VALUES (?, ?, ?, ?, TRUE)'
);

$insertStmt->execute([$username, $hash, $email, $fullName]);

$adminId = $pdo->lastInsertId();

echo "✓ Admin account created successfully.\n";
echo "  ID       : $adminId\n";
echo "  Username : $username\n";
echo "  Email    : $email\n";
echo "  Name     : $fullName\n\n";

// ─────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────

function prompt(string $label): string
{
    echo $label;
    $value = trim(fgets(STDIN));
    return $value;
}

function promptHidden(string $label): string
{
    // On Linux/Mac we can hide input with stty
    if (DIRECTORY_SEPARATOR === '/') {
        echo $label;
        system('stty -echo');
        $value = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $value;
    }

    // Fallback for Windows
    return prompt($label);
}
