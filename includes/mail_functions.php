<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config/config.php';

// Download PHPMailer if not exists (Simplified for this environment)
// Note: In a real production environment, you should use Composer.
// This is a wrapper function to send emails using SMTP.

function sendMail($to, $subject, $body) {
    global $pdo;
    
    // Fetch SMTP settings from DB
    $smtp_host = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'smtp_host'")->fetchColumn() ?: 'smtp.gmail.com';
    $smtp_user = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'smtp_user'")->fetchColumn() ?: '';
    $smtp_pass = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'smtp_pass'")->fetchColumn() ?: '';
    $smtp_port = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'smtp_port'")->fetchColumn() ?: 587;
    
    // Manual include of PHPMailer
    require_once __DIR__ . '/PHPMailer/Exception.php';
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;

        // Recipients
        $mail->setFrom($smtp_user, SITE_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
