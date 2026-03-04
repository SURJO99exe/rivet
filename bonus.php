<?php
require_once __DIR__ . '/includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get current user's rank for display
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_total_ads = $stmt->fetchColumn();
$is_eligible = ($user_total_ads >= 500);

if ($is_eligible) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 
        FROM users 
        WHERE total_earned > (SELECT total_earned FROM users WHERE id = ?) 
        AND is_admin = 0 
        AND (SELECT COUNT(*) FROM ad_views WHERE user_id = users.id) >= 500
    ");
    $stmt->execute([$user_id]);
    $user_rank = (int)$stmt->fetchColumn();
} else {
    $user_rank = 0; // Not ranked
}

// Bonus Structure
$bonuses = [
    1 => 10.00,
    2 => 9.00,
    3 => 8.00,
    4 => 7.00,
    5 => 6.00,
    6 => 5.00,
    7 => 4.00,
    8 => 3.00,
    9 => 2.00,
    10 => 1.00
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daily Bonuses - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">
    <header class="navbar-fixed">
        <nav class="navbar" style="padding-left: 2rem; padding-right: 2rem;">
            <div class="logo">
                <a href="index.php">
                    <?php if(SITE_LOGO): ?>
                        <img src="assets/img/<?php echo SITE_LOGO; ?>" alt="Logo">
                    <?php endif; ?>
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>
            </div>
            <div class="nav-links" id="nav-links">
                <a href="index.php">Home</a>
                <a href="user/dashboard.php">Dashboard</a>
                <a href="user/ads.php">Ads</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="bonus.php" style="font-weight: bold; border-bottom: 2px solid var(--secondary-color);">Bonuses</a>
                <a href="user/upgrade.php">Upgrade</a>
                <a href="logout.php" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.5rem 1rem;">Logout</a>
            </div>
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <main class="fade-in" style="padding: 0 2rem; margin-top: 2rem; max-width: 1000px; margin-left: auto; margin-right: auto;">
        <div style="margin-top: 40px; margin-bottom: 30px; text-align: center;">
            <h2 style="font-size: 2.5rem;">🎁 Daily Ranking Bonuses</h2>
            <p style="opacity: 0.7;">Top performers receive extra rewards every day at midnight!</p>
            
            <div style="margin-top: 20px; display: inline-flex; align-items: center; gap: 10px; background: <?php echo $is_eligible ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; padding: 10px 20px; border-radius: 50px;">
                <span style="font-size: 1.2rem;"><?php echo $is_eligible ? '✨' : '🔒'; ?></span>
                <span style="font-weight: 600; color: <?php echo $is_eligible ? 'var(--secondary-color)' : '#ef4444'; ?>;">
                    <?php echo $is_eligible ? 'Your Current Rank: #' . $user_rank : 'Not Yet Eligible (Need 500 Ads)'; ?>
                </span>
            </div>
        </div>

        <div class="stat-grid-dashboard" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <?php foreach($bonuses as $rank => $amount): ?>
                <?php 
                $color = '#94a3b8';
                $emoji = '👤';
                if($rank == 1) { $color = '#fbbf24'; $emoji = '🥇'; }
                elseif($rank == 2) { $color = '#94a3b8'; $emoji = '🥈'; }
                elseif($rank == 3) { $color = '#b45309'; $emoji = '🥉'; }
                ?>
                <div class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 20px; border-left: 5px solid <?php echo $color; ?>; position: relative; overflow: hidden;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 2rem;"><?php echo $emoji; ?></span>
                        <div>
                            <h4 style="margin: 0; font-size: 1.2rem;">Rank #<?php echo $rank; ?></h4>
                            <p style="margin: 5px 0 0; opacity: 0.6; font-size: 0.8rem;">Daily Top Performer</p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: block; font-size: 1.5rem; font-weight: 800; color: var(--secondary-color);">$<?php echo number_format($amount, 2); ?></span>
                        <span style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700; opacity: 0.5;">Bonus Reward</span>
                    </div>
                    <?php if($user_rank == $rank): ?>
                        <div style="position: absolute; top: 0; right: 0; background: var(--secondary-color); color: white; padding: 2px 10px; font-size: 0.6rem; font-weight: 800; border-bottom-left-radius: 8px;">YOU</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card" style="padding: 30px; background: #f8fafc; border: 1px dashed #cbd5e1; text-align: center;">
            <h3 style="margin-bottom: 15px;">How it works?</h3>
            <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px; opacity: 0.8;">
                <li>✅ Reach the top 10 on the global <a href="leaderboard.php" style="color: var(--primary-color); font-weight: 700;">Leaderboard</a>.</li>
                <li>✅ Maintain your position until the <strong>Daily Reset Cooldown</strong> finishes.</li>
                <li>✅ Bonuses are automatically added to your <strong>Balance</strong> every 24 hours.</li>
                <li>✅ You must have at least <strong>500 ads watched</strong> to qualify for ranking.</li>
            </ul>
            <a href="user/ads.php" class="btn btn-primary" style="margin-top: 25px;">Start Watching Now</a>
        </div>

        <!-- Native Ad Bottom -->
        <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <div style="margin-top: 40px; text-align: center;">
            <?php echo $settings['ad_native_code'] ?? ''; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer style="padding: 40px 2rem; text-align: center; opacity: 0.6; font-size: 0.9rem;">
        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
