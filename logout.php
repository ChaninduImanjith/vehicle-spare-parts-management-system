<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear user session variables
unset($_SESSION['user_id']);
unset($_SESSION['username']);

// Optionally, completely destroy the session, but preserving cart might be desired.
// Here we'll destroy it entirely for security.
session_destroy();

require_once __DIR__ . '/includes/functions.php';
redirect('/index.php');
