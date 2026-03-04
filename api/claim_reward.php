<?php
require_once __DIR__ . '/../includes/user_functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Handle Partner Ads
    if (isset($_POST['ad_id']) && $_POST['ad_id'] === 'partner_adsterra') {
        // Double check daily limit for partner ad
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND type = 'earning' AND description = 'Partner Adsterra reward' AND DATE(created_at) = CURDATE()");
        $stmt->execute([$user_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Already watched today']);
            exit();
        }

        $reward = 0.0050; // Special reward for partner ads
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?");
            $stmt->execute([$reward, $reward, $user_id]);

            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earning', 'Partner Adsterra reward')");
            $stmt->execute([$user_id, $reward]);

        // Referral commission
        $stmt = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $referred_by = $stmt->fetchColumn();

        if ($referred_by) {
            $commission_percent = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'referral_commission'")->fetchColumn();
            $commission_amount = $reward * ($commission_percent / 100);
            
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?");
            $stmt->execute([$commission_amount, $commission_amount, $referred_by]);

            $stmt = $pdo->prepare("INSERT INTO referral_commissions (user_id, referred_user_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$referred_by, $user_id, $commission_amount]);

            // Also record a transaction for the referrer
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'referral', ?)");
            $stmt->execute([$referred_by, $commission_amount, "Referral commission from " . $_SESSION['username']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
        exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
    }

    $ad_id = (int)$_POST['ad_id'];

    // Get user membership info and daily limit
    $stmt = $pdo->prepare("SELECT m.daily_ads, m.ad_reward FROM memberships m JOIN users u ON u.membership_id = m.id WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user_membership = $stmt->fetch();
    $daily_limit = $user_membership['daily_ads'] ?? 10;
    $user_reward = $user_membership['ad_reward'] ?? 0.0100;

    // Check how many ads watched today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ? AND DATE(viewed_at) = CURDATE()");
    $stmt->execute([$user_id]);
    $watched_today = $stmt->fetchColumn();

    if ($watched_today >= $daily_limit) {
        echo json_encode(['success' => false, 'message' => 'Daily limit reached. Upgrade to watch more!']);
        exit();
    }

    // Verify ad and daily limit again (Server-side validation)
    $stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND status = 'active'");
    $stmt->execute([$ad_id]);
    $ad = $stmt->fetch();

    if (!$ad) {
        echo json_encode(['success' => false, 'message' => 'Invalid ad']);
        exit();
    }

    // Check if already watched (Lifetime restriction)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE user_id = ? AND ad_id = ?");
    $stmt->execute([$user_id, $ad_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already completed this task.']);
        exit();
    }

    // Reward user
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO ad_views (user_id, ad_id, reward_earned) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $ad_id, $user_reward]);

        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?");
        $stmt->execute([$user_reward, $user_reward, $user_id]);

        // Referral commission
        $stmt = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $referred_by = $stmt->fetchColumn();

        if ($referred_by) {
            $commission_percent = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'referral_commission'")->fetchColumn();
            $commission_amount = $user_reward * ($commission_percent / 100);
            
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?");
            $stmt->execute([$commission_amount, $commission_amount, $referred_by]);

            $stmt = $pdo->prepare("INSERT INTO referral_commissions (user_id, referred_user_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$referred_by, $user_id, $commission_amount]);

            // Also record a transaction for the referrer
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'referral', ?)");
            $stmt->execute([$referred_by, $commission_amount, "Referral commission from " . $_SESSION['username']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
