<?php
require_once 'includes/auth.php';
require_once 'includes/mail_functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';

    if (empty($email)) {
        $error = "Please enter your email address";
    } else {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $stmt->execute([$otp, $expiry, $user['id']]);

            require_once 'includes/mail_functions.php';
            require_once 'includes/email_templates.php';
            $subject = "Reset Your Password - " . SITE_NAME;
            $message = "We received a request to reset your password. Use the 6-digit code below to proceed with the reset process:";
            $body = getEmailTemplate("Password Reset", $user['username'], $otp, $message);

            if (sendMail($email, $subject, $body)) {
                $_SESSION['reset_email'] = $email;
                redirect('verify_otp.php?type=reset');
            } else {
                $error = "Failed to send reset email. Please contact support.";
            }
        } else {
            $error = "No active account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
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
                    <h2 style="margin-top: 1rem;">Reset Password</h2>
                    <p style="opacity: 0.7;">Enter your email to receive reset instructions</p>
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
                <?php endif; ?>

                <form method="POST">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Email Address</label>
                        <input type="email" name="email" placeholder="enter@your-email.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Send Reset Link</button>
                </form>
                
                <p style="text-align: center; margin-top: 2rem; font-size: 0.95rem; opacity: 0.8;">
                    Remember your password? <a href="login.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Sign in</a>
                </p>
            </div>
        </div>
        <div class="auth-image-side">
            <div style="max-width: 400px;">
                <img src="https://img.freepik.com/free-vector/forgot-password-concept-illustration_114360-1123.jpg" alt="Security" style="width: 100%; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
                <h2 style="font-size: 2rem; margin-bottom: 1rem; color: white;">Account Recovery</h2>
                <p style="opacity: 0.9; color: white;">Don't worry, it happens to the best of us. We'll help you get back into your account safely.</p>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
