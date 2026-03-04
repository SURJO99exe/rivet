<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = $userClass->getUserDetails($_SESSION['user_id']);
$memberships = $pdo->query("SELECT * FROM memberships ORDER BY price ASC")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upgrade_id'])) {
    $plan_id = (int)$_POST['upgrade_id'];
    $stmt = $pdo->prepare("SELECT * FROM memberships WHERE id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();

    if ($plan) {
        if ($user['balance'] >= $plan['price']) {
            $pdo->beginTransaction();
            try {
                // Deduct balance
                $stmt = $pdo->prepare("UPDATE users SET balance = balance - ?, membership_id = ?, membership_expires_at = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?");
                $stmt->execute([$plan['price'], $plan_id, $plan['duration_days'], $_SESSION['user_id']]);

                // Record transaction
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'upgrade', ?)");
                $stmt->execute([$_SESSION['user_id'], $plan['price'], "Upgraded to " . $plan['name'] . " Plan"]);

                $pdo->commit();
                $success = "Successfully upgraded to " . $plan['name'] . " plan!";
                $user = $userClass->getUserDetails($_SESSION['user_id']); // Refresh user data
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "An error occurred. Please try again.";
            }
        } else {
            $error = "Insufficient balance. Please earn more or deposit funds.";
        }
    }
}

// Get current membership name
$stmt = $pdo->prepare("SELECT name FROM memberships WHERE id = ?");
$stmt->execute([$user['membership_id']]);
$current_plan_name = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade Plan - F Earning</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 0 2rem;">
        <div style="margin-top: 40px; text-align: center;">
            <h2 style="font-size: 2.5rem;">Membership Plans</h2>
            <p style="opacity: 0.7;">Current Plan: <strong style="color: var(--primary-color);"><?php echo $current_plan_name; ?></strong></p>
            <?php if($user['membership_expires_at']): ?>
                <p style="font-size: 0.85rem; opacity: 0.6;">Expires on: <?php echo date('M d, Y', strtotime($user['membership_expires_at'])); ?></p>
            <?php endif; ?>
        </div>

        <!-- Payment Methods Info -->
        <div style="max-width: 800px; margin: 30px auto 0; text-align: center; padding: 20px; background: white; border-radius: 1rem; box-shadow: var(--shadow);">
            <h4 style="margin-bottom: 15px; opacity: 0.8;">We Accept Global Payment Methods</h4>
            <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; align-items: center;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" style="height: 25px; object-fit: contain;" alt="PayPal">
                    <span style="font-size: 0.75rem; font-weight: 600;">PayPal</span>
                </div>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Binance_logo.svg/1200px-Binance_logo.svg.png" style="height: 25px; object-fit: contain;" alt="Binance">
                    <span style="font-size: 0.75rem; font-weight: 600;">Binance (USDT)</span>
                </div>
            </div>
        </div>

        <?php if($error): ?>
            <div style="max-width: 600px; margin: 20px auto; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if($success): ?>
            <div style="max-width: 600px; margin: 20px auto; background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; text-align: center;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-top: 40px; margin-bottom: 60px;">
            <?php foreach($memberships as $plan): ?>
                <div class="card <?php echo $user['membership_id'] == $plan['id'] ? 'plan-active' : ''; ?>" style="text-align: center; border: <?php echo $user['membership_id'] == $plan['id'] ? '2px solid var(--primary-color)' : '1px solid var(--border-light)'; ?>; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
                    <?php if($user['membership_id'] == $plan['id']): ?>
                        <div style="position: absolute; top: 10px; right: -35px; background: var(--primary-color); color: white; padding: 5px 40px; transform: rotate(45deg); font-size: 0.7rem; font-weight: 700; z-index: 1;">ACTIVE</div>
                    <?php endif; ?>
                    <div>
                        <h3 style="color: var(--primary-color); margin-bottom: 5px;"><?php echo $plan['name']; ?> Plan</h3>
                        <h2 style="font-size: 2.5rem; margin: 15px 0;">
                            <?php echo $plan['price'] == 0 ? 'FREE' : '$' . number_format($plan['price'], 2); ?>
                        </h2>
                        <ul style="list-style: none; margin-bottom: 25px; text-align: left; padding-left: 10px;">
                            <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 8px;">
                                <span style="color: #10b981;">✅</span> 
                                <span><strong><?php echo $plan['daily_ads']; ?></strong> Ads Daily</span>
                            </li>
                            <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 8px;">
                                <span style="color: #10b981;">✅</span> 
                                <span><strong><?php echo $plan['daily_surveys'] ?? 5; ?></strong> Surveys Daily</span>
                            </li>
                            <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 8px;">
                                <span style="color: #10b981;">✅</span> 
                                <span><strong>$<?php echo number_format($plan['ad_reward'], 4); ?></strong> Per Ad</span>
                            </li>
                            <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 8px;">
                                <span style="color: #10b981;">✅</span> 
                                <span>Duration: <strong><?php echo $plan['id'] == 1 ? 'Lifetime' : $plan['duration_days'] . ' Days'; ?></strong></span>
                            </li>
                            <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 8px;">
                                <span style="color: #10b981;">✅</span> 
                                <span><?php echo $plan['id'] == 1 ? 'Basic Support' : '<strong>Priority Support</strong>'; ?></span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <?php if($plan['id'] == 1): ?>
                            <button class="btn" style="width: 100%; cursor: default; background: <?php echo $user['membership_id'] == 1 ? '#10b981' : '#94a3b8'; ?>; color: white;" disabled>
                                <?php echo $user['membership_id'] == 1 ? 'Current Plan' : 'Free Plan'; ?>
                            </button>
                        <?php else: ?>
                            <a href="checkout.php?plan_id=<?php echo $plan['id']; ?>" class="btn <?php echo $user['membership_id'] == $plan['id'] ? 'btn-secondary' : 'btn-primary'; ?>" style="width: 100%; display: block; text-decoration: none; text-align: center;" <?php echo $user['membership_id'] == $plan['id'] ? 'onclick="return false;"' : ''; ?>>
                                <?php echo $user['membership_id'] == $plan['id'] ? 'Current Plan' : 'Upgrade Now'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
