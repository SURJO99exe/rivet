<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = $userClass->getUserDetails($_SESSION['user_id']);

// Safety check: if user session exists but record is missing (e.g. after DB reset)
if (!$user) {
    session_destroy();
    redirect('../login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Update email
    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
    if ($stmt->execute([$email, $_SESSION['user_id']])) {
        $success = "Profile updated successfully!";
        // Update password if provided
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['user_id']]);
            $success = "Profile and password updated successfully!";
        }
        $user = $userClass->getUserDetails($_SESSION['user_id']);
    } else {
        $error = "Update failed. Email might be in use.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile Settings - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_popunder_code'] ?? ''; ?>
    <?php endif; ?>
</head>
<body class="light">
    <header class="navbar-fixed">
        <nav class="navbar" style="padding-left: 2rem; padding-right: 2rem;">
            <div class="logo">
                <a href="../index.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                    <?php if(SITE_LOGO): ?>
                        <img src="../assets/img/<?php echo SITE_LOGO; ?>" alt="Logo" style="max-height: 40px;">
                    <?php endif; ?>
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>
            </div>
            <div class="nav-links" id="nav-links">
                <a href="../index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="ads.php">Ads</a>
                <a href="surveys.php">Surveys</a>
                <a href="upgrade.php">Upgrade</a>
                <a href="withdraw.php">Withdraw</a>
                <a href="referrals.php">Referrals</a>
                <a href="history.php">History</a>
                <a href="transactions.php">Wallet</a>
                <a href="profile.php">Profile</a>
                <button id="theme-toggle" class="btn">🌓</button>
                <a href="../logout.php" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.5rem 1rem;">Logout</a>
            </div>
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <main class="fade-in" style="padding: 0 2rem; margin-bottom: 50px;">
        <div style="margin-top: 40px; margin-bottom: 30px; text-align: center;">
            <h2 style="font-size: 2.5rem;">Account Settings</h2>
            <p style="opacity: 0.7;">Manage your personal information and security.</p>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; align-items: start;">
            <!-- Profile Update Form -->
            <div class="card shadow-sm" style="height: 100%;">
                <h3 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.5rem;">👤</span> Personal Information
                </h3>
                <?php if($error): ?><div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 15px; font-size: 0.9rem;"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 15px; font-size: 0.9rem;"><?php echo $success; ?></div><?php endif; ?>
                
                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 8px;">Username</label>
                        <div style="position: relative;">
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background: rgba(0,0,0,0.05); color: #666; cursor: not-allowed; padding-left: 40px;">
                            <span style="position: absolute; left: 15px; top: 12px; opacity: 0.5;">🔒</span>
                        </div>
                        <small style="opacity: 0.6; display: block; margin-top: 5px;">Username cannot be changed for security reasons.</small>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 8px;">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="your@email.com">
                    </div>
                    <div style="margin-bottom: 25px;">
                        <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 8px;">Update Password <small style="font-weight: 400; opacity: 0.6;">(leave blank to keep current)</small></label>
                        <input type="password" name="password" placeholder="New Password">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: 700;">Save Changes</button>
                </form>
            </div>

            <!-- Account Statistics & Referral -->
            <div style="display: flex; flex-direction: column; gap: 30px; height: 100%;">

                <div class="card shadow-sm" style="border-top: 4px solid var(--primary-color);">
                    <h3 style="margin-bottom: 20px; font-size: 1.25rem;">Account Overview</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border-light);">
                            <span style="opacity: 0.7;">Account ID</span>
                            <span style="font-weight: 700;">#<?php echo str_pad($user['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border-light);">
                            <span style="opacity: 0.7;">Current Balance</span>
                            <span style="font-weight: 700; color: var(--secondary-color);">$<?php echo number_format($user['balance'], 4); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border-light);">
                            <span style="opacity: 0.7;">Total Earned</span>
                            <span style="font-weight: 700;">$<?php echo number_format($user['total_earned'], 4); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="opacity: 0.7;">Member Since</span>
                            <span style="font-weight: 700;"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm" style="background: linear-gradient(135deg, var(--primary-color), #4f46e5); color: white; padding: 2rem;">
                    <h3 style="margin-bottom: 15px; color: white; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">🔗</span> Referral Status
                    </h3>
                    <p style="font-size: 0.95rem; margin-bottom: 25px; opacity: 0.9; line-height: 1.5;">Share your code and earn commissions from your friends' activity!</p>
                    <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 0.75rem; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                        <div>
                            <p style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.8; margin-bottom: 8px;">Your Unique Code</p>
                            <span style="font-size: 1.4rem; font-weight: 800; letter-spacing: 2px; font-family: monospace;"><?php echo $user['referral_code']; ?></span>
                        </div>
                        <button onclick="copyRef()" class="btn" style="background: white; color: var(--primary-color); border: none; padding: 12px 20px; border-radius: 0.5rem; font-weight: 700; cursor: pointer; white-space: nowrap; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">Copy Link</button>
                    </div>
                    <script>
                    function copyRef() {
                        const link = "<?php echo "http://" . $_SERVER['HTTP_HOST'] . "/ads/register.php?ref=" . $user['referral_code']; ?>";
                        const temp = document.createElement('input');
                        document.body.appendChild(temp);
                        temp.value = link;
                        temp.select();
                        document.execCommand('copy');
                        document.body.removeChild(temp);
                        alert('Referral link copied to clipboard!');
                    }
                    </script>
                </div>
            </div>
        </div>
    </main>
    <script src="../assets/js/main.js"></script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
