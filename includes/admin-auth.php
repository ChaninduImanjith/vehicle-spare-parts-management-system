<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function adminId(): ?int
{
    return isset($_SESSION['admin_id'])
        ? (int) $_SESSION['admin_id']
        : null;
}
