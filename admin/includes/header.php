<?php

$pageTitle = $pageTitle ?? 'Admin Panel';

require_once __DIR__ . '/../../includes/functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e($pageTitle) ?> |
        Vehicle Spare Parts Management System
    </title>

    <link
        rel="stylesheet"
        href="/assets/css/admin.css"
    >

</head>

<body>

<div class="admin-layout">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="admin-content">
