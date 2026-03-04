<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle Survey Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_survey'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $reward = (float)$_POST['reward'];
    
    // Basic JSON structure for questions
    $questions = [];
    foreach ($_POST['q'] as $index => $text) {
        if (!empty($text)) {
            $questions[] = [
                'id' => $index,
                'question' => sanitize($text),
                'options' => array_map('trim', explode(',', $_POST['options'][$index]))
            ];
        }
    }
    $questions_json = json_encode($questions);

    $stmt = $pdo->prepare("INSERT INTO surveys (title, description, reward, questions_json) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $reward, $questions_json])) {
        $success = "Survey created successfully!";
    } else {
        $error = "Failed to create survey.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM surveys WHERE id = ?")->execute([$id]);
    $success = "Survey deleted!";
}

$surveys = $pdo->query("SELECT * FROM surveys ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Surveys - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="light">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="logo">
                <a href="../index.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px; padding: 0 1rem; margin-bottom: 2rem;">
                    <?php if(SITE_LOGO): ?>
                        <img src="../assets/img/<?php echo SITE_LOGO; ?>" alt="Logo" style="max-height: 30px;">
                    <?php endif; ?>
                    <h2 style="margin-bottom: 0; padding: 0;"><?php echo SITE_NAME; ?></h2>
                </a>
            </div>
            <nav class="admin-nav" id="nav-links">
                <a href="dashboard.php" class="admin-nav-item"><span>📊</span> Dashboard</a>
                <a href="users.php" class="admin-nav-item"><span>👥</span> Users</a>
                <a href="ads.php" class="admin-nav-item"><span>📺</span> Ads Management</a>
                <a href="withdrawals.php" class="admin-nav-item"><span>💰</span> Withdrawals</a>
                <a href="surveys.php" class="admin-nav-item active"><span>📝</span> Surveys</a>
                <a href="reports.php" class="admin-nav-item"><span>📈</span> Reports</a>
                <a href="settings.php" class="admin-nav-item"><span>⚙️</span> Settings</a>
            </nav>
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div style="margin-top: auto;">
                <a href="../logout.php" class="admin-nav-item" style="color: #ef4444;"><span>🚪</span> Logout</a>
            </div>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Manage Surveys</h1>
                    <p style="opacity: 0.7;">Create and manage user surveys.</p>
                </div>
                <button id="theme-toggle" class="btn">🌓</button>
            </header>

            <?php if($success): ?><div style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;"><?php echo $success; ?></div><?php endif; ?>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px;">
                <div class="card">
                    <h3>Add New Survey</h3>
                    <form method="POST" style="margin-top: 20px;">
                        <div style="margin-bottom: 15px;">
                            <label>Title</label>
                            <input type="text" name="title" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Description</label>
                            <textarea name="description" style="height: 80px;"></textarea>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Reward ($)</label>
                            <input type="number" step="0.0001" name="reward" required>
                        </div>
                        <div id="questions-container">
                            <label>Questions & Options (Comma separated)</label>
                            <div style="margin-bottom: 10px; padding: 10px; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                <input type="text" name="q[0]" placeholder="Question 1" required>
                                <input type="text" name="options[0]" placeholder="Option A, Option B, Option C" required style="margin-top: 5px;">
                            </div>
                        </div>
                        <button type="submit" name="add_survey" class="btn btn-primary" style="width: 100%;">Create Survey</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Existing Surveys</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                        <thead>
                            <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                                <th style="padding: 10px;">TITLE</th>
                                <th style="padding: 10px;">REWARD</th>
                                <th style="padding: 10px;">STATUS</th>
                                <th style="padding: 10px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($surveys as $s): ?>
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 15px 10px; font-weight: 600;"><?php echo $s['title']; ?></td>
                                    <td style="padding: 15px 10px; color: var(--secondary-color); font-weight: 700;">$<?php echo number_format($s['reward'], 4); ?></td>
                                    <td style="padding: 15px 10px;"><?php echo strtoupper($s['status']); ?></td>
                                    <td style="padding: 15px 10px;">
                                        <a href="?delete=<?php echo $s['id']; ?>" style="color: #ef4444;" onclick="return confirm('Delete survey?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
