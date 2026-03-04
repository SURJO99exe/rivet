<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$survey_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ? AND status = 'active'");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
    redirect('surveys.php');
}

// Check if already completed
$stmt = $pdo->prepare("SELECT id FROM survey_completions WHERE user_id = ? AND survey_id = ?");
$stmt->execute([$user_id, $survey_id]);
if ($stmt->fetch()) {
    redirect('surveys.php?error=already_completed');
}

$questions = json_decode($survey['questions_json'], true);
$random_duration = ($survey['title'] == $welcome_survey_title) ? 60 : rand(181, 300); // 1 min for welcome, 3-5 mins for others
$direct_link = $settings['ad_smartlink_url'] ?? '#';

// Check user membership and daily limit
$stmt = $pdo->prepare("SELECT m.daily_surveys FROM memberships m JOIN users u ON u.membership_id = m.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_membership = $stmt->fetch();
$daily_limit = $user_membership['daily_surveys'] ?? 5;

// Check how many surveys completed today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM survey_completions WHERE user_id = ? AND DATE(completed_at) = CURDATE()");
$stmt->execute([$user_id]);
$completed_today = $stmt->fetchColumn();

if ($completed_today >= $daily_limit) {
    redirect('surveys.php?error=limit_reached');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_survey'])) {
    $ad_clicked = isset($_POST['ad_clicked']) && $_POST['ad_clicked'] == '1';
    
    if (!$ad_clicked) {
        $error = "You must click the direct link to complete this task.";
    } else {
        $completed = true;
        foreach ($questions as $q) {
            if (!isset($_POST['q_' . $q['id']]) || empty($_POST['q_' . $q['id']])) {
                $completed = false;
                break;
            }
        }

        if ($completed) {
            $pdo->beginTransaction();
            try {
                // Record completion
                $stmt = $pdo->prepare("INSERT INTO survey_completions (user_id, survey_id, reward_earned) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $survey_id, $survey['reward']]);

                // Update balance
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?");
                $stmt->execute([$survey['reward'], $survey['reward'], $user_id]);

                // Add to transactions
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earning', ?)");
                $stmt->execute([$user_id, $survey['reward'], "Completed Premium Survey: " . $survey['title']]);

                $pdo->commit();

                // Send Activity Email
                $user_data = $userClass->getUserDetails($user_id);
                if ($user_data) {
                    require_once 'includes/mail_functions.php';
                    require_once 'includes/email_templates.php';
                    $subject = "Task Completed Successfully - " . SITE_NAME;
                    $message = "Congratulations! You have successfully completed the survey: <strong>" . htmlspecialchars($survey['title']) . "</strong>. A reward of <strong>$" . number_format($survey['reward'], 4) . "</strong> has been added to your balance.";
                    $body = getEmailTemplate("Earning Notification", $user_data['username'], null, $message, true);
                    sendMail($user_data['email'], $subject, $body);
                }

                header("Location: dashboard.php?success=survey_complete");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Submission failed. Please try again.";
            }
        } else {
            $error = "Please answer all questions.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $survey['title']; ?> - <?php echo SITE_NAME; ?></title>
    <?php if(defined('SITE_FAVICON') && SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .timer-container {
            position: sticky;
            top: 80px;
            z-index: 100;
            background: #fff;
            padding: 20px;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .timer-container.ready {
            border-color: #10b981;
            background: #f0fdf4;
        }
        #timer-display {
            font-size: 1.8rem;
            font-weight: 800;
            color: #6366f1;
            font-family: 'Monaco', 'Consolas', monospace;
        }
        .timer-container.ready #timer-display {
            color: #10b981;
        }
        .direct-link-box {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            padding: 35px;
            border-radius: 1.5rem;
            margin-bottom: 35px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.3);
        }
        .direct-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: #6366f1;
            padding: 15px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.1rem;
            margin-top: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
        }
        .direct-link-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .survey-content {
            opacity: 0.3;
            filter: blur(4px);
            pointer-events: none;
            transition: all 0.6s ease;
            transform: translateY(20px);
        }
        .survey-content.active {
            opacity: 1;
            filter: blur(0);
            pointer-events: auto;
            transform: translateY(0);
        }
        .question-card {
            margin-bottom: 25px;
            padding: 25px;
            background: #f8fafc;
            border-radius: 1.25rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .question-card:hover {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .option-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .option-label:hover {
            border-color: #6366f1;
            background: #f5f3ff;
        }
        .option-label input[type="radio"] {
            order: 2;
            width: 18px;
            height: 18px;
            accent-color: #6366f1;
        }
        .option-text {
            order: 1;
            font-weight: 500;
            color: #475569;
        }
    </style>
</head>
<body class="light">
    <header class="navbar-fixed">
        <nav class="navbar" style="padding-left: 2rem; padding-right: 2rem;">
            <div class="logo">
                <a href="../index.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                    <?php if(SITE_LOGO): ?>
                        <img src="../assets/img/<?php echo SITE_LOGO; ?>" alt="Logo" style="max-height: 40px;">
                    <?php endif; ?>
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="surveys.php">Surveys</a>
            </div>
        </nav>
    </header>

    <div class="container fade-in" style="max-width: 800px; margin-top: 100px; margin-bottom: 50px;">
        <div class="timer-container" id="timer-box">
            <span>⏱️ Survey unlocks in: </span>
            <span id="timer-display">00:00</span>
        </div>

        <div class="direct-link-box">
            <h3>Step 1: Click the Direct Link</h3>
            <p>You must visit the link below to unlock the survey questions. No popunders are used here.</p>
            <a href="<?php echo $direct_link; ?>" target="_blank" class="direct-link-btn" id="direct-link-btn" onclick="markLinkVisited()">
                🚀 Visit Direct Link
            </a>
            <div id="link-status" style="margin-top: 10px; font-weight: 600; display: none;">✅ Link Visited!</div>
        </div>

        <div class="card survey-content" id="survey-form-container">
            <h2 style="margin-bottom: 10px;"><?php echo $survey['title']; ?></h2>
            <p style="opacity: 0.7; margin-bottom: 30px;">Earn $<?php echo number_format($survey['reward'], 4); ?> after the timer and questions.</p>

            <?php if(isset($error)): ?><div style="color: #ef4444; margin-bottom: 20px; font-weight: 700;"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST" id="survey-form">
                <input type="hidden" name="ad_clicked" id="ad_clicked_input" value="0">
                <?php foreach($questions as $q): ?>
                    <div class="question-card">
                        <p style="font-weight: 700; margin-bottom: 18px; color: #1e293b; font-size: 1.1rem;"><?php echo $q['question']; ?></p>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach($q['options'] as $option): ?>
                                <label class="option-label">
                                    <input type="radio" name="q_<?php echo $q['id']; ?>" value="<?php echo htmlspecialchars($option); ?>" required>
                                    <span class="option-text"><?php echo $option; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="submit_survey" id="submit-btn" class="btn btn-primary" style="width: 100%; padding: 1rem;" disabled>
                    Wait for timer...
                </button>
            </form>
        </div>
    </div>

    <script>
        let timeLeft = <?php echo $random_duration; ?>;
        let linkVisited = false;
        const timerDisplay = document.getElementById('timer-display');
        const surveyContainer = document.getElementById('survey-form-container');
        const submitBtn = document.getElementById('submit-btn');
        const adClickedInput = document.getElementById('ad_clicked_input');

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        function markLinkVisited() {
            linkVisited = true;
            adClickedInput.value = '1';
            document.getElementById('link-status').style.display = 'block';
            document.getElementById('direct-link-btn').style.opacity = '0.5';
            document.getElementById('direct-link-btn').innerText = 'Link Visited';
            checkUnlock();
        }

        function checkUnlock() {
            if (timeLeft <= 0 && linkVisited) {
                const timerBox = document.getElementById('timer-box');
                timerBox.classList.add('ready');
                surveyContainer.classList.add('active');
                submitBtn.disabled = false;
                submitBtn.style.background = '#10b981';
                submitBtn.innerText = '✅ Submit Survey & Claim Reward';
                timerDisplay.innerText = 'READY!';
            }
        }

        const countdown = setInterval(() => {
            timeLeft--;
            timerDisplay.innerText = formatTime(Math.max(0, timeLeft));

            if (timeLeft <= 0) {
                clearInterval(countdown);
                checkUnlock();
                if (!linkVisited) {
                    timerDisplay.innerText = "CLICK LINK TO START";
                }
            }
        }, 1000);

        // Initial display
        timerDisplay.innerText = formatTime(timeLeft);
    </script>
</body>
</html>
