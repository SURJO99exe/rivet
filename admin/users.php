<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $uid = (int)$_POST['user_id'];
    $status = sanitize($_POST['status']);
    $membership_id = (int)$_POST['membership_id'];
    
    $stmt = $pdo->prepare("UPDATE users SET status = ?, membership_id = ? WHERE id = ?");
    $stmt->execute([$status, $membership_id, $uid]);
}

// Fetch all memberships for the dropdown
$memberships = $pdo->query("SELECT id, name FROM memberships ORDER BY price ASC")->fetchAll();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Pagination
$items_per_page = 50;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

$query_count = "SELECT COUNT(*) FROM users WHERE is_admin = 0";
$query = "SELECT u.*, m.name as membership_name, m.daily_ads FROM users u LEFT JOIN memberships m ON u.membership_id = m.id WHERE u.is_admin = 0";
$params = [];

if ($search) {
    $search_part = " AND (u.username LIKE ? OR u.email LIKE ?)";
    $query_count .= $search_part;
    $query .= $search_part;
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $status_part = " AND u.status = ?";
    $query_count .= $status_part;
    $query .= $status_part;
    $params[] = $status_filter;
}

// Get total count for pagination
$stmt_count = $pdo->prepare($query_count);
$stmt_count->execute($params);
$total_users = $stmt_count->fetchColumn();
$total_pages = ceil($total_users / $items_per_page);

$query .= " ORDER BY u.created_at DESC LIMIT $items_per_page OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
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
                <a href="users.php" class="admin-nav-item active">
                    <span>👥</span> Users
                </a>
                <a href="ads.php" class="admin-nav-item">
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
                    <h1 style="font-size: 1.75rem;">User Management</h1>
                    <p style="opacity: 0.7;">View and manage your platform's users.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <form method="GET" style="display: flex; gap: 10px;">
                        <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search user..." style="padding: 8px; width: 200px; margin-top: 0;">
                        <select name="status" style="padding: 8px; width: 120px; margin-top: 0;">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="blocked" <?php echo $status_filter == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 8px 15px;">Filter</button>
                    </form>
                    <button id="theme-toggle" class="btn">🌓</button>
                </div>
            </header>

            <div class="card" style="margin-top: 20px;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                                <th style="padding: 15px 10px;">User Info</th>
                                <th style="padding: 15px 10px;">Balance</th>
                                <th style="padding: 15px 10px;">Current Plan</th>
                                <th style="padding: 15px 10px;">Status</th>
                                <th style="padding: 15px 10px;">Daily Ads</th>
                                <th style="padding: 15px 10px;">Joined Date</th>
                                <th style="padding: 15px 10px; width: 250px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 15px 10px;">
                                        <div style="font-weight: 700;"><?php echo htmlspecialchars($u['username']); ?></div>
                                        <div style="font-size: 0.8rem; opacity: 0.6;"><?php echo htmlspecialchars($u['email']); ?></div>
                                    </td>
                                    <td style="padding: 15px 10px; font-weight: 700; color: var(--secondary-color);">$<?php echo number_format($u['balance'], 4); ?></td>
                                    <td style="padding: 15px 10px;">
                                        <span style="font-size: 0.75rem; padding: 3px 8px; border-radius: 10px; background: rgba(99, 102, 241, 0.1); color: var(--primary-color); font-weight: 600;">
                                            <?php echo htmlspecialchars($u['membership_name'] ?: 'None'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <span style="font-size: 0.75rem; padding: 3px 8px; border-radius: 10px; background: <?php echo $u['status'] == 'active' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $u['status'] == 'active' ? 'var(--secondary-color)' : '#ef4444'; ?>; font-weight: 600;">
                                            <?php echo strtoupper($u['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <div style="font-weight: 700; color: var(--secondary-color); background: rgba(16, 185, 129, 0.1); padding: 3px 8px; border-radius: 10px; display: inline-block; font-size: 0.8rem;">
                                            <?php 
                                            $stmt_daily = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ? AND DATE(viewed_at) = CURDATE()");
                                            $stmt_daily->execute([$u['id']]);
                                            echo (int)$stmt_daily->fetchColumn(); 
                                            ?> / <?php echo $u['daily_ads'] ?? 10; ?>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 0.85rem; opacity: 0.7;"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td style="padding: 15px 10px;">
                                        <form method="POST" style="display: flex; gap: 5px;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <select name="membership_id" style="padding: 5px; font-size: 0.8rem; flex: 1;">
                                                <?php foreach($memberships as $m): ?>
                                                    <option value="<?php echo $m['id']; ?>" <?php echo $u['membership_id'] == $m['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="status" style="padding: 5px; font-size: 0.8rem;">
                                                <option value="active" <?php echo $u['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="blocked" <?php echo $u['status'] == 'blocked' ? 'selected' : ''; ?>>Block</option>
                                            </select>
                                            <button type="submit" name="update_user" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.75rem;">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 10px; padding: 20px 0; border-top: 1px solid var(--border-light);">
                        <?php if($current_page > 1): ?>
                            <a href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn" style="padding: 0.4rem 0.8rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);">&laquo;</a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 1);
                        $end_page = min($total_pages, $start_page + 2);
                        for($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn <?php echo ($i == $current_page) ? 'btn-primary' : ''; ?>" style="padding: 0.4rem 0.8rem; min-width: 35px; text-align: center; <?php echo ($i != $current_page) ? 'background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn" style="padding: 0.4rem 0.8rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

