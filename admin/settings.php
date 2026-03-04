<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([sanitize($value), $key]);
    }
    
    // Handle File Uploads (Logo & Favicon)
    $upload_dir = "../assets/img/";
    if (!empty($_FILES['site_logo']['name'])) {
        $logo_name = "logo_" . time() . "_" . $_FILES['site_logo']['name'];
        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_dir . $logo_name)) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_logo'");
            $stmt->execute([$logo_name]);
        }
    }
    if (!empty($_FILES['site_favicon']['name'])) {
        $favicon_name = "fav_" . time() . "_" . $_FILES['site_favicon']['name'];
        if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], $upload_dir . $favicon_name)) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_favicon'");
            $stmt->execute([$favicon_name]);
        }
    }

    $success = "Settings updated successfully!";
}

$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - F Earning Admin</title>
    <?php if(!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo $settings['site_favicon']; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="light">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h2>F Earning</h2>
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
                <a href="reports.php" class="admin-nav-item">
                    <span>📈</span> Reports
                </a>
                <a href="settings.php" class="admin-nav-item active">
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
                    <h1 style="font-size: 1.75rem;">Site Settings</h1>
                    <p style="opacity: 0.7;">Configure platform-wide earning rates and limits.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button id="theme-toggle" class="btn">🌓</button>
                </div>
            </header>

            <div class="card" style="margin-top: 20px;">
                <?php if(isset($success)): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h3>General Settings</h3>
                            <div style="margin: 20px 0;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Site Name</label>
                                <input type="text" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Contact Email</label>
                                <input type="email" name="settings[contact_email]" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required>
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Payment Methods (Comma separated)</label>
                                <input type="text" name="settings[payment_methods]" value="<?php echo htmlspecialchars($settings['payment_methods'] ?? ''); ?>" placeholder="Bkash,Nagad,PayPal" required>
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Referral Commission (%)</label>
                                <input type="number" name="settings[referral_commission]" value="<?php echo htmlspecialchars($settings['referral_commission'] ?? '10'); ?>" required>
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Minimum Withdrawal ($)</label>
                                <input type="number" step="0.01" name="settings[min_withdrawal]" value="<?php echo htmlspecialchars($settings['min_withdrawal'] ?? '10.00'); ?>" required>
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Adsterra API Key</label>
                                <input type="text" name="settings[adsterra_api_key]" value="<?php echo htmlspecialchars($settings['adsterra_api_key'] ?? ''); ?>">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Global Ads Control</label>
                                <select name="settings[ads_enabled]" style="width: 100%; padding: 0.5rem; border-radius: 0.5rem; border: 1px solid var(--border-light); background: var(--card-bg); color: var(--text-color);">
                                    <option value="1" <?php echo ($settings['ads_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>Ads On (Enabled)</option>
                                    <option value="0" <?php echo ($settings['ads_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>Ads Off (Disabled)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <h3>Appearance & Social</h3>
                            <div style="margin: 20px 0;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Site Logo</label>
                                <?php if(!empty($settings['site_logo'])): ?>
                                    <img src="../assets/img/<?php echo $settings['site_logo']; ?>" style="max-height: 50px; display: block; margin-bottom: 10px;">
                                <?php endif; ?>
                                <input type="file" name="site_logo" accept="image/*">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Favicon</label>
                                <?php if(!empty($settings['site_favicon'])): ?>
                                    <img src="../assets/img/<?php echo $settings['site_favicon']; ?>" style="max-height: 32px; display: block; margin-bottom: 10px;">
                                <?php endif; ?>
                                <input type="file" name="site_favicon" accept="image/x-icon,image/png">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Facebook URL</label>
                                <input type="text" name="settings[social_facebook]" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Twitter URL</label>
                                <input type="text" name="settings[social_twitter]" value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 5px;">Telegram URL</label>
                                <input type="text" name="settings[social_telegram]" value="<?php echo htmlspecialchars($settings['social_telegram'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="save_settings" class="btn btn-primary" style="width: 100%; padding: 1rem; margin-top: 20px;">Save All Changes</button>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
