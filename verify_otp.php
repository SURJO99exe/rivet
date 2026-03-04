<?php
require_once 'config/config.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'register';
$email = '';

if ($type == 'register') {
    $email = $_SESSION['verify_email'] ?? '';
} else {
    $email = $_SESSION['reset_email'] ?? '';
}

if (empty($email)) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = isset($_POST['otp']) ? sanitize($_POST['otp']) : '';

    if (empty($otp)) {
        $error = "Please enter the 6-digit code";
    } else {
        if ($type == 'register') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()");
            $stmt->execute([$email, $otp]);
            $user = $stmt->fetch();

            if ($user) {
                $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);
                $_SESSION['user_id'] = $user['id'];
                unset($_SESSION['verify_email']);
                redirect('user/dashboard.php?success=verified');
            } else {
                $error = "Invalid or expired verification code.";
            }
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_token = ? AND reset_token_expiry > NOW()");
            $stmt->execute([$email, $otp]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['reset_token_verified'] = $otp;
                redirect('reset_password.php?token=' . $otp);
            } else {
                $error = "Invalid or expired reset code.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Verify OTP - <?php echo SITE_NAME; ?></title>
    <?php if(defined('SITE_FAVICON') && SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .otp-input-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }
        .otp-input {
            width: 100%;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            letter-spacing: 5px;
        }
    </style>
</head>
<body class="light">
    <div class="auth-container">
        <div class="auth-form-side fade-in">
            <div class="card auth-card">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <a href="index.php" style="text-decoration: none;"><h1 style="color: var(--primary-color); font-weight: 800;">F Earning</h1></a>
                    <h2 style="margin-top: 1rem;">Verification Required</h2>
                    <p style="opacity: 0.7;">We sent a 6-digit code to <strong><?php echo htmlspecialchars($email); ?></strong></p>
                </div>
                
                <?php if($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; text-align: center; font-weight: 600; font-size: 0.9rem; margin-bottom: 10px;">Enter 6-Digit Code</label>
                        <input type="text" name="otp" class="otp-input" maxlength="6" placeholder="000000" pattern="\d{6}" required autocomplete="one-time-code">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Verify Code</button>
                </form>
                
                <p style="text-align: center; margin-top: 2rem; font-size: 0.95rem; opacity: 0.8;">
                    Didn't receive the code? <a href="#" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Resend Code</a>
                </p>
            </div>
        </div>
        <div class="auth-image-side">
            <div style="max-width: 400px;">
                <img src="https://img.freepik.com/free-vector/security-otp-concept-illustration_114360-7911.jpg" alt="OTP" style="width: 100%; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
                <h2 style="font-size: 2rem; margin-bottom: 1rem; color: white;">Two-Step Verification</h2>
                <p style="opacity: 0.9; color: white;">Your security is our priority. A verification code adds an extra layer of protection to your account.</p>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
