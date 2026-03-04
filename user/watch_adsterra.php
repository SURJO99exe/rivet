<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

// Logic for Adsterra Direct Link or Pop-under
// Typically, Adsterra rewards are based on CPM/CPC, 
// but we can create a timed transition page to simulate our internal reward system.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Partner Ad - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="light">
    <header class="navbar-fixed">
        <nav class="container navbar">
            <div class="logo">
                <a href="../index.php">
                    <?php if(SITE_LOGO): ?>
                        <img src="../assets/img/<?php echo SITE_LOGO; ?>" alt="Logo">
                    <?php endif; ?>
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>
            </div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="ads.php">Ads</a>
                <a href="surveys.php">Surveys</a>
                <a href="upgrade.php">Upgrade</a>
                <a href="withdraw.php">Withdraw</a>
                <a href="referrals.php">Referrals</a>
                <a href="history.php">History</a>
                <a href="transactions.php">Wallet</a>
                <a href="profile.php">Profile</a>
                <button id="theme-toggle" class="btn">🌓</button>
                <a href="../logout.php" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.5rem 1rem;">Logout</a>
            </div>
        </nav>
    </header>
    <div class="container fade-in" style="max-width: 800px; margin-top: 100px; text-align: center;">
        <div class="card" style="padding: 40px; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
            
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 10px;">Partner Advertisement</h2>
            <p style="opacity: 0.7; margin-bottom: 40px; font-size: 1.1rem;">You are about to watch an ad from our premium partner.<br>Stay on the page for <span style="color: var(--primary-color); font-weight: 700;">15 seconds</span> to earn your reward.</p>
            
            <!-- Enhanced Timer Cooldown -->
            <div style="position: relative; width: 120px; height: 120px; margin: 0 auto 40px; display: flex; align-items: center; justify-content: center;">
                <svg width="120" height="120" style="transform: rotate(-90deg); position: absolute; top: 0; left: 0;">
                    <circle cx="60" cy="60" r="54" stroke="rgba(0,0,0,0.05)" stroke-width="8" fill="transparent" />
                    <circle id="progress-circle" cx="60" cy="60" r="54" stroke="var(--primary-color)" stroke-width="8" fill="transparent" 
                        stroke-dasharray="339.292" stroke-dashoffset="0" style="transition: stroke-dashoffset 1s linear; stroke-linecap: round;" />
                </svg>
                <div id="ad-timer" style="font-size: 2.5rem; font-weight: 800; color: #111;">15</div>
            </div>
            
            <div style="margin-top: 30px;">
                <!-- Adsterra Social Bar Ad Unit -->
                <script type='text/javascript' src='//pl25914434.highrevenuenetwork.com/74/ec/7a/74ec7af0089809cb083f848d41cc9628.js'></script>
                <div style="background: rgba(0,0,0,0.02); min-height: 250px; border-radius: 1.5rem; display: flex; align-items: center; justify-content: center; border: 2px dashed rgba(0,0,0,0.1); padding: 20px;">
                    <div id="container-74ec7af0089809cb083f848d41cc9628">
                        <div style="opacity: 0.5; font-size: 0.9rem;">
                            <span style="display: block; font-size: 2rem; margin-bottom: 10px;">📺</span>
                            Partner Ad Loading...
                        </div>
                    </div>
                </div>
            </div>

            <div id="reward-section" style="display: none; margin-top: 30px;" class="slide-up">
                <button id="claim-btn" class="btn btn-secondary" style="padding: 1.2rem 4rem; font-size: 1.1rem; font-weight: 700; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);">
                    Claim Partner Reward ✨
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        const initialTime = 15;
        const circle = document.getElementById('progress-circle');
        const radius = circle.r.baseVal.value;
        const circumference = 2 * Math.PI * radius;
        
        circle.style.strokeDasharray = `${circumference} ${circumference}`;
        
        function setProgress(percent) {
            const offset = circumference - (percent / 100 * circumference);
            circle.style.strokeDashoffset = offset;
        }

        document.getElementById('claim-btn').addEventListener('click', () => {
            claimReward();
        });

        function claimReward() {
            const btn = document.getElementById('claim-btn');
            btn.disabled = true;
            btn.innerText = 'Processing...';

            fetch('../api/claim_reward.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ad_id=partner_adsterra'
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.href = 'dashboard.php?success=reward_claimed';
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerText = 'Claim Partner Reward ✨';
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerText = 'Claim Partner Reward ✨';
            });
        }

        // Custom timer logic with visual progress
        let timeLeft = initialTime;
        const timerText = document.getElementById('ad-timer');
        
        const timerId = setInterval(() => {
            timeLeft--;
            timerText.innerText = timeLeft;
            
            const percent = ((initialTime - timeLeft) / initialTime) * 100;
            setProgress(percent);
            
            if (timeLeft <= 0) {
                clearInterval(timerId);
                document.getElementById('reward-section').style.display = 'block';
                setTimeout(claimReward, 1000);
            }
        }, 1000);
    </script>
</body>
</html>
