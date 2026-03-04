<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

// Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$pending_withdrawals = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn();
$total_ads = $pdo->query("SELECT COUNT(*) FROM ads")->fetchColumn();
$total_earned = $pdo->query("SELECT SUM(reward_earned) FROM ad_views")->fetchColumn() ?: 0;

// New Stats for enhanced dashboard
$new_users_today = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND is_admin = 0")->fetchColumn();
$active_ads = $pdo->query("SELECT COUNT(*) FROM ads WHERE status = 'active'")->fetchColumn();
$total_payouts = $pdo->query("SELECT SUM(amount) FROM withdrawals WHERE status = 'approved'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
    <script src="https://pl28841577.effectivegatecpm.com/84/e0/1c/84e01cf268ab48a5873ceeff192728f1.js"></script>
    <?php endif; ?>
</head>
<body class="light">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="logo">
                <a href="../index.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px; padding: 0 1rem; margin-bottom: 2rem;">
                    <?php if(SITE_LOGO): ?>
                        <img src="../assets/img/<?php echo SITE_LOGO; ?>" alt="Logo" style="max-height: 30px;">
                    <?php endif; ?>
                    <h2 style="margin-bottom: 0; padding: 0;"><?php echo SITE_NAME; ?></h2>
                </a>
            </div>
            <nav class="admin-nav" id="nav-links">
                <a href="dashboard.php" class="admin-nav-item">
                    <span>📊</span> Dashboard
                </a>
                <a href="users.php" class="admin-nav-item">
                    <span>👥</span> Users
                </a>
                <a href="ads.php" class="admin-nav-item">
                    <span>📺</span> Ads Management
                </a>
                <a href="manage_ads.php" class="admin-nav-item">
                    <span>⚙️</span> Manage Adsterra
                </a>
                <a href="withdrawals.php" class="admin-nav-item">
                    <span>💰</span> Withdrawals
                </a>
                <a href="surveys.php" class="admin-nav-item">
                    <span>📝</span> Surveys
                </a>
                <a href="reports.php" class="admin-nav-item">
                    <span>📈</span> Reports
                </a>
                <a href="settings.php" class="admin-nav-item">
                    <span>⚙️</span> Settings
                </a>
            </nav>
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div style="margin-top: auto;">
                <a href="../logout.php" class="admin-nav-item" style="color: #ef4444;">
                    <span>🚪</span> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Dashboard Overview</h1>
                    <p style="opacity: 0.7;">Welcome back to the admin control center.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button id="theme-toggle" class="btn">🌓</button>
                    <div style="text-align: right;">
                        <p style="font-weight: 700;"><?php echo $_SESSION['username']; ?></p>
                        <p style="font-size: 0.8rem; opacity: 0.6;">Administrator</p>
                    </div>
                </div>
            </header>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; align-items: stretch;">
        <div class="card stat-card" style="border-bottom: 4px solid var(--primary-color); display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; padding: 1.25rem;">
            <span class="label" style="font-size: 0.75rem; font-weight: 700; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px;">Total Users</span>
            <span class="value" style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;"><?php echo $total_users; ?></span>
            <p style="font-size: 0.8rem; color: var(--secondary-color); margin: 0; font-weight: 600;">+<?php echo $new_users_today; ?> today</p>
        </div>
        <div class="card stat-card" style="border-bottom: 4px solid #f59e0b; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; padding: 1.25rem;">
            <span class="label" style="font-size: 0.75rem; font-weight: 700; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px;">Pending Withdrawals</span>
            <span class="value" style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;"><?php echo $pending_withdrawals; ?></span>
            <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">Needs approval</p>
        </div>
        <div class="card stat-card" style="border-bottom: 4px solid var(--secondary-color); display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; padding: 1.25rem;">
            <span class="label" style="font-size: 0.75rem; font-weight: 700; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px;">Total Payouts</span>
            <span class="value" style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;">$<?php echo number_format($total_payouts, 2); ?></span>
            <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">Approved withdrawals</p>
        </div>
        <div class="card stat-card" style="border-bottom: 4px solid #ef4444; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; padding: 1.25rem;">
            <span class="label" style="font-size: 0.75rem; font-weight: 700; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px;">Active Ads</span>
            <span class="value" style="font-size: 1.75rem; font-weight: 800; margin: 0.5rem 0;"><?php echo $active_ads; ?></span>
            <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">Running currently</p>
        </div>
    </div>

            <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 1.5rem;">
                <!-- Recent Activity -->
                <div class="card" style="display: flex; flex-direction: column;">
                    <h3 style="margin-bottom: 1.5rem;">Recent User Registrations</h3>
                    <?php
                    $recent_users = $pdo->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT 5")->fetchAll();
                    ?>
                    <div style="flex: 1;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                                    <th style="padding: 10px;">User</th>
                                    <th style="padding: 10px;">Email</th>
                                    <th style="padding: 10px;">Joined</th>
                                    <th style="padding: 10px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_users as $u): ?>
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 12px 10px; font-weight: 600;"><?php echo $u['username']; ?></td>
                                    <td style="padding: 12px 10px;"><?php echo $u['email']; ?></td>
                                    <td style="padding: 12px 10px; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td style="padding: 12px 10px;">
                                        <span style="font-size: 0.75rem; padding: 3px 8px; border-radius: 10px; background: <?php echo $u['status'] == 'active' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $u['status'] == 'active' ? 'var(--secondary-color)' : '#ef4444'; ?>;">
                                            <?php echo strtoupper($u['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="users.php" style="display: block; text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--primary-color); text-decoration: none; font-weight: 600;">View All Users</a>
                </div>

                <!-- System Info -->
                <div class="card" style="display: flex; flex-direction: column;">
                    <h3 style="margin-bottom: 1.5rem;">System Overview</h3>
                    <div style="display: flex; flex-direction: column; gap: 1.25rem; flex: 1; justify-content: center;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="opacity: 0.7; font-size: 0.9rem;">PHP Version</span>
                            <span style="font-weight: 600; background: rgba(0,0,0,0.03); padding: 4px 10px; border-radius: 6px;"><?php echo phpversion(); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="opacity: 0.7; font-size: 0.9rem;">Server Software</span>
                            <span style="font-weight: 600; text-align: right; font-size: 0.85rem; max-width: 180px;"><?php echo explode(' ', $_SERVER['SERVER_SOFTWARE'])[0]; ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="opacity: 0.7; font-size: 0.9rem;">Database Engine</span>
                            <span style="font-weight: 600;">MySQL (PDO)</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--border-light); padding-top: 1.25rem; margin-top: 0.5rem;">
                            <span style="opacity: 0.7; font-size: 0.9rem; font-weight: 700;">Total Platform Rewards</span>
                            <span style="font-weight: 800; color: var(--secondary-color); font-size: 1.1rem;">$<?php echo number_format($total_earned, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
    <script src="https://pl28841591.effectivegatecpm.com/f7/86/88/f78688a1d1b5f7dfc12912e9ebd056eb.js"></script>
    <?php endif; ?>
</body>
</html>
