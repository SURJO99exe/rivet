<?php
require_once __DIR__ . '/includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get real top earners from DB with minimum 500 ads watched
$stmt = $pdo->query("
    SELECT id, username, total_earned, balance, 
    (SELECT COUNT(*) FROM ad_views WHERE user_id = users.id) as ads_watched
    FROM users 
    WHERE is_admin = 0 
    AND (SELECT COUNT(*) FROM ad_views WHERE user_id = users.id) >= 500
    ORDER BY total_earned DESC 
    LIMIT 20
");
$real_users = $stmt->fetchAll();

// Random names and countries for global users
$random_names = ['Alex', 'Emma', 'Liam', 'Sophia', 'Noah', 'Olivia', 'Ethan', 'Ava', 'Mason', 'Isabella', 'James', 'Mia', 'Lucas', 'Charlotte', 'Benjamin', 'Amelia', 'William', 'Evelyn', 'Jack', 'Abigail', 'Michael', 'Harper', 'Alexander', 'Emily', 'Daniel', 'Elizabeth', 'Matthew', 'Avery', 'Henry', 'Sofia', 'Jackson', 'Ella', 'Sebastian', 'Madison', 'Aiden', 'Scarlett', 'David', 'Victoria', 'Wyatt', 'Aria', 'Carter', 'Grace', 'Owen', 'Chloe', 'Jayden', 'Camila', 'John', 'Penelope', 'Luke', 'Riley'];
$random_countries = ['🇺🇸 USA', '🇬🇧 UK', '🇨🇦 Canada', '🇦🇺 Australia', '🇩🇪 Germany', '🇫🇷 France', '🇮🇳 India', '🇧🇷 Brazil', '🇯🇵 Japan', '🇰🇷 South Korea', '🇳🇬 Nigeria', '🇿🇦 South Africa', '🇵🇰 Pakistan', '🇧🇩 Bangladesh', '🇮🇩 Indonesia', '🇷🇺 Russia', '🇮🇹 Italy', '🇪🇸 Spain', '🇲🇽 Mexico', '🇹🇷 Turkey'];

// Randomize refresh period (between 1 to 4 hours)
if (!isset($_SESSION['leaderboard_reset']) || time() >= $_SESSION['leaderboard_reset']) {
    $random_hours = rand(1, 4);
    $_SESSION['leaderboard_reset'] = time() + ($random_hours * 3600);
}
$reset_timestamp = $_SESSION['leaderboard_reset'];

// Generate random users based on the current reset period
srand($reset_timestamp); 
$fake_users = [];
for ($i = 0; $i < 30; $i++) {
    $name = $random_names[array_rand($random_names)] . rand(10, 999);
    $country = $random_countries[array_rand($random_countries)];
    $fake_users[] = [
        'id' => -($i + 1), 
        'username' => $name,
        'country' => $country,
        'total_earned' => (float)(rand(50, 500) / 10),
        'ads_watched' => rand(500, 2000) 
    ];
}
srand(); // Reset seed

// Combine and sort
$all_leaderboard = array_merge($real_users, $fake_users);
usort($all_leaderboard, function($a, $b) {
    return $b['total_earned'] <=> $a['total_earned'];
});

$all_leaderboard = array_slice($all_leaderboard, 0, 50);

$user_id = $_SESSION['user_id'];

// Find current user's rank (among those with 500+ ads)
$stmt = $pdo->prepare("
    SELECT COUNT(*) + 1 
    FROM users 
    WHERE total_earned > (SELECT total_earned FROM users WHERE id = ?) 
    AND is_admin = 0 
    AND (SELECT COUNT(*) FROM ad_views WHERE user_id = users.id) >= 500
");
$stmt->execute([$user_id]);
$user_rank = $stmt->fetchColumn();

// Check if user has enough ads watched to be on leaderboard
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_total_ads = $stmt->fetchColumn();
$is_eligible = ($user_total_ads >= 500);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Leaderboard - <?php echo SITE_NAME; ?></title>
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

    <main class="fade-in" style="padding: 0 2rem; margin-top: 2rem; max-width: 1000px; margin-left: auto; margin-right: auto;">
        <div style="margin-top: 40px; margin-bottom: 30px; text-align: center;">
            <h2 style="font-size: 2.5rem;">🏆 Top Earners</h2>
            <p style="opacity: 0.7;">The most active members on the platform.</p>
        </div>

        <div class="card" style="margin-bottom: 30px; border-left: 5px solid <?php echo $is_eligible ? 'var(--secondary-color)' : '#ef4444'; ?>; padding: 25px; background: white; border-radius: 1rem; box-shadow: var(--shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="opacity: 0.6; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">YOUR CURRENT STANDING</h4>
                    <?php if($is_eligible): ?>
                        <h3 style="font-size: 1.8rem; font-weight: 800; color: var(--primary-color);">Rank #<?php echo $user_rank; ?></h3>
                    <?php else: ?>
                        <h3 style="font-size: 1.8rem; font-weight: 800; color: #ef4444;">Not Yet Ranked</h3>
                        <p style="font-size: 0.9rem; opacity: 0.8; margin-top: 8px; line-height: 1.4;">
                            You need to watch <strong style="color: #111;"><?php echo 500 - $user_total_ads; ?></strong> more ads to qualify for the leaderboard.<br>
                            <span style="font-size: 0.8rem; opacity: 0.6;">(Current Progress: <?php echo $user_total_ads; ?> / 500)</span>
                        </p>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <div style="width: 60px; height: 60px; background: <?php echo $is_eligible ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <?php echo $is_eligible ? '🌟' : '🔒'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Reset Cooldown Timer -->
        <div style="margin-bottom: 30px; text-align: center;">
            <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 12px;">
                <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 2px;">Next Reset In</span>
                <div class="odometer-container">
                    <div class="odo-unit">
                        <div class="odo-digit" id="hour-1">0</div>
                        <div class="odo-digit" id="hour-2">0</div>
                        <label>HOURS</label>
                    </div>
                    <div class="odo-sep">:</div>
                    <div class="odo-unit">
                        <div class="odo-digit" id="min-1">0</div>
                        <div class="odo-digit" id="min-2">0</div>
                        <label>MINS</label>
                    </div>
                    <div class="odo-sep">:</div>
                    <div class="odo-unit">
                        <div class="odo-digit" id="sec-1">0</div>
                        <div class="odo-digit" id="sec-2">0</div>
                        <label>SECS</label>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .odometer-container {
                display: flex;
                gap: 8px;
                align-items: center;
                background: #1a1a1a;
                padding: 15px 25px;
                border-radius: 12px;
                border: 3px solid #333;
                box-shadow: inset 0 0 15px #000, 0 10px 25px rgba(0,0,0,0.5);
            }
            .odo-unit {
                display: flex;
                gap: 2px;
                position: relative;
                padding-bottom: 15px;
            }
            .odo-digit {
                background: #222;
                color: #fff;
                font-family: 'Courier New', Courier, monospace;
                font-size: 2.2rem;
                font-weight: 900;
                width: 35px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                border: 1px solid #444;
                box-shadow: inset 0 0 10px #000;
                position: relative;
                overflow: hidden;
            }
            .odo-digit::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 0;
                width: 100%;
                height: 1px;
                background: rgba(255,255,255,0.1);
                box-shadow: 0 0 5px rgba(0,0,0,0.5);
            }
            .odo-unit label {
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 100%;
                text-align: center;
                font-size: 0.55rem;
                font-weight: 800;
                color: #888;
                letter-spacing: 1px;
            }
            .odo-sep {
                font-size: 1.5rem;
                font-weight: 900;
                color: #444;
                padding-bottom: 15px;
            }
            .digit-flip {
                animation: flipDigit 0.5s ease-in-out;
            }
            @keyframes flipDigit {
                0% { transform: translateY(-100%); opacity: 0; }
                100% { transform: translateY(0); opacity: 1; }
            }
        </style>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--border-light);">
                            <th style="padding: 20px; text-align: center; width: 80px;">Rank</th>
                            <th style="padding: 20px; text-align: left;">Member</th>
                            <th style="padding: 20px; text-align: left;">Country</th>
                            <th style="padding: 20px; text-align: center;">Ads Watched</th>
                            <th style="padding: 20px; text-align: right;">Total Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach($all_leaderboard as $row): 
                            $is_current_user = ($row['id'] == $user_id);
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-light); <?php echo $is_current_user ? 'background: rgba(99, 102, 241, 0.05);' : ''; ?>">
                            <td style="padding: 15px; text-align: center;">
                                <?php if($rank === 1): ?>
                                    <span style="font-size: 1.5rem;">🥇</span>
                                <?php elseif($rank === 2): ?>
                                    <span style="font-size: 1.5rem;">🥈</span>
                                <?php elseif($rank === 3): ?>
                                    <span style="font-size: 1.5rem;">🥉</span>
                                <?php else: ?>
                                    <span style="font-weight: 700; opacity: 0.5;">#<?php echo $rank; ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; text-transform: uppercase;">
                                        <?php echo substr($row['username'], 0, 1); ?>
                                    </div>
                                    <span style="font-weight: 600; <?php echo $is_current_user ? 'color: var(--primary-color);' : ''; ?>">
                                        <?php echo htmlspecialchars($row['username']); ?>
                                        <?php echo $is_current_user ? ' (You)' : ''; ?>
                                    </span>
                                </div>
                            </td>
                            <td style="padding: 15px; opacity: 0.8; font-weight: 500;">
                                <?php echo $row['country'] ?? '🇧🇩 Bangladesh'; ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 600; opacity: 0.7;">
                                <?php echo number_format($row['ads_watched']); ?>
                            </td>
                            <td style="padding: 15px; text-align: right; font-weight: 700; color: var(--secondary-color);">
                                $<?php echo number_format($row['total_earned'], 4); ?>
                            </td>
                        </tr>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Adsterra Banners for Monetization -->
        <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <div style="margin: 40px 0; text-align: center;">
            <?php echo $settings['ad_native_code'] ?? ''; ?>
        </div>
        <?php endif; ?>

    </main>

    <footer style="padding: 40px 2rem; text-align: center; opacity: 0.6; font-size: 0.9rem;">
        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
    </footer>

    <script src="assets/js/main.js"></script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
    <script>
        let lastVals = { h1: -1, h2: -1, m1: -1, m2: -1, s1: -1, s2: -1 };

        function updateDigit(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            if (lastVals[id] !== val) {
                el.textContent = val;
                el.classList.remove('digit-flip');
                void el.offsetWidth; // Trigger reflow
                el.classList.add('digit-flip');
                lastVals[id] = val;
            }
        }

        function updateResetTimer() {
            const now = Math.floor(Date.now() / 1000);
            const target = <?php echo $reset_timestamp; ?>;
            
            const diff = target - now;
            
            if (diff <= 0) {
                window.location.reload();
                return;
            }

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = Math.floor(diff % 60);
            
            const hStr = String(hours).padStart(2, '0');
            const mStr = String(minutes).padStart(2, '0');
            const sStr = String(seconds).padStart(2, '0');

            updateDigit('hour-1', hStr[0]);
            updateDigit('hour-2', hStr[1]);
            updateDigit('min-1', mStr[0]);
            updateDigit('min-2', mStr[1]);
            updateDigit('sec-1', sStr[0]);
            updateDigit('sec-2', sStr[1]);
        }
        
        setInterval(updateResetTimer, 1000);
        updateResetTimer();
    </script>
</body>
</html>
