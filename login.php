<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    redirect('/profile.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, username, password_hash, is_verified FROM registered_user WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to intended page or profile
            $redirect = $_SESSION['redirect_after_login'] ?? '/profile.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Login | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 80px 20px;">
    <div style="max-width: 400px; margin: 0 auto; background: var(--bg-surface); padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="width: 60px; height: 60px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
                👤
            </div>
            <h1 style="font-size: 28px; margin-bottom: 8px;">Welcome Back</h1>
            <p style="color: var(--text-muted);">Please log in to your account.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="background: var(--danger); color: white; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>
        
        <form action="/login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="<?= e($username) ?>" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 24px; color: var(--text-muted); font-size: 14px;">
            Don't have an account? <a href="/register.php" style="font-weight: 600;">Sign up</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
