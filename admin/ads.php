<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_ad'])) {
    $title = sanitize($_POST['title']);
    $url = sanitize($_POST['url']);
    $thumbnail = sanitize($_POST['thumbnail'] ?? '');
    $duration = (int)$_POST['duration'];
    $reward = (float)$_POST['reward'];

    $stmt = $pdo->prepare("INSERT INTO ads (title, video_url, thumbnail, duration, reward) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $url, $thumbnail, $duration, $reward]);
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM ads WHERE id = ?")->execute([$id]);
}

// Pagination for Ads
$items_per_page = 50;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

$total_ads_count = $pdo->query("SELECT COUNT(*) FROM ads")->fetchColumn();
$total_pages = ceil($total_ads_count / $items_per_page);

$ads = $pdo->query("SELECT * FROM ads ORDER BY created_at DESC LIMIT $items_per_page OFFSET $offset")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ads - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="light">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h2>F Earning</h2>
            <nav class="admin-nav" id="nav-links">
                <a href="dashboard.php" class="admin-nav-item">
                    <span>📊</span> Dashboard
                </a>
                <a href="users.php" class="admin-nav-item">
                    <span>👥</span> Users
                </a>
                <a href="ads.php" class="admin-nav-item active">
                    <span>📺</span> Ads Management
                </a>
                <a href="withdrawals.php" class="admin-nav-item">
                    <span>💰</span> Withdrawals
                </a>
                <a href="reports.php" class="admin-nav-item">
                    <span>📈</span> Reports
                </a>
                <a href="settings.php" class="admin-nav-item">
                    <span>⚙️</span> Settings
                </a>
            </nav>
            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div style="margin-top: auto;">
                <a href="../logout.php" class="admin-nav-item" style="color: #ef4444;">
                    <span>🚪</span> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Manage Ads</h1>
                    <p style="opacity: 0.7;">Create and manage video advertisements.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button id="theme-toggle" class="btn">🌓</button>
                </div>
            </header>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px;">
                <div class="card">
                    <h3>Add New Ad</h3>
                    <form method="POST">
                        <div style="margin-bottom: 15px;">
                            <label>Title</label>
                            <input type="text" name="title" placeholder="Enter ad title" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Video URL / ID</label>
                            <input type="text" name="url" placeholder="e.g. dQw4w9WgXcQ" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Thumbnail URL (Optional)</label>
                            <input type="text" name="thumbnail" placeholder="e.g. https://example.com/image.jpg">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Duration (Seconds)</label>
                            <input type="number" name="duration" placeholder="e.g. 30" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label>Reward ($)</label>
                            <input type="number" name="reward" step="0.0001" placeholder="e.g. 0.0100" required>
                        </div>
                        <button type="submit" name="add_ad" class="btn btn-primary" style="width: 100%;">Add Ad</button>
                    </form>
                </div>

                <div class="card" style="height: 600px; display: flex; flex-direction: column;">
                    <h3>Existing Ads</h3>
                    <div style="overflow-y: auto; flex: 1; scrollbar-width: thin; margin-top: 15px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead style="position: sticky; top: 0; background: var(--card-bg); z-index: 10;">
                                <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                                    <th style="padding: 15px 10px;">TITLE</th>
                                    <th style="padding: 15px 10px;">REWARD</th>
                                    <th style="padding: 15px 10px;">DURATION</th>
                                    <th style="padding: 15px 10px;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ads as $ad): ?>
                                    <tr style="border-bottom: 1px solid var(--border-light);">
                                        <td style="padding: 15px 10px; font-weight: 600; font-size: 0.85rem;"><?php echo htmlspecialchars($ad['title']); ?></td>
                                        <td style="padding: 15px 10px; color: var(--secondary-color); font-weight: 700;">$<?php echo number_format($ad['reward'], 4); ?></td>
                                        <td style="padding: 15px 10px;"><?php echo $ad['duration']; ?>s</td>
                                        <td style="padding: 15px 10px;">
                                            <a href="?delete=<?php echo $ad['id']; ?>" style="color: #ef4444; text-decoration: none; font-size: 0.85rem;" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Navigation -->
                    <?php if($total_pages > 1): ?>
                        <div style="display: flex; justify-content: center; align-items: center; gap: 10px; padding: 15px 0; border-top: 1px solid var(--border-light); background: var(--card-bg);">
                            <?php if($current_page > 1): ?>
                                <a href="?page=1" class="btn" style="padding: 0.4rem 0.8rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);">&laquo;</a>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 1);
                            $end_page = min($total_pages, $start_page + 2);
                            for($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="btn <?php echo ($i == $current_page) ? 'btn-primary' : ''; ?>" style="padding: 0.4rem 0.8rem; min-width: 35px; text-align: center; <?php echo ($i != $current_page) ? 'background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>" class="btn" style="padding: 0.4rem 0.8rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);">&raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
