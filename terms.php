<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 120px 2rem 60px; max-width: 800px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: #1e293b; margin-bottom: 30px;">Terms of Service</h1>
        
        <div class="card" style="padding: 40px; color: #475569; line-height: 1.8;">
            <section style="margin-bottom: 30px;">
                <h3 style="color: #1e293b; margin-bottom: 15px;">1. User Agreement</h3>
                <p>By using <?php echo SITE_NAME; ?>, you agree to comply with our rules. One account per person is allowed. Use of VPNs, bots, or any form of cheating will result in a permanent ban.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h3 style="color: #1e293b; margin-bottom: 15px;">2. Earning & Rewards</h3>
                <p>Rewards are credited based on successful task completion. We reserve the right to withhold payments if fraudulent activity is suspected.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h3 style="color: #1e293b; margin-bottom: 15px;">3. Account Termination</h3>
                <p>We may terminate or suspend your account at any time, without prior notice, for conduct that we believe violates these Terms or is harmful to other users or our business interests.</p>
            </section>

            <section>
                <h3 style="color: #1e293b; margin-bottom: 15px;">4. Limitation of Liability</h3>
                <p><?php echo SITE_NAME; ?> is provided "as is". We are not responsible for any financial losses or technical issues encountered while using the platform.</p>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
