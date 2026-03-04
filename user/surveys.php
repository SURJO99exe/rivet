<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Get user membership info and daily limits
$stmt = $pdo->prepare("SELECT m.daily_surveys FROM memberships m JOIN users u ON u.membership_id = m.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_membership = $stmt->fetch();
$daily_limit = $user_membership['daily_surveys'] ?? 5;

// Check how many surveys completed today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM survey_completions WHERE user_id = ? AND DATE(completed_at) = CURDATE()");
$stmt->execute([$user_id]);
$completed_today = $stmt->fetchColumn();

// Pagination logic
$limit = 12; // Surveys per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Special Welcome Survey Logic for new users
$welcome_survey_title = '🌟 Welcome Bonus Survey';
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE title = ? AND status = 'active' LIMIT 1");
$stmt->execute([$welcome_survey_title]);
$welcome_survey = $stmt->fetch();

$show_welcome = false;
if ($welcome_survey) {
    // Check if user has EVER completed ANY survey
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM survey_completions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_completions = $stmt->fetchColumn();
    
    if ($total_completions == 0) {
        $show_welcome = true;
    }
}

// Logic to determine unlocked surveys based on daily limit
$unlocked_count = $daily_limit - $completed_today;

// Get total count for pagination
$total_stmt = $pdo->prepare("
    SELECT COUNT(*) FROM surveys 
    WHERE status = 'active' 
    AND title != ?
    AND id NOT IN (
        SELECT survey_id FROM survey_completions 
        WHERE user_id = ?
    )
");
$total_stmt->execute([$welcome_survey_title, $user_id]);
$total_surveys = $total_stmt->fetchColumn();
$total_pages = ceil($total_surveys / $limit);

// Get surveys for current page (excluding welcome survey from normal list)
$stmt = $pdo->prepare("
    SELECT * FROM surveys 
    WHERE status = 'active' 
    AND title != ?
    AND id NOT IN (
        SELECT survey_id FROM survey_completions 
        WHERE user_id = ?
    )
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute([$welcome_survey_title, $user_id]);
$raw_surveys = $stmt->fetchAll();

$available_surveys = [];
$surveys_shown = 0;
foreach ($raw_surveys as $survey) {
    // A survey is unlocked if:
    // 1. We haven't reached the daily limit overall
    // 2. Its position in the TOTAL results (including previous pages) is within the remaining daily quota
    $total_position = $offset + $surveys_shown;
    $survey['is_unlocked'] = ($total_position < $unlocked_count) ? 1 : 0;
    
    $available_surveys[] = $survey;
    $surveys_shown++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Surveys - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .survey-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }
        .survey-card {
            background: #ffffff;
            border-radius: 1.25rem;
            padding: 20px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .survey-card.locked-survey {
            filter: grayscale(0.5);
        }
        .survey-card:hover:not(.locked-survey) {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #6366f1;
        }
        .locked-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.6);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
            border-radius: 1.25rem;
        }
        .locked-badge {
            background: white;
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            color: #475569;
        }
        .survey-thumb {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 1rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: rgba(255,255,255,0.2);
        }
        .survey-thumb i {
            font-size: 3rem;
        }
        .survey-thumb::after {
            content: 'DIRECT AD LINK';
            position: absolute;
            bottom: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.5);
        }
        .survey-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .survey-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            flex: 1;
        }
        .survey-reward {
            background: #f0fdf4;
            color: #10b981;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            white-space: nowrap;
            margin-left: 10px;
        }
        .survey-timer {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fff7ed;
            color: #f97316;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.8rem;
            margin-top: 10px;
            border: 1px solid #ffedd5;
        }
        .survey-desc {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 25px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .survey-footer {
            margin-top: auto;
        }
        .btn-start {
            display: block;
            width: 100%;
            padding: 12px;
            background: #6366f1;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: background 0.2s;
        }
        .btn-start:hover {
            background: #4f46e5;
        }
        .page-header {
            text-align: center;
            margin-top: 60px;
            margin-bottom: 20px;
        }
        .page-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .page-header p {
            color: #64748b;
            font-size: 1.1rem;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
            margin-bottom: 50px;
        }
        .pagination a, .pagination span {
            padding: 10px 18px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .pagination a:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: #f5f3ff;
        }
        .pagination .active {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }
        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 0 2rem; max-width: 1200px; margin: 0 auto;">
        <div class="page-header">
            <h2>Available Surveys</h2>
            <p>Complete simple surveys to earn high rewards.</p>
            <div style="margin-top: 15px; display: inline-block; background: rgba(99, 102, 241, 0.1); padding: 8px 20px; border-radius: 50px; font-weight: 600; color: #6366f1;">
                Daily Limit: <?php echo $completed_today; ?> / <?php echo $daily_limit; ?>
            </div>
        </div>

        <div class="survey-grid">
            <?php if ($show_welcome && $page == 1): ?>
                <div class="survey-card slide-up" style="border: 2px solid #f59e0b; background: #fffcf0;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: #f59e0b;"></div>
                    <div class="survey-thumb" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                    </div>
                    <div class="survey-header">
                        <h3 class="survey-title"><?php echo $welcome_survey['title']; ?></h3>
                        <span class="survey-reward" style="background: #fef3c7; color: #92400e;">$1.0000</span>
                    </div>
                    <div class="survey-timer" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
                        <span>⏱️</span> 1:00 Minute
                    </div>
                    <p class="survey-desc"><?php echo $welcome_survey['description']; ?></p>
                    <div class="survey-footer">
                        <a href="take_survey_direct.php?id=<?php echo $welcome_survey['id']; ?>" class="btn-start" style="background: #f59e0b;">Claim Welcome Bonus</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach($available_surveys as $survey): ?>
                <?php 
                $is_locked = !($survey['is_unlocked'] ?? 1); 
                $limit_reached = ($completed_today >= $daily_limit);
                ?>
                <div class="survey-card slide-up <?php echo ($is_locked || $limit_reached) ? 'locked-survey' : ''; ?>">
                    <?php if($is_locked || $limit_reached): ?>
                        <a href="upgrade.php" class="locked-overlay" style="text-decoration: none; cursor: pointer;">
                            <div class="locked-badge">
                                <span>🔒</span>
                                <span><?php echo $limit_reached ? 'Daily Limit Reached' : 'Upgrade to Unlock'; ?></span>
                            </div>
                        </a>
                    <?php endif; ?>
                    <div class="survey-thumb">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                    </div>
                    <div class="survey-header">
                        <h3 class="survey-title"><?php echo $survey['title']; ?></h3>
                        <span class="survey-reward">$<?php echo number_format($survey['reward'], 4); ?></span>
                    </div>
                    <div class="survey-timer">
                        <span>⏱️</span> <?php echo rand(3, 5); ?>:<?php echo str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT); ?> Minutes
                    </div>
                    <p class="survey-desc"><?php echo $survey['description']; ?></p>
                    <div class="survey-footer">
                        <?php if ($completed_today < $daily_limit): ?>
                            <a href="take_survey_direct.php?id=<?php echo $survey['id']; ?>" class="btn-start">Start Survey</a>
                        <?php else: ?>
                            <button class="btn-start" style="background: #cbd5e1; cursor: not-allowed;" disabled>Limit Reached</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if(count($available_surveys) == 0): ?>
            <div class="card" style="text-align: center; padding: 80px; margin-top: 40px;">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" style="width: 120px; opacity: 0.3; margin-bottom: 25px;">
                <h3>No Surveys Available</h3>
                <p style="opacity: 0.6;">Check back later for new survey opportunities.</p>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
