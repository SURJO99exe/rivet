<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    }
    redirect('user/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($username) && !empty($password)) {
        if ($auth->login($username, $password)) {
            if (isAdmin()) {
                redirect('admin/dashboard.php');
            }
            redirect('user/dashboard.php');
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_popunder_code'] ?? ''; ?>
    <?php endif; ?>
</head>
<body class="light">
    <div class="auth-container">
        <div class="auth-form-side fade-in">
            <div class="card auth-card" style="background: white; padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <a href="index.php" style="text-decoration: none;"><h1 style="color: var(--primary-color); font-weight: 800;">F Earning</h1></a>
                    <h2 style="margin-top: 1rem;">Welcome Back</h2>
                    <p style="opacity: 0.7;">Login to access your dashboard</p>
                </div>
                
                <?php if($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Username or Email</label>
                        <input type="text" name="username" placeholder="Enter your username" required>
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <label style="font-weight: 600; font-size: 0.9rem;">Password</label>
                            <a href="#" style="font-size: 0.8rem; color: var(--primary-color); text-decoration: none;">Forgot password?</a>
                        </div>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Sign In</button>
                </form>
                
                <p style="text-align: center; margin-top: 2rem; font-size: 0.95rem; opacity: 0.8;">
                    New to F Earning? <a href="register.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Create an account</a>
                </p>
            </div>
        </div>
        <div class="auth-image-side">
            <div style="max-width: 400px;">
                <img src="https://img.freepik.com/free-vector/user-verification-unauthorized-access-prevention-private-account-authentication-cyber-security-people-entering-password-safety-measures-concept-vector-isolated-concept-metaphor-illustration_335657-2213.jpg" alt="Security" style="width: 100%; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
                <h2 style="font-size: 2rem; margin-bottom: 1rem; color: white;">Secure Micro-Earning</h2>
                <p style="opacity: 0.9; color: white;">Your security is our priority. We use industry-standard encryption to protect your data and earnings.</p>
            </div>
        </div>
        <div style="text-align: center; padding: 20px 0; opacity: 0.6; font-size: 0.85rem; border-top: 1px solid var(--border-light); margin-top: 40px;">
            <!-- Adsterra 320x50 Banner -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <div style="margin-bottom: 15px; display: flex; justify-content: center;">
                <?php echo $settings['ad_banner_320_50_code'] ?? ''; ?>
            </div>
            <?php endif; ?>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
