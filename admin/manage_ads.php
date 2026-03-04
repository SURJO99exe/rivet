<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_ads'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    $success = "Ad management settings updated successfully!";
}

$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Ads - Admin Dashboard</title>
    <?php if(!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo $settings['site_favicon']; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .ad-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-top: 20px; }
        .ad-item { background: var(--card-bg-light); padding: 20px; border-radius: 1rem; border: 1px solid var(--border-light); }
        body.dark .ad-item { background: var(--card-bg-dark); border-color: var(--border-dark); }
        .ad-item h3 { margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        textarea { font-family: monospace; font-size: 0.85rem; height: 120px; }
    </style>
</head>
<body class="light">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="logo">
                <a href="../index.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px; padding: 0 1rem; margin-bottom: 2rem;">
                    <h2><?php echo SITE_NAME; ?></h2>
                </a>
            </div>
            <nav class="admin-nav" id="nav-links">
                <a href="dashboard.php" class="admin-nav-item"><span>📊</span> Dashboard</a>
                <a href="users.php" class="admin-nav-item"><span>👥</span> Users</a>
                <a href="ads.php" class="admin-nav-item"><span>📺</span> Ads Management</a>
                <a href="manage_ads.php" class="admin-nav-item active"><span>⚙️</span> Manage Adsterra</a>
                <a href="withdrawals.php" class="admin-nav-item"><span>💰</span> Withdrawals</a>
                <a href="settings.php" class="admin-nav-item"><span>⚙️</span> Site Settings</a>
            </nav>
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Manage Adsterra Units</h1>
                    <p style="opacity: 0.7;">Manage scripts, links, and toggle all ad units platform-wide.</p>
                </div>
                <button id="theme-toggle" class="btn">🌓</button>
            </header>

            <?php if($success): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; margin-top: 20px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="margin-top: 30px;">
                <div class="card" style="margin-bottom: 20px; border-left: 5px solid var(--primary-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="margin: 0;">Global Ads Master Toggle</h3>
                            <p style="margin: 5px 0 0; opacity: 0.7; font-size: 0.9rem;">Turn all platform ads ON or OFF instantly.</p>
                        </div>
                        <select name="settings[ads_enabled]" style="width: 200px;">
                            <option value="1" <?php echo ($settings['ads_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>Ads Enabled (ON)</option>
                            <option value="0" <?php echo ($settings['ads_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>Ads Disabled (OFF)</option>
                        </select>
                    </div>
                </div>

                <div class="ad-grid">
                    <!-- Popunder -->
                    <div class="ad-item">
                        <h3>Popunder Script</h3>
                        <textarea name="settings[ad_popunder_code]"><?php echo htmlspecialchars($settings['ad_popunder_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- Social Bar -->
                    <div class="ad-item">
                        <h3>Social Bar Script</h3>
                        <textarea name="settings[ad_social_bar_code]"><?php echo htmlspecialchars($settings['ad_social_bar_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- Smartlink -->
                    <div class="ad-item">
                        <h3>Smartlink URL</h3>
                        <input type="text" name="settings[ad_smartlink_url]" value="<?php echo htmlspecialchars($settings['ad_smartlink_url'] ?? ''); ?>">
                        <p style="margin-top: 10px; font-size: 0.8rem; opacity: 0.6;">Used for "Bonus Earning" buttons.</p>
                    </div>

                    <!-- Native -->
                    <div class="ad-item">
                        <h3>Native Banner Code</h3>
                        <textarea name="settings[ad_native_code]"><?php echo htmlspecialchars($settings['ad_native_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- 468x60 -->
                    <div class="ad-item">
                        <h3>Banner 468x60 Code</h3>
                        <textarea name="settings[ad_banner_468_60_code]"><?php echo htmlspecialchars($settings['ad_banner_468_60_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- 728x90 -->
                    <div class="ad-item">
                        <h3>Banner 728x90 Code</h3>
                        <textarea name="settings[ad_banner_728_90_code]"><?php echo htmlspecialchars($settings['ad_banner_728_90_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- 300x250 -->
                    <div class="ad-item">
                        <h3>Banner 300x250 Code</h3>
                        <textarea name="settings[ad_banner_300_250_code]"><?php echo htmlspecialchars($settings['ad_banner_300_250_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- 160x300 -->
                    <div class="ad-item">
                        <h3>Banner 160x300 Code</h3>
                        <textarea name="settings[ad_banner_160_300_code]"><?php echo htmlspecialchars($settings['ad_banner_160_300_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- 160x600 -->
                    <div class="ad-item">
                        <h3>Banner 160x600 Code</h3>
                        <textarea name="settings[ad_banner_160_600_code]"><?php echo htmlspecialchars($settings['ad_banner_160_600_code'] ?? ''); ?></textarea>
                    </div>

                    <!-- 320x50 -->
                    <div class="ad-item">
                        <h3>Banner 320x50 Code</h3>
                        <textarea name="settings[ad_banner_320_50_code]"><?php echo htmlspecialchars($settings['ad_banner_320_50_code'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" name="save_ads" class="btn btn-primary" style="width: 100%; padding: 1.25rem; margin-top: 30px; font-size: 1.1rem;">Save All Ad Configurations</button>
            </form>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
