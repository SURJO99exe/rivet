<?php require_once 'config/config.php'; 
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $success = "Thank you for contacting us! We will get back to you soon.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
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
        <div style="display: flex; gap: 50px; align-items: flex-start; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <h1 style="font-size: 3rem; margin-bottom: 20px;">Get in <span style="color: var(--primary-color);">Touch</span></h1>
                <p style="font-size: 1.1rem; opacity: 0.8; margin-bottom: 30px;">Have questions or need support? Fill out the form below and our team will assist you as soon as possible.</p>
                <div class="card">
                    <h3>Contact Information</h3>
                    <p style="margin-top: 15px;">📧 support@f-earning.com</p>
                    <p style="margin-top: 10px;">📍 123 Earning Street, Digital City</p>
                    <p style="margin-top: 10px;">📞 +1 234 567 890</p>
                </div>
            </div>
            <div style="flex: 1; min-width: 300px;">
                <div class="card">
                    <h3>Send us a Message</h3>
                    <?php if($success): ?>
                        <div style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; margin: 1.5rem 0;">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 600;">Full Name</label>
                            <input type="text" name="name" placeholder="John Doe" required>
                        </div>
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 600;">Email Address</label>
                            <input type="email" name="email" placeholder="john@example.com" required>
                        </div>
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 600;">Subject</label>
                            <input type="text" name="subject" placeholder="Question about rewards" required>
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-weight: 600;">Message</label>
                            <textarea name="message" placeholder="How can we help you?" style="height: 120px;" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Send Message</button>
                    </form>
                </div>
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
