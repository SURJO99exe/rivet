<?php
require_once __DIR__ . '/../includes/user_functions.php';
require_once __DIR__ . '/../includes/nowpayments.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = $userClass->getUserDetails($_SESSION['user_id']);
$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM memberships WHERE id = ? AND id > 1");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();

if (!$plan) {
    redirect('upgrade.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($payment_method === 'balance') {
        if ($user['balance'] >= $plan['price']) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance - ?, membership_id = ?, membership_expires_at = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?");
                $stmt->execute([$plan['price'], $plan_id, $plan['duration_days'], $_SESSION['user_id']]);

                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'upgrade', ?)");
                $stmt->execute([$_SESSION['user_id'], $plan['price'], "Upgraded to " . $plan['name'] . " Plan (Balance)"]);

                $pdo->commit();
                header("Location: dashboard.php?success=upgraded");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "An error occurred. Please try again.";
            }
        } else {
            $error = "Insufficient balance in your account.";
        }
    } elseif ($payment_method === 'crypto') {
        $order_id = "upgrade_" . $_SESSION['user_id'] . "_plan_" . $plan_id . "_" . time();
        $payment = $nowPayments->createInvoice($plan['price'], 'usd', $order_id, "Upgrade to " . $plan['name']);
        
        if (isset($payment['invoice_url'])) {
            header("Location: " . $payment['invoice_url']);
            exit;
        } else {
            error_log("NOWPAYMENTS FAILED: " . json_encode($payment));
            $error = "Failed to initialize crypto payment. Please try again or use another method.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="light">
    <header class="navbar-fixed">
        <nav class="navbar" style="padding-left: 2rem; padding-right: 2rem;">
            <div class="logo"><h1><?php echo SITE_NAME; ?></h1></div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="upgrade.php">Back to Plans</a>
            </div>
        </nav>
    </header>

    <main class="container fade-in" style="padding: 40px 2rem; max-width: 900px; margin: 80px auto;">
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 style="font-size: 2.5rem;">Checkout</h2>
            <p style="opacity: 0.7;">Finalize your upgrade to the <strong><?php echo $plan['name']; ?> Plan</strong></p>
        </div>

        <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 20px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px; align-items: start;">
            <!-- Order Summary -->
            <div class="card" style="padding: 30px;">
                <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Order Summary</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-light);">
                    <span><?php echo $plan['name']; ?> Plan</span>
                    <span style="font-weight: 700;">$<?php echo number_format($plan['price'], 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: var(--primary-color);">
                    <span>Total</span>
                    <span>$<?php echo number_format($plan['price'], 2); ?></span>
                </div>
                <ul style="list-style: none; margin-top: 25px; font-size: 0.9rem; opacity: 0.7;">
                    <li style="margin-bottom: 8px;">✅ <?php echo $plan['daily_ads']; ?> Ads Daily</li>
                    <li style="margin-bottom: 8px;">✅ $<?php echo number_format($plan['ad_reward'], 4); ?> Per Ad</li>
                    <li style="margin-bottom: 8px;">✅ Duration: <?php echo $plan['duration_days']; ?> Days</li>
                </ul>
            </div>

            <!-- Payment Selection -->
            <div class="card" style="padding: 30px;">
                <h3 style="margin-bottom: 25px; font-size: 1.2rem;">Select Payment Method</h3>
                <form method="POST">
                    <!-- Account Balance -->
                    <label style="display: block; margin-bottom: 15px; cursor: pointer;">
                        <div style="border: 2px solid var(--border-light); padding: 20px; border-radius: 12px; transition: all 0.3s; position: relative;" class="payment-option">
                            <input type="radio" name="payment_method" value="balance" checked style="position: absolute; opacity: 0;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 1.5rem;">💰</span>
                                <div>
                                    <p style="font-weight: 700; margin-bottom: 2px;">Account Balance</p>
                                    <p style="font-size: 0.85rem; opacity: 0.6;">Current: $<?php echo number_format($user['balance'], 4); ?></p>
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Crypto Payment -->
                    <label style="display: block; margin-bottom: 25px; cursor: pointer;">
                        <div style="border: 2px solid var(--border-light); padding: 20px; border-radius: 12px; transition: all 0.3s; position: relative;" class="payment-option">
                            <input type="radio" name="payment_method" value="crypto" style="position: absolute; opacity: 0;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 1.5rem;">💎</span>
                                <div>
                                    <p style="font-weight: 700; margin-bottom: 2px;">Pay with Crypto</p>
                                    <p style="font-size: 0.85rem; opacity: 0.6;">USDT (TRC20) - Instant Activation</p>
                                </div>
                            </div>
                        </div>
                    </label>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; font-weight: 700; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">Complete Purchase</button>
                </form>
            </div>
        </div>
    </main>

    <style>
        .payment-option:hover { border-color: var(--primary-color); background: rgba(99, 102, 241, 0.02); }
        input[type="radio"]:checked + div { border-color: var(--primary-color); background: rgba(99, 102, 241, 0.05); }
        input[type="radio"]:checked + div::after {
            content: "✓";
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary-color);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }
    </style>
    <script src="../assets/js/main.js"></script>
</body>
</html>
