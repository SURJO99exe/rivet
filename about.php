<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
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

    <main class="fade-in" style="padding: 120px 2rem 60px; max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; max-width: 800px; margin: 0 auto;">
            <h1 style="font-size: 3rem; margin-bottom: 20px;">About <span style="color: var(--primary-color);">F Earning</span></h1>
            <p style="font-size: 1.2rem; opacity: 0.8; line-height: 1.6;">
                F Earning is a leading micro-earning platform dedicated to providing users with simple ways to earn money while helping advertisers reach a global audience. Our mission is to create a transparent and rewarding ecosystem for everyone.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 60px;">
            <div class="card">
                <h3>Our Vision</h3>
                <p style="opacity: 0.8; margin-top: 10px;">To become the most trusted global platform for micro-tasks and digital advertising, empowering millions of users to achieve financial flexibility.</p>
            </div>
            <div class="card">
                <h3>Our Mission</h3>
                <p style="opacity: 0.8; margin-top: 10px;">We strive to bridge the gap between users looking for easy earning opportunities and businesses seeking high-quality engagement.</p>
            </div>
            <div class="card">
                <h3>Why Choose Us?</h3>
                <p style="opacity: 0.8; margin-top: 10px;">With instant rewards, secure payments, and a supportive community, F Earning offers the best experience in the industry.</p>
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
