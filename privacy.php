<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?php echo SITE_NAME; ?></title>
    <?php if(defined('SITE_FAVICON') && SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 120px 2rem 60px; max-width: 800px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: #1e293b; margin-bottom: 30px;">Privacy Policy</h1>
        
        <div class="card" style="padding: 40px; color: #475569; line-height: 1.8;">
            <section style="margin-bottom: 30px;">
                <h3 style="color: #1e293b; margin-bottom: 15px;">1. Information We Collect</h3>
                <p>We collect information you provide directly to us when you create an account, such as your username, email address, and country. We also collect data regarding your activity on the platform (ads watched, surveys completed).</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h3 style="color: #1e293b; margin-bottom: 15px;">2. How We Use Your Information</h3>
                <p>Your information is used to manage your account, process rewards, detect fraudulent activity, and improve our services. We do not sell your personal data to third parties.</p>
            </section>

            <section style="margin-bottom: 30px;">
                <h3 style="color: #1e293b; margin-bottom: 15px;">3. Data Security</h3>
                <p>We implement industry-standard security measures to protect your information. However, no method of transmission over the internet is 100% secure.</p>
            </section>

            <section>
                <h3 style="color: #1e293b; margin-bottom: 15px;">4. Cookies</h3>
                <p>We use cookies to maintain your session and remember your preferences. You can disable cookies in your browser settings, but some features may not function correctly.</p>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
