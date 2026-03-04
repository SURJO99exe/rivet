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
    <title>Payment Successful - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="light">
    <main class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
        <div class="card fade-in" style="max-width: 500px; text-align: center; padding: 50px;">
            <div style="background: rgba(16, 185, 129, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; font-size: 3rem;">
                ✅
            </div>
            <h2 style="font-size: 2rem; margin-bottom: 15px;">Payment Successful!</h2>
            <p style="opacity: 0.7; margin-bottom: 30px; line-height: 1.6;">Your crypto payment has been received and processed. Your account membership has been upgraded automatically.</p>
            <div style="background: rgba(0,0,0,0.02); padding: 15px; border-radius: 10px; margin-bottom: 30px; font-size: 0.9rem;">
                <strong>Payment ID:</strong> <?php echo htmlspecialchars($payment_id); ?>
            </div>
            <a href="dashboard.php" class="btn btn-primary" style="width: 100%; padding: 1rem;">Go to Dashboard</a>
        </div>
    </main>
</body>
</html>
