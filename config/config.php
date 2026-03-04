<?php
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_41300929');
define('DB_PASS', 'Surjo253692');
define('DB_NAME', 'if0_41300929_f_earning_db');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();

// Fetch global settings
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
define('SITE_NAME', $settings['site_name'] ?? 'F Earning');
define('SITE_LOGO', !empty($settings['site_logo']) ? $settings['site_logo'] : '');
define('SITE_FAVICON', !empty($settings['site_favicon']) ? $settings['site_favicon'] : '');

// NowPayments Configuration
define('NOWPAYMENTS_API_KEY', 'M0MY75T-7DCMARC-HQTWZ9G-5DKF2EY');
define('NOWPAYMENTS_IPN_SECRET', 'ynMUqfK5U2j/XckpuqbvpBGz24pX5QFZ');
define('NOWPAYMENTS_PUBLIC_KEY', 'ed267e9d-8781-4823-a8a0-3c8624f28ac6');

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirect($path) {
    header("Location: " . $path);
    exit();
}
?>
