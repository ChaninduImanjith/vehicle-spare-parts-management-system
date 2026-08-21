<?php
/**
 * Admin panel reusable HTML header.
 * Included by every admin page.
 * Requires $pageTitle to be set before inclusion.
 */

$pageTitle = $pageTitle ?? 'Admin Panel';

require_once __DIR__ . '/../../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — Vehicle Spare Parts Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body>

<!-- Mobile sidebar toggle -->
<button
    class="sidebar-toggle"
    id="sidebarToggle"
    onclick="toggleSidebar()"
    aria-label="Toggle navigation"
>&#9776;</button>

<div class="admin-layout">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="admin-content">

