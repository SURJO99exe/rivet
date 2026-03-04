<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$stmt = $pdo->prepare("SELECT av.*, a.title FROM ad_views av JOIN ads a ON av.ad_id = a.id WHERE av.user_id = ? ORDER BY av.viewed_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Earning History - <?php echo SITE_NAME; ?></title>
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
            <h2 style="font-size: 2.5rem;">Earning History</h2>
            <p style="opacity: 0.7;">A complete log of all your ad views and rewards.</p>
        </div>

        <div class="card" style="margin-top: 30px;">
            <div class="table-container">
                <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                        <th style="padding: 15px 10px;">AD TITLE</th>
                        <th style="padding: 15px 10px;">WATCHED AT</th>
                        <th style="padding: 15px 10px;">REWARD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($history as $row): ?>
                        <tr style="border-bottom: 1px solid var(--border-light); font-size: 0.95rem;">
                            <td style="padding: 15px 10px; font-weight: 600;"><?php echo $row['title']; ?></td>
                            <td style="padding: 15px 10px;"><?php echo date('M d, Y H:i', strtotime($row['viewed_at'])); ?></td>
                            <td style="padding: 15px 10px; color: var(--secondary-color); font-weight: 700;">+$<?php echo number_format($row['reward_earned'], 4); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($history) == 0): ?>
                        <tr>
                            <td colspan="3" style="padding: 40px; text-align: center; opacity: 0.5;">
                                No earning history found.
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
