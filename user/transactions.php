<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - <?php echo SITE_NAME; ?></title>
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

    <main class="fade-in" style="padding: 0 2rem;">
        <div style="margin-top: 40px; text-align: center;">
            <h2 style="font-size: 2.5rem;">Transaction History</h2>
            <p style="opacity: 0.7;">View all your financial movements on the platform.</p>
        </div>

        <div class="card" style="margin-top: 30px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; opacity: 0.6; font-size: 0.85rem; border-bottom: 1px solid var(--border-light);">
                        <th style="padding: 15px 10px;">DATE</th>
                        <th style="padding: 15px 10px;">TYPE</th>
                        <th style="padding: 15px 10px;">DESCRIPTION</th>
                        <th style="padding: 15px 10px;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $tx): ?>
                        <tr style="border-bottom: 1px solid var(--border-light); font-size: 0.95rem;">
                            <td style="padding: 15px 10px;"><?php echo date('M d, Y H:i', strtotime($tx['created_at'])); ?></td>
                            <td style="padding: 15px 10px;">
                                <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: <?php 
                                    echo $tx['type'] == 'earning' ? 'rgba(16, 185, 129, 0.1)' : 
                                        ($tx['type'] == 'upgrade' ? 'rgba(99, 102, 241, 0.1)' : 
                                        ($tx['type'] == 'withdrawal' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)')); 
                                ?>; color: <?php 
                                    echo $tx['type'] == 'earning' ? 'var(--secondary-color)' : 
                                        ($tx['type'] == 'upgrade' ? 'var(--primary-color)' : 
                                        ($tx['type'] == 'withdrawal' ? '#ef4444' : '#f59e0b')); 
                                ?>;">
                                    <?php echo strtoupper($tx['type']); ?>
                                </span>
                            </td>
                            <td style="padding: 15px 10px; opacity: 0.8;"><?php echo $tx['description']; ?></td>
                            <td style="padding: 15px 10px; font-weight: 700; color: <?php echo in_array($tx['type'], ['earning', 'referral']) ? 'var(--secondary-color)' : '#ef4444'; ?>">
                                <?php echo in_array($tx['type'], ['earning', 'referral']) ? '+' : '-'; ?>$<?php echo number_format($tx['amount'], 4); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($transactions) == 0): ?>
                        <tr>
                            <td colspan="4" style="padding: 40px; text-align: center; opacity: 0.5;">
                                No transactions found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="../assets/js/main.js"></script>
</body>
</html>
