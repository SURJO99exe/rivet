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
    die("You have already completed this survey.");
}

$questions = json_decode($survey['questions_json'], true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_survey'])) {
    // Basic server-side validation: ensure all questions were answered
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
            $stmt->execute([$user_id, $survey['reward'], "Completed survey: " . $survey['title']]);

            $pdo->commit();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $survey['title']; ?> - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            <div class="nav-links" id="nav-links">
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
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <div class="container fade-in" style="max-width: 800px; margin-top: 100px; margin-bottom: 50px;">
        <div class="card">
            <h2 style="margin-bottom: 10px;"><?php echo $survey['title']; ?></h2>
            <p style="opacity: 0.7; margin-bottom: 30px;">Earn $<?php echo number_format($survey['reward'], 4); ?> upon completion.</p>

            <?php if(isset($error)): ?><div style="color: #ef4444; margin-bottom: 20px;"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <?php foreach($questions as $q): ?>
                    <div style="margin-bottom: 25px; padding: 20px; background: rgba(0,0,0,0.02); border-radius: 1rem; border: 1px solid var(--border-light);">
                        <p style="font-weight: 600; margin-bottom: 15px;"><?php echo $q['question']; ?></p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach($q['options'] as $option): ?>
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="radio" name="q_<?php echo $q['id']; ?>" value="<?php echo htmlspecialchars($option); ?>" required style="width: auto; margin-top: 0;">
                                    <span><?php echo $option; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="submit_survey" class="btn btn-primary" style="width: 100%; padding: 1rem;">Submit Survey & Claim Reward</button>
            </form>
        </div>
    </div>
</body>
</html>
