<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Username and password are required.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT admin_id, username, password_hash, full_name, is_active
             FROM admin
             WHERE username = ?
             LIMIT 1'
        );

        $stmt->execute([$username]);

        $admin = $stmt->fetch();

        if (
            $admin &&
            (int) $admin['is_active'] === 1 &&
            password_verify($password, $admin['password_hash'])
        ) {

            session_regenerate_id(true);

            $_SESSION['admin_id'] =
                (int) $admin['admin_id'];

            $_SESSION['admin_username'] =
                $admin['username'];

            $_SESSION['admin_name'] =
                $admin['full_name'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid username or password.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<div class="login-wrapper">

    <div class="login-card">

        <h1>Admin Login</h1>

        <p>Vehicle Spare Parts Management System</p>

        <?php if ($error !== ''): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label for="username">
                Username
            </label>

            <input
                type="text"
                name="username"
                id="username"
                required
            >

            <label for="password">
                Password
            </label>

            <input
                type="password"
                name="password"
                id="password"
                required
            >

            <button type="submit">
                Login
            </button>

        </form>

    </div>

</div>

</body>
</html>
