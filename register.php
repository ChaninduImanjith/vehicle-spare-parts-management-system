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
$success = '';

$username = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Username, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check uniqueness
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM registered_user WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Insert
            $hash = password_hash($password, PASSWORD_BCRYPT);
            try {
                $insert = $pdo->prepare(
                    'INSERT INTO registered_user (username, email, password_hash, phone, address) 
                     VALUES (?, ?, ?, ?, ?)'
                );
                $insert->execute([$username, $email, $hash, $phone ?: null, $address ?: null]);
                
                $success = 'Registration successful! You can now login.';
                // Clear fields
                $username = $email = $phone = $address = '';
            } catch (Exception $e) {
                error_log('Registration error: ' . $e->getMessage());
                $error = 'An error occurred during registration. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 60px 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: var(--bg-surface); padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 28px; margin-bottom: 8px;">Create an Account</h1>
            <p style="color: var(--text-muted);">Join SpareHub to order genuine parts easily.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="background: var(--danger); color: white; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="background: var(--success); color: white; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px;">
                <?= e($success) ?> <a href="/login.php" style="color: white; text-decoration: underline; font-weight: bold;">Login here</a>
            </div>
        <?php else: ?>
        
            <form action="/register.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Username *</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?= e($username) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>" required>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="password">Password *</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password *</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number (Optional)</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= e($phone) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Shipping Address (Optional)</label>
                    <textarea name="address" id="address" class="form-control" rows="3"><?= e($address) ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px; margin-top: 10px;">Register</button>
            </form>
            
            <div style="text-align: center; margin-top: 24px; color: var(--text-muted); font-size: 14px;">
                Already have an account? <a href="/login.php" style="font-weight: 600;">Log in</a>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
