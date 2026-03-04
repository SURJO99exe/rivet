<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
// Get user membership info
$stmt = $pdo->prepare("SELECT m.* FROM memberships m JOIN users u ON u.membership_id = m.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_membership = $stmt->fetch();

$daily_limit = $user_membership['daily_ads'] ?? 10;
$is_basic_plan = ($user_membership['id'] == 1); // Assuming ID 1 is the Free/Basic plan

// Get today's watched count for limit display
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ? AND DATE(viewed_at) = CURDATE()");
$stmt->execute([$user_id]);
$watched_today = (int)$stmt->fetchColumn();

// Pagination settings
$ads_per_page = $daily_limit;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $ads_per_page;

// Get total count of ads not watched by user at all
$count_query = "
    SELECT COUNT(*) 
    FROM ads 
    WHERE ads.status = 'active' 
    AND ads.id NOT IN (
        SELECT ad_id FROM ad_views 
        WHERE user_id = :user_id
    )
";
$stmt = $pdo->prepare($count_query);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_ads = (int)$stmt->fetchColumn();

$total_pages = ceil($total_ads / $ads_per_page);

// Get ads not watched by user at all with pagination
$stmt = $pdo->prepare("
    SELECT ads.*
    FROM ads 
    WHERE ads.status = 'active' 
    AND ads.id NOT IN (
        SELECT ad_id FROM ad_views 
        WHERE user_id = :user_id
    )
    ORDER BY ads.reward DESC, ads.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $ads_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$raw_ads = $stmt->fetchAll();

$available_ads = [];
$ads_shown = 0;
foreach ($raw_ads as $ad) {
    // An ad is unlocked if:
    // 1. We haven't reached the daily limit overall
    // 2. Its position in the TOTAL results (including previous pages) is within the remaining daily quota
    $total_position = $offset + $ads_shown;
    $ad['is_unlocked'] = ($total_position < ($daily_limit - $watched_today)) ? 1 : 0;
    
    $available_ads[] = $ad;
    $ads_shown++;
}

// Check if Adsterra partner ad was already watched today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND type = 'earning' AND description = 'Partner Adsterra reward' AND DATE(created_at) = CURDATE()");
$stmt->execute([$user_id]);
$partner_ad_watched = $stmt->fetchColumn() > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Watch Ads - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_popunder_code'] ?? ''; ?>
    <?php endif; ?>
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 0 2rem; margin-top: 2rem;">
        <div style="margin-top: 40px; margin-bottom: 30px; text-align: center;">
            <h2 style="font-size: 2.5rem;">Available Ads</h2>
            <p style="opacity: 0.7;">Watch ads to earn rewards. Daily limit: <?php echo $watched_today; ?> / <?php echo $daily_limit; ?></p>
            <!-- Adsterra Native Banner -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <div style="margin: 20px auto; max-width: 800px;">
                <?php echo $settings['ad_native_code'] ?? ''; ?>
            </div>
            <!-- Adsterra 468x60 Banner -->
            <div style="margin: 20px auto; max-width: 468px; overflow: hidden;">
                <?php echo $settings['ad_banner_468_60_code'] ?? ''; ?>
            </div>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 20px; align-items: flex-start; margin-bottom: 40px;">
            <!-- Main Ads Content -->
            <div style="flex: 1;">
                <div class="stat-grid-dashboard" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); max-height: 800px; overflow-y: auto; padding: 10px; border: 1px solid rgba(0,0,0,0.05); border-radius: 1rem; scrollbar-width: thin; margin-bottom: 20px;">
                    <?php if($watched_today >= $daily_limit): ?>
                        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 30px; border-top: 4px solid #ef4444; margin-bottom: 20px;">
                            <h3 style="color: #ef4444;">Daily Limit Reached!</h3>
                            <p>You have reached your daily limit of <strong><?php echo $daily_limit; ?></strong> ads. Upgrade your plan to watch more ads every day!</p>
                            <a href="upgrade.php" class="btn btn-primary" style="margin-top: 15px;">Upgrade Now</a>
                        </div>
                    <?php endif; ?>

                    <!-- Premium Partner Ad -->
                    <?php if(($settings['ads_enabled'] ?? '1') == '1' && !$partner_ad_watched): ?>
                    <div class="stat-card-dashboard" style="border-top-color: var(--primary-color); position: relative; min-height: 300px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <span style="position: absolute; top: 10px; right: 10px; background: var(--primary-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 700;">BONUS</span>
                            <span class="icon-wrap" style="top: 15px; right: 45px;">💎</span>
                            <span class="label">Premium Partner Ad</span>
                            <p style="opacity: 0.7; font-size: 0.85rem; margin: 15px 0;">Watch this premium partner ad from Adsterra to earn extra rewards!</p>
                        </div>
                        <a href="watch_adsterra.php" class="btn btn-primary" style="width: 100%; font-size: 0.9rem; padding: 0.75rem;">Watch Partner Ad</a>
                    </div>
                    <?php endif; ?>

                    <?php foreach($available_ads as $ad): ?>
                        <?php 
                        $is_locked = !($ad['is_unlocked'] ?? 1); 
                        $limit_reached = ($watched_today >= $daily_limit);
                        ?>
                        <div class="stat-card-dashboard <?php echo ($is_locked || $limit_reached) ? 'locked-task' : ''; ?>" style="padding: 15px; border-top-color: <?php echo ($is_locked || $limit_reached) ? '#94a3b8' : 'var(--secondary-color)'; ?>; min-height: 300px; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                            <?php if($is_locked || $limit_reached): ?>
                                <a href="upgrade.php" style="position: absolute; inset: 0; background: rgba(255,255,255,0.4); z-index: 10; display: flex; align-items: center; justify-content: center; border-radius: 1rem; backdrop-filter: blur(2px); text-decoration: none; cursor: pointer;">
                                    <div style="background: white; padding: 15px 25px; border-radius: 50px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 1.2rem;">🔒</span>
                                        <span style="font-weight: 700; font-size: 0.85rem; color: #475569;"><?php echo $limit_reached ? 'Limit Reached' : 'Upgrade to Unlock'; ?></span>
                                    </div>
                                </a>
                            <?php endif; ?>
                            <div>
                                <div style="position: relative; height: 160px; background: #111; border-radius: 0.75rem; overflow: hidden; margin-bottom: 15px;">
                                    <?php if(!empty($ad['thumbnail'])): ?>
                                        <img src="<?php echo htmlspecialchars($ad['thumbnail']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php elseif(filter_var($ad['video_url'], FILTER_VALIDATE_URL)): ?>
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: url('https://images.unsplash.com/photo-1614850523296-d8c1af93d400?q=80&w=2070&auto=format&fit=crop') no-repeat center center; background-size: cover; position: relative;">
                                            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.6); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                                <span style="font-size: 2.5rem; display: block; margin-bottom: 5px;">🔗</span>
                                                <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: white; opacity: 0.9;">Direct Ad Link</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <img src="https://img.youtube.com/vi/<?php echo $ad['video_url']; ?>/mqdefault.jpg" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
                                            <span style="font-size: 1.2rem; color: white;">▶️</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(!$is_locked && !$limit_reached): ?>
                                        <a href="watch.php?id=<?php echo $ad['id']; ?>" style="position: absolute; inset: 0; z-index: 5;"></a>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                    <h3 style="font-size: 1.1rem; line-height: 1.2; font-weight: 700;">
                                        <?php if($is_locked || $limit_reached): ?>
                                            <span style="color: inherit; opacity: 0.5;"><?php echo $ad['title']; ?></span>
                                        <?php else: ?>
                                            <a href="watch.php?id=<?php echo $ad['id']; ?>" style="text-decoration: none; color: inherit;"><?php echo $ad['title']; ?></a>
                                        <?php endif; ?>
                                    </h3>
                                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">$<?php echo number_format($user_membership['ad_reward'], 4); ?></span>
                                </div>
                                <p style="opacity: 0.6; font-size: 0.8rem; margin-bottom: 15px;">Required Duration: <strong><?php echo rand(15, 35); ?>s</strong></p>
                            </div>
                            <?php if($is_locked): ?>
                                <a href="upgrade.php" class="btn" style="width: 100%; font-size: 0.9rem; padding: 0.75rem; background: #94a3b8; color: white; cursor: pointer;">Upgrade to Unlock</a>
                            <?php elseif($limit_reached): ?>
                                <a href="upgrade.php" class="btn" style="width: 100%; font-size: 0.9rem; padding: 0.75rem; background: #ef4444; color: white; cursor: pointer;">Limit Reached - Upgrade</a>
                            <?php else: ?>
                                <a href="watch.php?id=<?php echo $ad['id']; ?>" class="btn btn-primary" style="width: 100%; font-size: 0.9rem; padding: 0.75rem;">Watch Ad</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if($is_basic_plan && count($available_ads) >= $daily_limit): ?>
                        <div class="stat-card-dashboard" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px; min-height: 300px;">
                            <span style="font-size: 3rem; margin-bottom: 15px;">🚀</span>
                            <h3 style="color: white; font-size: 1.3rem;">Want More Ads?</h3>
                            <p style="font-size: 0.9rem; opacity: 0.9; margin: 10px 0 20px;">Basic plan is limited to <?php echo $daily_limit; ?> ads. Upgrade to Starter or Pro to unlock 1000+ daily tasks!</p>
                            <a href="upgrade.php" class="btn" style="background: white; color: var(--primary-color); font-weight: 700; width: 100%;">Upgrade Plan</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination Navigation -->
                <?php if($total_pages > 1): ?>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 20px; padding: 20px 0;">
                        <?php if($current_page > 1): ?>
                            <a href="?page=1" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1); text-decoration: none; color: inherit; border-radius: 8px;">&laquo;</a>
                            <a href="?page=<?php echo $current_page - 1; ?>" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1); text-decoration: none; color: inherit; border-radius: 8px;">&lsaquo;</a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 1);
                        $end_page = min($total_pages, $start_page + 2);
                        if ($end_page - $start_page < 2) {
                            $start_page = max(1, $end_page - 2);
                        }

                        for($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="btn <?php echo ($i == $current_page) ? 'btn-primary' : ''; ?>" style="padding: 0.5rem 1rem; min-width: 40px; text-align: center; text-decoration: none; border-radius: 8px; <?php echo ($i != $current_page) ? 'background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1); color: inherit;' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1); text-decoration: none; color: inherit; border-radius: 8px;">&rsaquo;</a>
                            <a href="?page=<?php echo $total_pages; ?>" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1); text-decoration: none; color: inherit; border-radius: 8px;">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Sponsored Banners -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <div style="display: flex; flex-direction: column; gap: 20px; width: 180px; flex-shrink: 0;">
                <div class="stat-card-dashboard" style="display: flex; flex-direction: column; align-items: center; padding: 10px; border-top-color: #f59e0b; height: 320px; overflow: hidden; position: relative;">
                    <span class="label" style="position: absolute; top: 5px; left: 5px; font-size: 0.6rem; z-index: 5; background: rgba(255,255,255,0.8); padding: 2px 5px; border-radius: 4px;">SPONSORED</span>
                    <div style="margin-top: 15px;">
                        <?php echo $settings['ad_banner_160_300_code'] ?? ''; ?>
                    </div>
                </div>
                <div class="stat-card-dashboard" style="display: flex; flex-direction: column; align-items: center; padding: 10px; border-top-color: #ec4899; height: 620px; overflow: hidden; position: relative;">
                    <span class="label" style="position: absolute; top: 5px; left: 5px; font-size: 0.6rem; z-index: 5; background: rgba(255,255,255,0.8); padding: 2px 5px; border-radius: 4px;">SPONSORED</span>
                    <div style="margin-top: 15px;">
                        <?php echo $settings['ad_banner_160_600_code'] ?? ''; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if(count($available_ads) == 0 && $partner_ad_watched): ?>
            <div class="card" style="text-align: center; padding: 60px; margin-top: 40px; width: 100%;">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" style="width: 120px; margin-bottom: 20px; opacity: 0.5;">
                <h3>All Caught Up!</h3>
                <p style="opacity: 0.7; margin-top: 10px;">You have watched all available ads for today. Please come back tomorrow for more opportunities to earn.</p>
                <a href="dashboard.php" class="btn btn-primary" style="margin-top: 25px;">Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>






