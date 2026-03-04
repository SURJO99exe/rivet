<?php
require_once __DIR__ . '/../includes/user_functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = $userClass->getUserDetails($_SESSION['user_id']);
$user_country = strtolower($user['country'] ?? 'global');

// Get country-specific payment methods
$setting_key = "payment_methods_" . $user_country;
$payment_methods_str = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$payment_methods_str->execute([$setting_key]);
$methods = $payment_methods_str->fetchColumn();

if (!$methods) {
    // Fallback to Global if country specific not found
    $payment_methods_str = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'payment_methods_global'")->fetchColumn();
    $payment_methods = explode(',', $payment_methods_str);
} else {
    $payment_methods = explode(',', $methods);
}

$min_withdrawal = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'min_withdrawal'")->fetchColumn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = (float)$_POST['amount'];
    $method = sanitize($_POST['method']);
    $details = sanitize($_POST['details']);

    if ($amount < $min_withdrawal) {
        $error = "Minimum withdrawal amount is $" . $min_withdrawal;
    } elseif ($amount > $user['balance']) {
        $error = "Insufficient balance";
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, payment_details) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $amount, $method, $details]);

            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $_SESSION['user_id']]);

            // Record transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'withdrawal', ?)");
            $stmt->execute([$_SESSION['user_id'], $amount, "Withdrawal request via " . $method]);

            $pdo->commit();

            // Send Activity Email
            require_once '../includes/mail_functions.php';
            require_once '../includes/email_templates.php';
            $subject = "Withdrawal Request Received - " . SITE_NAME;
            $message = "Your withdrawal request for <strong>$" . number_format($amount, 2) . "</strong> via <strong>" . htmlspecialchars($method) . "</strong> has been received and is currently under review. We will notify you once it has been processed.";
            $body = getEmailTemplate("Withdrawal Update", $user['username'], null, $message, true);
            sendMail($user['email'], $subject, $body);

            $success = "Withdrawal request submitted successfully!";
            $user['balance'] -= $amount; // Update local variable for display
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Something went wrong. Please try again.";
        }
    }
}

// Get history
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="../assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .withdraw-card {
            background: #ffffff;
            border-radius: 1.5rem;
            padding: 30px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .method-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.5em 1.5em;
        }
        .history-table th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 15px;
        }
        .history-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .balance-badge {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            padding: 30px;
            border-radius: 1.25rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
    </style>
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <main class="fade-in" style="padding: 0 2rem; max-width: 1200px; margin: 0 auto;">
        <div style="margin-top: 60px; margin-bottom: 40px; text-align: center;">
            <h2 style="font-size: 2.5rem; font-weight: 800; color: #1e293b;">Withdraw Funds</h2>
            <p style="color: #64748b; font-size: 1.1rem;">Securely withdraw your earnings to your preferred payment method.</p>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; margin-top: 30px;">
            <div>
                <div class="balance-badge">
                    <div style="background: rgba(255, 255, 255, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        💰
                    </div>
                    <div>
                        <p style="font-size: 0.95rem; opacity: 0.9; font-weight: 500;">Available Balance</p>
                        <h3 style="font-size: 2.2rem; font-weight: 800;">$<?php echo number_format($user['balance'], 4); ?></h3>
                    </div>
                </div>

                <div class="withdraw-card slide-up">
                    <h3 style="font-size: 1.25rem; margin-bottom: 25px; color: #1e293b;">Request Payout</h3>
                    
                    <?php if($error): ?>
                        <div style="background: #fef2f2; color: #ef4444; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #fee2e2;">
                            <strong>Error:</strong> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div style="background: #f0fdf4; color: #10b981; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #dcfce7;">
                            <strong>Success:</strong> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Withdrawal Amount</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: 600;">$</span>
                                <input type="number" name="amount" step="0.01" min="<?php echo $min_withdrawal; ?>" placeholder="0.00" required style="padding-left: 30px; height: 55px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 600; font-size: 1.1rem;">
                            </div>
                            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px;">Minimum withdrawal: <strong>$<?php echo number_format($min_withdrawal, 2); ?></strong></p>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Payment Method</label>
                            <select name="method" class="method-select" required style="height: 55px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 600;">
                                <option value="" disabled selected>Select method...</option>
                                <?php foreach($payment_methods as $method): ?>
                                    <option value="<?php echo trim($method); ?>"><?php echo trim($method); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Account Details</label>
                            <textarea name="details" placeholder="Enter your email, wallet address, or account number" required style="height: 100px; border-radius: 12px; border: 1px solid #e2e8f0; padding: 15px; font-size: 0.95rem; resize: none;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 12px; font-weight: 800; font-size: 1rem; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">Submit Withdrawal Request</button>
                    </form>
                </div>
            </div>

            <div class="withdraw-card history-table-container slide-up" style="transition-delay: 0.1s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-size: 1.25rem; color: #1e293b;">Withdrawal History</h3>
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;"><?php echo count($history); ?> Total Requests</span>
                </div>
                
                <div class="table-container" style="overflow-x: auto;">
                    <table class="history-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($history as $row): ?>
                                <tr>
                                    <td style="color: #64748b;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                    <td style="font-weight: 800; color: #1e293b;">$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $status_color = '#f59e0b';
                                        $status_bg = '#fffbeb';
                                        if($row['status'] == 'approved') { $status_color = '#10b981'; $status_bg = '#f0fdf4'; }
                                        if($row['status'] == 'rejected') { $status_color = '#ef4444'; $status_bg = '#fef2f2'; }
                                        ?>
                                        <span style="padding: 6px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <?php echo strtoupper($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(count($history) == 0): ?>
                                <tr>
                                    <td colspan="4" style="padding: 60px; text-align: center;">
                                        <div style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;">📄</div>
                                        <p style="opacity: 0.5; font-weight: 600;">No withdrawal history found yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
