<?php
require_once 'includes/auth.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';

if (empty($token) && !isset($_SESSION['reset_token_verified'])) {
    redirect('login.php');
}

// Check if token was just verified via OTP
if (isset($_SESSION['reset_token_verified']) && $_SESSION['reset_token_verified'] == $token) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_token = ? AND status = 'active'");
    $stmt->execute([$_SESSION['reset_email'], $token]);
    $user = $stmt->fetch();
} else {
    // Legacy support or fallback
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() AND status = 'active'");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
}

if (!$user) {
    $error = "Invalid or expired reset session. Please request a new one.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user) {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $user['id']]);
            $pdo->commit();
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_token_verified']);
            $success = "Password has been reset successfully. You can now login.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "An error occurred. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <?php if(defined('SITE_FAVICON') && SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">
    <div class="auth-container">
        <div class="auth-form-side fade-in">
            <div class="card auth-card">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <a href="index.php" style="text-decoration: none;"><h1 style="color: var(--primary-color); font-weight: 800;">F Earning</h1></a>
                    <h2 style="margin-top: 1rem;">Set New Password</h2>
                    <p style="opacity: 0.7;">Enter your new password below</p>
                </div>
                
                <?php if($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                        <?php echo $success; ?>
                    </div>
                    <div style="text-align: center;">
                        <a href="login.php" class="btn btn-primary" style="width: 100%;">Go to Login</a>
                    </div>
                <?php endif; ?>

                <?php if(!$success && $user): ?>
                <form method="POST">
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">New Password</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Reset Password</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="auth-image-side">
            <div style="max-width: 400px;">
                <img src="https://img.freepik.com/free-vector/user-verification-unauthorized-access-prevention-private-account-authentication-cyber-security-people-entering-password-safety-measures-concept-vector-isolated-concept-metaphor-illustration_335657-2213.jpg" alt="Security" style="width: 100%; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
                <h2 style="font-size: 2rem; margin-bottom: 1rem; color: white;">New Credentials</h2>
                <p style="opacity: 0.9; color: white;">Make sure to choose a strong password that you haven't used elsewhere.</p>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
