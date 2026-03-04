<?php
require_once __DIR__ . '/includes/user_functions.php';

$error_message = isset($_GET['msg']) ? sanitize($_GET['msg']) : 'An unexpected error occurred.';
$error_type = isset($_GET['type']) ? sanitize($_GET['type']) : 'error';

// Determine icon and title based on error type
$icon = '❌';
$title = 'Error Occurred';
if ($error_type === 'info') {
    $icon = 'ℹ️';
    $title = 'Information';
} elseif ($error_type === 'warning') {
    $icon = '⚠️';
    $title = 'Warning';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">
    <div class="container fade-in" style="max-width: 600px; margin-top: 100px; text-align: center;">
        <div class="card" style="padding: 50px; border-top: 5px solid <?php echo ($error_type === 'error' ? '#ef4444' : ($error_type === 'warning' ? '#f59e0b' : 'var(--primary-color)')); ?>;">
            <div style="font-size: 4rem; margin-bottom: 20px;"><?php echo $icon; ?></div>
            <h1 style="font-size: 2rem; margin-bottom: 15px; color: #1e293b;"><?php echo $title; ?></h1>
            <div style="background: rgba(0,0,0,0.02); padding: 20px; border-radius: 1rem; border: 1px solid rgba(0,0,0,0.05); margin-bottom: 30px;">
                <p style="font-size: 1.1rem; line-height: 1.6; color: #475569;"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="user/dashboard.php" class="btn btn-primary" style="padding: 12px 30px;">Go to Dashboard</a>
                <a href="javascript:history.back()" class="btn" style="padding: 12px 30px; background: #94a3b8; color: white;">Go Back</a>
            </div>
        </div>
        
        <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <div style="margin-top: 40px;">
            <?php echo $settings['ad_native_code'] ?? ''; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
