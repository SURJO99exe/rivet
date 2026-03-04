<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$stmt = $pdo->prepare("
    SELECT 
        u.username, 
        u.created_at, 
        COALESCE(SUM(rc.amount), 0) as total_commission 
    FROM users u 
    LEFT JOIN referral_commissions rc ON u.id = rc.referred_user_id AND rc.user_id = :user_id
    WHERE u.referred_by = :user_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$referrals = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Referrals - <?php echo SITE_NAME; ?></title>
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
                <a href="dashboard.php">Dashboard</a>
                <a href="ads.php">Watch Ads</a>
                <a href="withdraw.php">Withdraw</a>
                <a href="referrals.php">Referrals</a>
                <a href="history.php">History</a>
                <a href="profile.php">Profile</a>
                <button id="theme-toggle" class="btn">🌓</button>
                <a href="../logout.php" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">Logout</a>
            </div>
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <main class="fade-in" style="padding: 0 2rem;">
        <div style="margin-top: 40px; text-align: center;">
            <h2 style="font-size: 2.5rem;">Referral Network</h2>
            <p style="opacity: 0.7;">View users who joined using your link and the commissions you've earned.</p>
        </div>

        <div class="card" style="margin-top: 30px;">
            <div class="table-container">
                <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                        <th style="padding: 15px 10px;">USERNAME</th>
                        <th style="padding: 15px 10px;">JOINED DATE</th>
                        <th style="padding: 15px 10px;">COMMISSION EARNED</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($referrals as $ref): ?>
                        <tr style="border-bottom: 1px solid var(--border-light); font-size: 0.95rem;">
                            <td style="padding: 15px 10px; font-weight: 600;"><?php echo $ref['username']; ?></td>
                            <td style="padding: 15px 10px;"><?php echo date('M d, Y', strtotime($ref['created_at'])); ?></td>
                            <td style="padding: 15px 10px; color: var(--secondary-color); font-weight: 700;">+$<?php echo number_format($ref['total_commission'], 4); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($referrals) == 0): ?>
                        <tr>
                            <td colspan="3" style="padding: 40px; text-align: center; opacity: 0.5;">
                                You haven't referred anyone yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="../assets/js/main.js"></script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
