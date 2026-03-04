<?php
function getEmailTemplate($title, $username, $otp, $message, $is_activity = false) {
    $siteName = defined('SITE_NAME') ? SITE_NAME : 'F Earning';
    
    $otp_section = "";
    if (!$is_activity && $otp) {
        $otp_section = "
        <div class='otp-container'>
            <h2 style='font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;'>Your Verification Code</h2>
            <div class='otp-code'>$otp</div>
            <div class='expiry-text'>Valid for 15 minutes</div>
        </div>";
    }

    $footer_note = $is_activity ? "" : "<p style='color: #64748b; font-size: 14px; line-height: 1.6;'>If you did not request this code, please ignore this email or contact support if you have concerns.</p>";

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            .email-container {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 600px;
                margin: 0 auto;
                background-color: #f8fafc;
                padding: 40px 20px;
            }
            .email-content {
                background-color: #ffffff;
                border-radius: 16px;
                padding: 40px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                border: 1px solid #e2e8f0;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .logo-text {
                font-size: 24px;
                font-weight: 800;
                color: #6366f1;
                margin: 0;
            }
            .welcome-text {
                font-size: 18px;
                color: #1e293b;
                margin-bottom: 20px;
            }
            .otp-container {
                background-color: #f1f5f9;
                border-radius: 12px;
                padding: 30px;
                text-align: center;
                margin: 30px 0;
            }
            .otp-code {
                font-size: 36px;
                font-weight: 800;
                color: #6366f1;
                letter-spacing: 8px;
                margin: 0;
            }
            .expiry-text {
                font-size: 14px;
                color: #64748b;
                margin-top: 10px;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                font-size: 12px;
                color: #94a3b8;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background-color: #6366f1;
                color: white !important;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 700;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-content'>
                <div class='header'>
                    <h1 class='logo-text'>$siteName</h1>
                </div>
                <div class='welcome-text'>Hello <strong>$username</strong>,</div>
                <p style='color: #475569; line-height: 1.6;'>$message</p>
                
                $otp_section
                
                $footer_note
                
                <div style='border-top: 1px solid #e2e8f0; margin-top: 30px; padding-top: 20px; text-align: center;'>
                    <p style='margin: 0; color: #1e293b; font-weight: 600;'>The $siteName Team</p>
                </div>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " $siteName Platform. All rights reserved.
            </div>
        </div>
    </body>
    </html>";
}
?>
