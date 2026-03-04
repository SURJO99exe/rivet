<footer class="footer" style="background: #f8fafc; padding: 60px 2rem 30px; margin-top: 60px; border-top: 1px solid #e2e8f0; position: relative; z-index: 9999;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; position: relative; z-index: 10000;">
        <!-- Brand Section -->
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <?php if(defined('SITE_LOGO') && SITE_LOGO): ?>
                    <img src="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>assets/img/<?php echo SITE_LOGO; ?>" alt="Logo" style="height: 40px;">
                <?php else: ?>
                    <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem;">F</div>
                <?php endif; ?>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b;"><?php echo SITE_NAME; ?></h2>
            </div>
            <p style="color: #64748b; line-height: 1.6; font-size: 0.95rem; max-width: 300px;">
                The most trusted platform for micro-earning through video ads and surveys. Join us and start earning today.
            </p>
        </div>

        <!-- Quick Links -->
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">Quick Links</h3>
            <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 15px;">
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>index.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: color 0.2s; display: inline-block; position: relative; z-index: 10001; pointer-events: auto;">Home</a></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>about.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: color 0.2s; display: inline-block; position: relative; z-index: 10001; pointer-events: auto;">About Us</a></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>faq.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: color 0.2s; display: inline-block; position: relative; z-index: 10001; pointer-events: auto;">FAQ</a></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>contact.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: color 0.2s; display: inline-block; position: relative; z-index: 10001; pointer-events: auto;">Contact</a></li>
            </ul>
        </div>

        <!-- Support -->
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">Support</h3>
            <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 15px;">
                <li style="color: #64748b; font-size: 0.95rem;">Email: <?php echo $settings['contact_email'] ?? 'support@f-earning.com'; ?></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>help.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: color 0.2s; display: inline-block; position: relative; z-index: 10001; pointer-events: auto;">Help Center</a></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>terms.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: color 0.2s; display: inline-block; position: relative; z-index: 10001; pointer-events: auto;">Terms of Service</a></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>privacy.php" style="color: #64748b; text-decoration: none; font-size: 0.95rem; transition: color 0.2s; display: inline-block; position: relative; z-index: 10001; pointer-events: auto;">Privacy Policy</a></li>
            </ul>
        </div>

        <!-- Follow Us -->
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">Follow Us</h3>
            <div style="display: flex; gap: 15px;">
                <!-- Social links can be added here if available in settings -->
                <p style="color: #64748b; font-size: 0.9rem; font-style: italic;">Stay connected with us on social media.</p>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div style="max-width: 1200px; margin: 40px auto 0; padding-top: 30px; border-top: 1px solid #e2e8f0; text-align: center;">
        <p style="color: #94a3b8; font-size: 0.9rem;">
            &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
        </p>
    </div>
</footer>

<?php include __DIR__ . '/activity_popup.php'; ?>

<style>
    .footer a:hover {
        color: var(--primary-color) !important;
    }
</style>
