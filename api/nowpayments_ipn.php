<?php
require_once __DIR__ . '/../config/config.php';

function check_ipn_request() {
    if (!isset($_SERVER['HTTP_X_NOWPAYMENTS_SIG'])) {
        return false;
    }

    $received_hmac = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'];
    $request_json = file_get_contents('php://input');
    $request_data = json_decode($request_json, true);

    ksort($request_data);
    $sorted_request_json = json_encode($request_data, JSON_UNESCAPED_SLASHES);

    if ($sorted_request_json !== false && !empty($sorted_request_json)) {
        $hmac = hash_hmac('sha512', $sorted_request_json, NOWPAYMENTS_IPN_SECRET);
        if ($hmac == $received_hmac) {
            return $request_data;
        }
    }
    return false;
}

$request_data = check_ipn_request();

if ($request_data) {
    $payment_status = $request_data['payment_status'];
    $order_id = $request_data['order_id'];
    $price_amount = $request_data['price_amount'];
    
    if ($payment_status == 'finished' || $payment_status == 'confirmed') {
        // Logic to process upgrade based on order_id (e.g. "upgrade_user_1_plan_2")
        if (strpos($order_id, 'upgrade_') === 0) {
            $parts = explode('_', $order_id);
            $user_id = (int)$parts[2];
            $plan_id = (int)$parts[4];

            $stmt = $pdo->prepare("SELECT * FROM memberships WHERE id = ?");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch();

            if ($plan) {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("UPDATE users SET membership_id = ?, membership_expires_at = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?");
                    $stmt->execute([$plan_id, $plan['duration_days'], $user_id]);

                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'upgrade', ?)");
                    $stmt->execute([$user_id, $price_amount, "Upgraded to " . $plan['name'] . " Plan via Crypto"]);

                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                }
            }
        }
    }
}
?>
