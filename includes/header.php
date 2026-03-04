<nav class="navbar" style="padding: 0.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; height: 70px;">
    <div class="logo">
        <a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>index.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
            <?php if(defined('SITE_LOGO') && SITE_LOGO): ?>
                <img src="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>assets/img/<?php echo SITE_LOGO; ?>" alt="Logo" style="height: 35px;">
            <?php endif; ?>
            <h1 style="font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0; white-space: nowrap;"><?php echo SITE_NAME; ?></h1>
        </a>
    </div>
    
    <div class="nav-links" style="display: flex; align-items: center; gap: 1.5rem;">
        <?php 
        $prefix = (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : '');
        $userPrefix = (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '' : 'user/');
        ?>
        
        <a href="<?php echo $prefix; ?>index.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Home</a>
        
        <?php if(isLoggedIn()): ?>
            <a href="<?php echo $userPrefix; ?>dashboard.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Dashboard</a>
            <a href="<?php echo $userPrefix; ?>ads.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Ads</a>
            <a href="<?php echo $userPrefix; ?>surveys.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Surveys</a>
            <a href="<?php echo $userPrefix; ?>upgrade.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Upgrade</a>
            <a href="<?php echo $userPrefix; ?>withdraw.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Withdraw</a>
            <a href="<?php echo $userPrefix; ?>referrals.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Referrals</a>
            <a href="<?php echo $userPrefix; ?>history.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">History</a>
            <a href="<?php echo $userPrefix; ?>transactions.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Wallet</a>
            <a href="<?php echo $userPrefix; ?>profile.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Profile</a>
            
            <button id="theme-toggle" class="btn" style="background: #f1f5f9; padding: 8px 12px; border-radius: 10px; border: 1px solid #e2e8f0; cursor: pointer;">🌓</button>
            
            <a href="<?php echo $prefix; ?>logout.php" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.6rem 1.2rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; text-decoration: none; transition: all 0.2s;">Logout</a>
        <?php else: ?>
            <a href="<?php echo $prefix; ?>leaderboard.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Leaderboard</a>
            <a href="<?php echo $prefix; ?>about.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">About</a>
            <a href="<?php echo $prefix; ?>faq.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">FAQ</a>
            <a href="<?php echo $prefix; ?>contact.php" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Contact</a>
            
            <a href="<?php echo $prefix; ?>login.php" class="btn" style="text-decoration: none; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase;">Login</a>
            <a href="<?php echo $prefix; ?>register.php" class="btn btn-primary" style="padding: 0.6rem 1.5rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; text-decoration: none;">Register</a>
        <?php endif; ?>
    </div>
    
    <div class="mobile-menu-toggle" id="mobile-menu-toggle" onclick="toggleMobileMenu()" style="display: none;">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<!-- Mobile Menu Drawer -->
<div id="mobile-menu" style="position: fixed; top: 70px; left: -100%; width: 100%; height: calc(100vh - 70px); background: #f8fafc; z-index: 9999; transition: left 0.3s ease; display: flex; flex-direction: column; padding: 2rem; gap: 1rem; border-top: 1px solid #e2e8f0;">
    <a href="<?php echo $prefix; ?>index.php" class="mobile-link">Home</a>
    <?php if(isLoggedIn()): ?>
        <a href="<?php echo $userPrefix; ?>dashboard.php" class="mobile-link">Dashboard</a>
        <a href="<?php echo $userPrefix; ?>ads.php" class="mobile-link">Ads</a>
        <a href="<?php echo $userPrefix; ?>surveys.php" class="mobile-link">Surveys</a>
        <a href="<?php echo $userPrefix; ?>upgrade.php" class="mobile-link">Upgrade</a>
        <a href="<?php echo $userPrefix; ?>withdraw.php" class="mobile-link">Withdraw</a>
        <a href="<?php echo $userPrefix; ?>referrals.php" class="mobile-link">Referrals</a>
        <a href="<?php echo $userPrefix; ?>history.php" class="mobile-link">History</a>
        <a href="<?php echo $userPrefix; ?>transactions.php" class="mobile-link">Wallet</a>
        <a href="<?php echo $userPrefix; ?>profile.php" class="mobile-link">Profile</a>
        <a href="<?php echo $prefix; ?>logout.php" class="mobile-link" style="color: #ef4444; border-top: 1px solid #e2e8f0; padding-top: 1rem;">Logout</a>
    <?php else: ?>
        <a href="<?php echo $prefix; ?>leaderboard.php" class="mobile-link">Leaderboard</a>
        <a href="<?php echo $prefix; ?>about.php" class="mobile-link">About</a>
        <a href="<?php echo $prefix; ?>faq.php" class="mobile-link">FAQ</a>
        <a href="<?php echo $prefix; ?>contact.php" class="mobile-link">Contact</a>
        <a href="<?php echo $prefix; ?>login.php" class="mobile-link">Login</a>
        <a href="<?php echo $prefix; ?>register.php" class="mobile-link" style="color: var(--primary-color); font-weight: 800;">Register</a>
    <?php endif; ?>
</div>

<style>
    .nav-links a:hover {
        color: var(--primary-color) !important;
    }
    .mobile-link {
        text-decoration: none;
        font-size: 1.1rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 0;
    }
    @media (max-width: 1100px) {
        .nav-links {
            display: none !important;
        }
        .mobile-menu-toggle {
            display: flex !important;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            background: #f1f5f9;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .mobile-menu-toggle span {
            width: 20px;
            height: 2px;
            background: #475569;
            border-radius: 2px;
        }
    }
</style>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        if (menu.style.left === '0px') {
            menu.style.left = '-100%';
        } else {
            menu.style.left = '0px';
        }
    }
</script>
