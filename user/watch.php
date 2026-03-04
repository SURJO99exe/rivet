<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND status = 'active'");
$stmt->execute([$ad_id]);
$ad = $stmt->fetch();

if (!$ad) {
    redirect('ads.php');
}

// Check if already watched (Permanent lifetime restriction)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ? AND ad_id = ?");
$stmt->execute([$_SESSION['user_id'], $ad_id]);
if ($stmt->fetchColumn() > 0) {
    redirect('../error.php?type=warning&msg=' . urlencode('You have already completed this task. Each ad can only be watched once per account.'));
}

// Get user membership info and daily limit
$stmt = $pdo->prepare("SELECT m.daily_ads, m.ad_reward FROM memberships m JOIN users u ON u.membership_id = m.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_membership = $stmt->fetch();
$daily_limit = $user_membership['daily_ads'] ?? 10;
$user_reward = $user_membership['ad_reward'] ?? 0.0100;

// Check how many ads watched today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ? AND DATE(viewed_at) = CURDATE()");
$stmt->execute([$_SESSION['user_id']]);
$watched_today = $stmt->fetchColumn();

if ($watched_today >= $daily_limit) {
    redirect('../error.php?type=error&msg=' . urlencode('Daily limit reached. Please upgrade your plan to watch more ads.'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Watching Ad - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_popunder_code'] ?? ''; ?>
    <?php endif; ?>
</head>
<body class="light">
    <div class="container fade-in" style="max-width: 900px; margin-top: 20px; text-align: center; padding: 0 15px;">
        <!-- Top Banner Ad -->
        <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <div style="margin-bottom: 15px; min-height: 50px; overflow: hidden;">
            <?php echo $settings['ad_banner_728_90_code'] ?? ''; ?>
        </div>
        <?php endif; ?>

        <div class="card" style="padding: 20px; border-radius: 1.5rem;">
            <div style="display: flex; flex-direction: column; gap: 10px; align-items: center; margin-bottom: 25px;">
                <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                    <a href="ads.php" style="text-decoration: none; color: inherit; opacity: 0.7; font-size: 0.9rem;">← Back</a>
                    <div style="background: rgba(99, 102, 241, 0.1); padding: 5px 12px; border-radius: 20px; font-weight: 700; color: var(--primary-color); font-size: 0.85rem;">
                        Reward: $<?php echo number_format($user_reward, 4); ?>
                    </div>
                </div>
                <h2 style="font-size: 1.25rem; margin-top: 5px;"><?php echo htmlspecialchars($ad['title']); ?></h2>
            </div>

            <div style="position: relative; background: #000; border-radius: 1rem; overflow: hidden; aspect-ratio: 16/9; box-shadow: 0 15px 30px -5px rgba(0,0,0,0.3); width: 100%;">
                <?php if(filter_var($ad['video_url'], FILTER_VALIDATE_URL)): ?>
                    <a href="<?php echo $ad['video_url']; ?>" target="_blank" id="ad-link-wrap" style="text-decoration: none; cursor: pointer;">
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #111;">
                            <div style="text-align: center; color: white;">
                                <span style="font-size: 4rem; display: block; margin-bottom: 15px;">🔗</span>
                                <h3>Click to Visit Ad Link</h3>
                                <p style="opacity: 0.7;">The link will also open automatically when the timer finishes.</p>
                            </div>
                        </div>
                    </a>
                <?php else: ?>
                    <iframe width="100%" height="100%" src="https://www.youtube.com/embed/<?php echo $ad['video_url']; ?>?autoplay=1&controls=0&disablekb=1&modestbranding=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                <?php endif; ?>
                
                <div id="overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.7); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 10; color: white; pointer-events: none; backdrop-filter: blur(4px);">
                    <div style="position: relative; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <svg style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: rotate(-90deg);">
                            <circle cx="50" cy="50" r="44" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="6" />
                            <circle id="timer-progress" cx="50" cy="50" r="44" fill="none" stroke="var(--primary-color)" stroke-width="6" stroke-dasharray="276.46" stroke-dashoffset="0" stroke-linecap="round" style="transition: stroke-dashoffset 1s linear;" />
                        </svg>
                        <div id="ad-timer" style="font-size: 2rem; font-weight: 800; font-family: 'Courier New', Courier, monospace; color: white; text-shadow: 0 0 15px var(--primary-color); animation: glow 1s infinite alternate;"><?php echo $ad['duration']; ?></div>
                    </div>
                    <p style="font-weight: 700; letter-spacing: 1px; text-transform: uppercase; font-size: 0.8rem; color: rgba(255,255,255,0.8);">Watching Ad...</p>
                    
                    <?php if(filter_var($ad['video_url'], FILTER_VALIDATE_URL)): ?>
                    <a href="<?php echo $ad['video_url']; ?>" target="_blank" onclick="markAdClicked()" style="margin-top: 20px; pointer-events: auto; text-decoration: none;">
                        <button id="ad-click-btn" class="btn btn-primary" style="padding: 10px 25px; border-radius: 50px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);">
                            <span>🔗</span> Click Here to Visit
                        </button>
                    </a>
                    <?php endif; ?>
                </div>

                <style>
                    @keyframes glow {
                        from { text-shadow: 0 0 5px var(--primary-color), 0 0 10px var(--primary-color); }
                        to { text-shadow: 0 0 15px var(--primary-color), 0 0 25px var(--primary-color); }
                    }
                </style>
            </div>

            <div id="reward-section" style="display: none; margin-top: 30px;" class="slide-up">
                <div style="background: rgba(16, 185, 129, 0.1); padding: 20px; border-radius: 1rem; margin-bottom: 20px;">
                    <h3 style="color: var(--secondary-color);">Ad Complete! 🎉</h3>
                    <p style="opacity: 0.8;">You can now claim your reward of $<?php echo number_format($user_reward, 4); ?></p>
                </div>
                <button id="claim-btn" class="btn btn-secondary" style="padding: 1rem 3rem; font-size: 1.1rem;">Claim Reward</button>
            </div>
            
            <!-- Native Ad after video -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <div style="margin-top: 30px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px;">
                <?php echo $settings['ad_native_code'] ?? ''; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Bottom Banner Ad -->
        <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <div style="margin-top: 30px; min-height: 90px;">
            <?php echo $settings['ad_banner_320_50_code'] ?? ''; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Use localStorage to persist click state across potential page focus changes
        const clickKey = 'ad_clicked_<?php echo $ad_id; ?>_<?php echo $_SESSION['user_id']; ?>';
        localStorage.removeItem(clickKey); // Reset on page load
        
        let adClicked = false;
        const duration = <?php echo rand(15, 35); ?>;
        const adUrl = "<?php echo $ad['video_url']; ?>";
        let timeLeft = duration;
        
        // Track if the ad button was clicked
        function markAdClicked() {
            adClicked = true;
            localStorage.setItem(clickKey, 'true');
            console.log('Ad button clicked. Verification successful.');
            
            // Visual feedback on the button
            const clickBtn = document.querySelector('#ad-click-btn');
            if (clickBtn) {
                clickBtn.innerHTML = '<span>✅</span> Link Visited';
                clickBtn.style.background = 'var(--secondary-color)';
                clickBtn.style.opacity = '0.8';
            }

            // If timer is already at 0, trigger claim now
            if (timeLeft <= 0) {
                claimReward();
            }
        }

        function claimReward() {
            // Re-check from localStorage just in case variable state was lost
            if (!adClicked && localStorage.getItem(clickKey) !== 'true') {
                alert('Please click the "Click Here to Visit" button to verify you watched the ad.');
                return;
            }

            const claimBtn = document.getElementById('claim-btn');
            if (claimBtn) {
                claimBtn.disabled = true;
                claimBtn.textContent = 'Processing...';
            }

            fetch('../api/claim_reward.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ad_id=<?php echo $ad_id; ?>'
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const rewardSection = document.getElementById('reward-section');
                    rewardSection.innerHTML = `
                        <div class="slide-up" style="background: rgba(16, 185, 129, 0.1); padding: 30px; border-radius: 1.5rem; border: 2px solid var(--secondary-color); text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                            <div style="font-size: 3.5rem; margin-bottom: 15px;">🎉</div>
                            <h2 style="color: var(--secondary-color); margin-bottom: 10px; font-size: 1.8rem;">Task Successful!</h2>
                            <p style="font-size: 1.1rem; font-weight: 600; opacity: 0.9; margin-bottom: 20px;">$<?php echo number_format($user_reward, 4); ?> has been added to your balance.</p>
                            <div style="display: flex; align-items: center; justify-content: center; gap: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem;">
                                <div class="loading-spinner-small"></div>
                                Please wait, redirecting...
                            </div>
                        </div>
                        <style>
                            .loading-spinner-small { width: 18px; height: 18px; border: 3px solid rgba(99, 102, 241, 0.1); border-top: 3px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; }
                            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                        </style>
                    `;
                    setTimeout(() => { window.location.href = 'ads.php?success=ad_complete'; }, 3000);
                } else {
                    alert(data.message);
                    if (claimBtn) { claimBtn.disabled = false; claimBtn.textContent = 'Claim Reward'; }
                }
            })
            .catch(err => {
                alert('Connection error. Please try again.');
                if (claimBtn) { claimBtn.disabled = false; claimBtn.textContent = 'Claim Reward'; }
            });
        }

        // Timer Logic
        const timerDisplay = document.getElementById('ad-timer');
        const progressBar = document.getElementById('timer-progress');
        const totalDash = 276.46;

        const timerInterval = setInterval(() => {
            timeLeft--;
            
            // Update display
            if (timerDisplay) {
                timerDisplay.textContent = Math.max(0, timeLeft);
            }
            
            // Update progress bar
            if (progressBar) {
                const percentage = Math.max(0, timeLeft) / duration;
                const offset = totalDash - (percentage * totalDash);
                progressBar.style.strokeDashoffset = offset;
            }

            // Check if finished
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                
                // Final UI update to ensure it shows 0
                if (timerDisplay) timerDisplay.textContent = "0";
                if (progressBar) progressBar.style.strokeDashoffset = totalDash;

                const overlay = document.getElementById('overlay');
                const rewardSection = document.getElementById('reward-section');
                
                if (overlay) overlay.style.display = 'none';
                if (rewardSection) {
                    rewardSection.style.display = 'block';
                    rewardSection.scrollIntoView({ behavior: 'smooth' });
                }
                
                // Auto-trigger claim if already clicked, otherwise wait for user
                if (adClicked) {
                    console.log('Timer hit 0 and already clicked. Claiming...');
                    claimReward();
                } else {
                    console.log('Timer hit 0. Waiting for user to click the ad button.');
                }
            }
        }, 1000);

        // Auto-open link
        if (adUrl.startsWith('http')) {
            setTimeout(() => { window.open(adUrl, '_blank'); }, 1500);
        }

        document.getElementById('claim-btn').addEventListener('click', claimReward);
    </script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
