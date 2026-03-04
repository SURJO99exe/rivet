<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$payment_id = $_GET['payment_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Payment - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta http-equiv="refresh" content="30">
</head>
<body class="light">
    <main class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
        <div class="card fade-in" style="max-width: 500px; text-align: center; padding: 50px;">
            <div class="spinner" style="margin: 0 auto 30px;"></div>
            <h2 style="font-size: 2rem; margin-bottom: 15px;">Waiting for Confirmation</h2>
            <p style="opacity: 0.7; margin-bottom: 30px; line-height: 1.6;">We are waiting for your crypto transaction to be confirmed on the blockchain. This page will automatically update once the payment is finished.</p>
            <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 15px; border-radius: 10px; margin-bottom: 30px; font-size: 0.9rem;">
                <strong>Status:</strong> Awaiting Network Confirmation...
            </div>
            <p style="font-size: 0.8rem; opacity: 0.5;">Payment ID: <?php echo htmlspecialchars($payment_id); ?></p>
            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <a href="upgrade.php" class="btn" style="flex: 1; border: 1px solid var(--border-light);">Cancel</a>
                <a href="dashboard.php" class="btn btn-primary" style="flex: 2;">Back to Dashboard</a>
            </div>
        </div>
    </main>
</body>
</html>
