<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>FAQ - <?php echo SITE_NAME; ?></title>
    <?php if(defined('SITE_FAVICON') && SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_popunder_code'] ?? ''; ?>
    <?php endif; ?>
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 120px 2rem 60px; max-width: 900px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h1 style="font-size: 3rem;">Frequently Asked <span style="color: var(--primary-color);">Questions</span></h1>
            <p style="opacity: 0.8;">Find answers to common questions about using F Earning.</p>
        </div>

        <div style="max-width: 800px; margin: 0 auto;">
            <div class="card" style="margin-bottom: 20px;">
                <h3>How do I start earning?</h3>
                <p style="opacity: 0.8; margin-top: 10px;">Simply create an account, verify your email, and head to the "Watch Ads" section in your dashboard. You'll earn rewards for every ad you watch completely.</p>
            </div>
            <div class="card" style="margin-bottom: 20px;">
                <h3>What is the minimum withdrawal limit?</h3>
                <p style="opacity: 0.8; margin-top: 10px;">The minimum withdrawal limit depends on your membership plan. For the free plan, it is typically $10.00.</p>
            </div>
            <div class="card" style="margin-bottom: 20px;">
                <h3>Which payment methods are supported?</h3>
                <p style="opacity: 0.8; margin-top: 10px;">We support Bkash, Nagad, PayPal, and many other localized methods based on your country selection.</p>
            </div>
            <div class="card" style="margin-bottom: 20px;">
                <h3>Can I have multiple accounts?</h3>
                <p style="opacity: 0.8; margin-top: 10px;">No, we strictly prohibit multiple accounts per person. Any user found with multiple accounts will be blocked.</p>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
