<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = $userClass->getUserDetails($_SESSION['user_id']);
if (!$user) {
    session_destroy();
    redirect('../login.php');
}

// Get today's watched ads count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ? AND DATE(viewed_at) = CURDATE()");
$stmt->execute([$_SESSION['user_id']]);
$watched_today = $stmt->fetchColumn();

// Get total ads available
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ads WHERE status = 'active'");
$stmt->execute();
$total_ads = $stmt->fetchColumn();

// Get recent activity (ad views and survey completions)
$recent_activity_stmt = $pdo->prepare("
    (SELECT 'ad' as activity_type, a.title, v.viewed_at as activity_date, v.reward_earned as reward 
     FROM ad_views v 
     JOIN ads a ON v.ad_id = a.id 
     WHERE v.user_id = ? 
     ORDER BY v.viewed_at DESC 
     LIMIT 5)
    UNION ALL
    (SELECT 'survey' as activity_type, s.title, c.completed_at as activity_date, c.reward_earned as reward 
     FROM survey_completions c 
     JOIN surveys s ON c.survey_id = s.id 
     WHERE c.user_id = ? 
     ORDER BY c.completed_at DESC 
     LIMIT 5)
    ORDER BY activity_date DESC 
    LIMIT 10
");
$recent_activity_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$recent_activities = $recent_activity_stmt->fetchAll();

$daily_limit = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_ad_limit'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - <?php echo SITE_NAME; ?></title>
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
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 0 2rem;">
        <div style="margin-top: 40px; margin-bottom: 30px;">
            <h2 style="font-size: 2rem;">Welcome back, <span style="color: var(--primary-color);"><?php echo $user['username']; ?></span>! 👋</h2>
            <p style="opacity: 0.7;">Here's an overview of your earning performance.</p>
        </div>

        <div class="stat-grid-dashboard">
            <!-- Bonus Smartlink Card -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <a href="<?php echo htmlspecialchars($settings['ad_smartlink_url'] ?? ''); ?>" target="_blank" style="text-decoration: none; color: inherit;">
                <div class="stat-card-dashboard" style="border-top-color: #f59e0b;">
                    <span style="position: absolute; top: 10px; right: 10px; background: #f59e0b; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 700;">HOT</span>
                    <span class="icon-wrap">🚀</span>
                    <span class="label">Bonus Link</span>
                    <span class="value">Earn Extra</span>
                    <span class="sub-label" style="color: #f59e0b;">Click to boost earnings</span>
                </div>
            </a>
            <?php endif; ?>

            <div class="stat-card-dashboard" style="border-top-color: var(--primary-color);">
                <span class="icon-wrap">💰</span>
                <span class="label">Main Balance</span>
                <span class="value">$<?php echo number_format($user['balance'], 4); ?></span>
                <span class="sub-label" style="color: var(--secondary-color);">Ready to withdraw</span>
            </div>

            <div class="stat-card-dashboard" style="border-top-color: var(--secondary-color);">
                <span class="icon-wrap">📈</span>
                <span class="label">Total Earned</span>
                <span class="value">$<?php echo number_format($user['total_earned'], 4); ?></span>
                <span class="sub-label" style="opacity: 0.7;">Lifetime earnings</span>
            </div>

            <div class="stat-card-dashboard" style="border-top-color: #f59e0b;">
                <span class="icon-wrap">📺</span>
                <span class="label">Daily Progress</span>
                <div style="display: flex; align-items: baseline; gap: 5px;">
                    <span class="value"><?php echo $watched_today; ?></span>
                    <span style="opacity: 0.5; font-weight: 600;">/ <?php echo $daily_limit; ?></span>
                </div>
                <div style="width: 100%; height: 6px; background: rgba(0,0,0,0.05); border-radius: 3px; margin-top: 15px; overflow: hidden;">
                    <div style="width: <?php echo $daily_limit > 0 ? min(100, ($watched_today / $daily_limit) * 100) : 0; ?>%; height: 100%; background: #f59e0b;"></div>
                </div>
            </div>

            <div class="stat-card-dashboard" style="border-top-color: #ec4899;">
                <span class="icon-wrap">🤝</span>
                <span class="label">Referrals</span>
                <?php
                $stmt = $pdo->prepare("SELECT SUM(amount) FROM referral_commissions WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $ref_earned = $stmt->fetchColumn() ?: 0;
                ?>
                <span class="value">$<?php echo number_format($ref_earned, 4); ?></span>
                <span class="sub-label" style="opacity: 0.7;">From your network</span>
            </div>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 40px;">
            <div class="card slide-up">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-size: 1.4rem;">Recent Activity</h3>
                    <a href="ads.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Watch More Ads</a>
                </div>
                <!-- Adsterra 728x90 Banner -->
                <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
                <div style="margin-bottom: 25px; display: flex; justify-content: center;">
                    <?php echo $settings['ad_banner_728_90_code'] ?? ''; ?>
                </div>
                <?php endif; ?>
                <!-- Adsterra Banner Ad Unit -->
                <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
                <div style="margin-bottom: 20px; text-align: center;">
                    <?php echo $settings['ad_banner_468_60_code'] ?? ''; ?>
                </div>
                <?php endif; ?>
                <?php if (empty($recent_activities)): ?>
                <div style="background: rgba(0,0,0,0.02); padding: 40px; border-radius: 1rem; text-align: center; border: 2px dashed var(--border-light);">
                    <img src="https://cdn-icons-png.flaticon.com/512/2645/2645897.png" style="width: 80px; opacity: 0.5; margin-bottom: 15px;">
                    <p style="opacity: 0.6;">Your recent ad views and earnings will appear here.</p>
                </div>
                <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: rgba(0,0,0,0.02); border-radius: 12px; border: 1px solid var(--border-light);">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: <?php echo $activity['activity_type'] == 'ad' ? 'rgba(99, 102, 241, 0.1)' : 'rgba(16, 185, 129, 0.1)'; ?>; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                <?php echo $activity['activity_type'] == 'ad' ? '📺' : '📋'; ?>
                            </div>
                            <div>
                                <h4 style="font-size: 0.95rem; margin-bottom: 2px;"><?php echo htmlspecialchars($activity['title']); ?></h4>
                                <p style="font-size: 0.8rem; opacity: 0.6;"><?php echo date('M d, Y h:i A', strtotime($activity['activity_date'])); ?></p>
                            </div>
                        </div>
                        <div style="font-weight: 700; color: var(--secondary-color); font-size: 1rem;">
                            +$<?php echo number_format($activity['reward'], 4); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="card slide-up" style="transition-delay: 0.1s;">
                <h3 style="font-size: 1.4rem; margin-bottom: 20px;">Referral Link</h3>
                <p style="font-size: 0.9rem; opacity: 0.7; margin-bottom: 15px;">Invite your friends and earn <?php echo $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'referral_commission'")->fetchColumn(); ?>% commission on their earnings!</p>
                <div style="position: relative;">
                    <input type="text" id="ref-link" value="<?php echo "http://" . $_SERVER['HTTP_HOST'] . "/ads/register.php?ref=" . $user['referral_code']; ?>" readonly style="padding-right: 80px; font-size: 0.85rem; background: rgba(0,0,0,0.03);">
                    <button onclick="copyRef()" class="btn btn-primary" style="position: absolute; right: 4px; top: 10px; padding: 0.4rem 0.8rem; font-size: 0.75rem;">Copy</button>
                </div>
                
                <!-- Adsterra 300x250 Banner -->
                <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
                <div style="margin-top: 30px; display: flex; justify-content: center;">
                    <?php echo $settings['ad_banner_300_250_code'] ?? ''; ?>
                </div>
                <?php endif; ?>

                <script>
                function copyRef() {
                    var copyText = document.getElementById("ref-link");
                    copyText.select();
                    document.execCommand("copy");
                    alert("Referral link copied!");
                }
                </script>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
