<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 120px 2rem 60px; max-width: 900px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 style="font-size: 3rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Help Center</h1>
            <p style="color: #64748b; font-size: 1.2rem;">Everything you need to know about using <?php echo SITE_NAME; ?>.</p>
        </div>

        <div class="grid" style="display: grid; gap: 30px;">
            <div class="card" style="padding: 30px;">
                <h3 style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-color);">Getting Started</h3>
                <div style="display: flex; flex-direction: column; gap: 15px; color: #475569; line-height: 1.6;">
                    <p><strong>1. How do I start earning?</strong><br>Simply watch available ads or complete surveys from your dashboard. Rewards are credited instantly after verification.</p>
                    <p><strong>2. How long do tasks take?</strong><br>Ads typically take 15-35 seconds, while premium surveys require 3-5 minutes of engagement.</p>
                </div>
            </div>

            <div class="card" style="padding: 30px;">
                <h3 style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-color);">Payments & Withdrawals</h3>
                <div style="display: flex; flex-direction: column; gap: 15px; color: #475569; line-height: 1.6;">
                    <p><strong>1. What is the minimum withdrawal?</strong><br>The minimum amount you can withdraw is $<?php echo $settings['min_withdrawal'] ?? '10.00'; ?>.</p>
                    <p><strong>2. Which payment methods are supported?</strong><br>We support localized methods based on your country, including Bkash, PayPal, Crypto, and more.</p>
                </div>
            </div>

            <div class="card" style="padding: 30px;">
                <h3 style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-color);">Account & Security</h3>
                <div style="display: flex; flex-direction: column; gap: 15px; color: #475569; line-height: 1.6;">
                    <p><strong>1. Can I have multiple accounts?</strong><br>No, we only allow one account per person/IP to ensure platform integrity.</p>
                    <p><strong>2. Why is my task locked?</strong><br>Tasks may be locked if you've reached your daily limit. Upgrade your plan to increase your capacity.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
