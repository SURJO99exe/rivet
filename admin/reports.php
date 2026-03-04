<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

// Pagination for Transactions
$items_per_page = 50;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

$total_transactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$total_pages = ceil($total_transactions / $items_per_page);

$recent_transactions = $pdo->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT $items_per_page OFFSET $offset")->fetchAll();

// Statistics for Reports
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND is_admin = 0")->fetchColumn();
$blocked_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'blocked' AND is_admin = 0")->fetchColumn();

$total_ads_watched = $pdo->query("SELECT COUNT(*) FROM ad_views")->fetchColumn();
$total_rewards_paid = $pdo->query("SELECT SUM(reward_earned) FROM ad_views")->fetchColumn() ?: 0;

// Withdrawal stats
$approved_withdrawals = $pdo->query("SELECT SUM(amount) FROM withdrawals WHERE status = 'approved'")->fetchColumn() ?: 0;
$pending_withdrawals = $pdo->query("SELECT SUM(amount) FROM withdrawals WHERE status = 'pending'")->fetchColumn() ?: 0;

$top_earners = $pdo->query("SELECT username, total_earned, balance FROM users WHERE is_admin = 0 ORDER BY total_earned DESC LIMIT 5")->fetchAll();
$ad_performance = $pdo->query("SELECT a.title, COUNT(av.id) as total_views, SUM(av.reward_earned) as total_payout FROM ads a LEFT JOIN ad_views av ON a.id = av.ad_id GROUP BY a.id ORDER BY total_views DESC")->fetchAll();


// Last 7 days earnings chart data
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT SUM(reward_earned) FROM ad_views WHERE DATE(viewed_at) = ?");
    $stmt->execute([$date]);
    $val = $stmt->fetchColumn() ?: 0;
    $chart_data[$date] = $val;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - <?php echo SITE_NAME; ?></title>
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
                <a href="ads.php" class="admin-nav-item">
                    <span>📺</span> Ads Management
                </a>
                <a href="withdrawals.php" class="admin-nav-item">
                    <span>💰</span> Withdrawals
                </a>
                <a href="reports.php" class="admin-nav-item active">
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
                    <h1 style="font-size: 1.75rem;">Platform Reports</h1>
                    <p style="opacity: 0.7;">Detailed analytics and performance metrics.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button id="theme-toggle" class="btn">🌓</button>
                </div>
            </header>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="card">
                <h4>User Distribution</h4>
                <div style="margin-top: 15px;">
                    <p>Active: <strong><?php echo $active_users; ?></strong></p>
                    <p>Blocked: <strong><?php echo $blocked_users; ?></strong></p>
                    <p>Total: <strong><?php echo $total_users; ?></strong></p>
                </div>
            </div>
            <div class="card">
                <h4>Earning Stats</h4>
                <div style="margin-top: 15px;">
                    <p>Total Ads Watched: <strong><?php echo $total_ads_watched; ?></strong></p>
                    <p>Total Rewards: <strong>$<?php echo number_format($total_rewards_paid, 4); ?></strong></p>
                </div>
            </div>
            <div class="card">
                <h4>Withdrawal Stats</h4>
                <div style="margin-top: 15px;">
                    <p>Paid: <strong>$<?php echo number_format($approved_withdrawals, 2); ?></strong></p>
                    <p>Pending: <strong>$<?php echo number_format($pending_withdrawals, 2); ?></strong></p>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 30px;">
            <h3>Earnings (Last 7 Days)</h3>
            <div style="display: flex; align-items: flex-end; gap: 10px; height: 200px; margin-top: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-light);">
                <?php 
                $max_val = max($chart_data) ?: 1;
                foreach($chart_data as $date => $val): 
                    $height = ($val / $max_val) * 100;
                ?>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                        <div style="width: 100%; background: var(--primary-color); height: <?php echo $height; ?>%; border-radius: 4px 4px 0 0;" title="$<?php echo $val; ?>"></div>
                        <span style="font-size: 0.7rem; margin-top: 5px; opacity: 0.6;"><?php echo date('d M', strtotime($date)); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; margin-top: 30px;">
            <!-- Top Earners -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">🏆 Top Earners</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                            <th style="padding: 10px;">User</th>
                            <th style="padding: 10px;">Total Earned</th>
                            <th style="padding: 10px;">Current Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($top_earners as $earner): ?>
                        <tr style="border-bottom: 1px solid var(--border-light);">
                            <td style="padding: 12px 10px; font-weight: 600;"><?php echo htmlspecialchars($earner['username']); ?></td>
                            <td style="padding: 12px 10px; color: var(--secondary-color); font-weight: 700;">$<?php echo number_format($earner['total_earned'], 4); ?></td>
                            <td style="padding: 12px 10px;">$<?php echo number_format($earner['balance'], 4); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ad Performance -->
            <div class="card" style="height: 500px; display: flex; flex-direction: column;">
                <h3 style="margin-bottom: 1.5rem;">📊 Ad Performance</h3>
                <div style="overflow-y: auto; flex: 1; scrollbar-width: thin;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="position: sticky; top: 0; background: var(--card-bg); z-index: 10;">
                            <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                                <th style="padding: 10px;">Ad Title</th>
                                <th style="padding: 10px;">Views</th>
                                <th style="padding: 10px;">Payout</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ad_performance as $ad): ?>
                            <tr style="border-bottom: 1px solid var(--border-light);">
                                <td style="padding: 12px 10px; font-weight: 600; font-size: 0.85rem;"><?php echo htmlspecialchars($ad['title']); ?></td>
                                <td style="padding: 12px 10px;"><?php echo $ad['total_views']; ?></td>
                                <td style="padding: 12px 10px; font-weight: 700;">$<?php echo number_format($ad['total_payout'], 4); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card" style="margin-top: 30px; margin-bottom: 50px;">
            <h3 style="margin-bottom: 1.5rem;">💸 Recent Global Transactions</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                            <th style="padding: 10px;">Date</th>
                            <th style="padding: 10px;">User</th>
                            <th style="padding: 10px;">Type</th>
                            <th style="padding: 10px;">Description</th>
                            <th style="padding: 10px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_transactions as $tx): ?>
                        <tr style="border-bottom: 1px solid var(--border-light);">
                            <td style="padding: 12px 10px; font-size: 0.9rem;"><?php echo date('M d, H:i', strtotime($tx['created_at'])); ?></td>
                            <td style="padding: 12px 10px; font-weight: 600;"><?php echo htmlspecialchars($tx['username']); ?></td>
                            <td style="padding: 12px 10px;">
                                <span style="font-size: 0.75rem; padding: 3px 8px; border-radius: 10px; background: <?php 
                                    echo $tx['type'] == 'earning' ? 'rgba(16, 185, 129, 0.1)' : 
                                        ($tx['type'] == 'withdrawal' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(99, 102, 241, 0.1)'); 
                                ?>; color: <?php 
                                    echo $tx['type'] == 'earning' ? 'var(--secondary-color)' : 
                                        ($tx['type'] == 'withdrawal' ? '#ef4444' : 'var(--primary-color)'); 
                                ?>;">
                                    <?php echo strtoupper($tx['type']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px 10px; font-size: 0.9rem; opacity: 0.8;"><?php echo htmlspecialchars($tx['description']); ?></td>
                            <td style="padding: 12px 10px; font-weight: 700;">$<?php echo number_format($tx['amount'], 4); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px; margin-bottom: 10px;">
                    <?php if($current_page > 1): ?>
                        <a href="?page=1" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);">&laquo; First</a>
                        <a href="?page=<?php echo $current_page - 1; ?>" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1); font-weight: bold;">&lsaquo;</a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    if($end_page - $start_page < 4) {
                        $start_page = max(1, $end_page - 4);
                    }
                    $start_page = max(1, $start_page);

                    for($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="btn <?php echo ($i == $current_page) ? 'btn-primary' : ''; ?>" style="padding: 0.5rem 1rem; min-width: 40px; text-align: center; <?php echo ($i != $current_page) ? 'background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1); font-weight: bold;">&rsaquo;</a>
                        <a href="?page=<?php echo $total_pages; ?>" class="btn" style="padding: 0.5rem 1rem; background: var(--card-bg); border: 1px solid rgba(0,0,0,0.1);">&raquo; Last</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
    <script src="../assets/js/main.js"></script>
</body>
</html>
