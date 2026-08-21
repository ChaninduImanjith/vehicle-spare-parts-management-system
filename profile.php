<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireUser(); // Ensure user is logged in
$userId = (int)$_SESSION['user_id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Check if email belongs to someone else
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM registered_user WHERE email = ? AND user_id != ?');
        $checkStmt->execute([$email, $userId]);
        
        if ($checkStmt->fetchColumn() > 0) {
            $error = 'This email is already in use by another account.';
        } else {
            try {
                $updStmt = $pdo->prepare('UPDATE registered_user SET email = ?, phone = ?, address = ? WHERE user_id = ?');
                $updStmt->execute([$email, $phone ?: null, $address ?: null, $userId]);
                $success = 'Profile updated successfully.';
            } catch (Exception $e) {
                error_log('Profile update error: ' . $e->getMessage());
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Fetch current user data
$stmt = $pdo->prepare('SELECT username, email, phone, address, registered_at FROM registered_user WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    // Edge case if user was deleted but session exists
    session_destroy();
    redirect('/login.php');
}

$pageTitle = 'My Profile | SpareHub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 32px; margin-bottom: 8px;">My Profile</h1>
            <p style="color: var(--text-muted);">Manage your account details and shipping information.</p>
        </div>
        <a href="/my-orders.php" class="btn btn-secondary">View My Orders</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px;">
        
        <!-- Sidebar Profile Summary -->
        <div>
            <div style="background: var(--bg-surface); padding: 30px; border-radius: var(--radius-lg); border: 1px solid var(--border); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 16px;">
                    👤
                </div>
                <h2 style="font-size: 20px; margin-bottom: 4px;"><?= e($user['username']) ?></h2>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Member since <?= date('M Y', strtotime($user['registered_at'])) ?></p>
                
                <ul style="text-align: left; border-top: 1px solid var(--border); padding-top: 20px;">
                    <li style="margin-bottom: 12px;"><a href="/profile.php" style="color: var(--primary); font-weight: 500;">Account Settings</a></li>
                    <li style="margin-bottom: 12px;"><a href="/my-orders.php" style="color: var(--text-main);">My Orders</a></li>
                    <li style="margin-bottom: 12px;"><a href="/request-part.php" style="color: var(--text-main);">Request a Part</a></li>
                    <li><a href="/logout.php" style="color: var(--danger);">Logout</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Form -->
        <div style="background: var(--bg-surface); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border);">
            <h3 style="font-size: 20px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">Update Details</h3>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="background: var(--success); color: white; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: var(--danger); color: white; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <form action="/profile.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled style="background: var(--bg-surface-alt); cursor: not-allowed;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">Username cannot be changed.</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= e($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Default Shipping Address</label>
                    <textarea name="address" id="address" class="form-control" rows="4"><?= e($user['address'] ?? '') ?></textarea>
                </div>
                
                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
        
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
