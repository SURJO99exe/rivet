<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $pdo->prepare("UPDATE withdrawals SET status = 'approved' WHERE id = ?")->execute([$id]);
}

if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $withdraw = $pdo->query("SELECT * FROM withdrawals WHERE id = $id")->fetch();
    if ($withdraw && $withdraw['status'] == 'pending') {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE withdrawals SET status = 'rejected' WHERE id = ?")->execute([$id]);
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$withdraw['amount'], $withdraw['user_id']]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}

$withdrawals = $pdo->query("
    SELECT w.*, u.username 
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    ORDER BY w.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Withdrawals - <?php echo SITE_NAME; ?></title>
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
                <a href="withdrawals.php" class="admin-nav-item active">
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
                    <h1 style="font-size: 1.75rem;">Manage Withdrawals</h1>
                    <p style="opacity: 0.7;">Approve or reject user withdrawal requests.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button id="theme-toggle" class="btn">🌓</button>
                </div>
            </header>

            <div class="card" style="margin-top: 20px;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                                <th style="padding: 15px 10px;">USER</th>
                                <th style="padding: 15px 10px;">AMOUNT</th>
                                <th style="padding: 15px 10px;">METHOD</th>
                                <th style="padding: 15px 10px;">DETAILS</th>
                                <th style="padding: 15px 10px;">STATUS</th>
                                <th style="padding: 15px 10px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($withdrawals as $w): ?>
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 15px 10px; font-weight: 600;"><?php echo $w['username']; ?></td>
                                    <td style="padding: 15px 10px; font-weight: 700;">$<?php echo number_format($w['amount'], 2); ?></td>
                                    <td style="padding: 15px 10px;"><?php echo $w['payment_method']; ?></td>
                                    <td style="padding: 15px 10px; font-size: 0.9rem; max-width: 200px; word-break: break-all;"><?php echo $w['payment_details']; ?></td>
                                    <td style="padding: 15px 10px;">
                                        <span style="font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; font-weight: 700; background: <?php echo $w['status'] == 'approved' ? 'rgba(16, 185, 129, 0.1)' : ($w['status'] == 'pending' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)'); ?>; color: <?php echo $w['status'] == 'approved' ? 'var(--secondary-color)' : ($w['status'] == 'pending' ? '#f59e0b' : '#ef4444'); ?>;">
                                            <?php echo strtoupper($w['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <?php if($w['status'] == 'pending'): ?>
                                            <a href="?approve=<?php echo $w['id']; ?>" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; text-decoration: none;">Approve</a>
                                            <a href="?reject=<?php echo $w['id']; ?>" class="btn" style="padding: 4px 10px; font-size: 0.75rem; text-decoration: none; background: rgba(239, 68, 68, 0.1); color: #ef4444; margin-left: 5px;">Reject</a>
                                        <?php endif; ?>
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
