<?php
/**
 * PHPMailer Utility for Real Email Delivery
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

/**
 * Sends an OTP email using PHPMailer via Gmail SMTP
 */
function sendOTPEmail($to, $otp)
{
    $smtp_user = getenv('MAIL_USERNAME') ?: 'your_email@gmail.com';
    $smtp_pass = getenv('MAIL_PASSWORD') ?: '';
    $from_name = getenv('MAIL_FROM_NAME') ?: 'College Admission System';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_user;
        $mail->Password = $smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port = 465;

        // Recipients
        $mail->setFrom($smtp_user, $from_name);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your Verification Code - College Admission System";

        $mail->Body = "
        <html>
        <head>
            <style>
                .container { font-family: 'Inter', sans-serif; max-width: 600px; margin: 0 auto; padding: 40px; background: #f8fafc; border-radius: 24px; color: #0f172a; }
                .logo { font-weight: 800; font-size: 24px; color: #bef264; margin-bottom: 24px; background: #0f172a; display: inline-block; padding: 8px 16px; border-radius: 12px; }
                .otp { font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #2563eb; margin: 32px 0; }
                .footer { font-size: 13px; color: #64748b; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>AS</div>
                <h1>Email Verification</h1>
                <p>Welcome to the College Counselling System. Use the code below to verify your account. This code is valid for 10 minutes.</p>
                <div class='otp'>$otp</div>
                <p>If you didn't request this code, you can safely ignore this email.</p>
                <div class='footer'>
                    &copy; " . date('Y') . " College Admission System. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();

        // Still log for fallback/debugging
        $mail_log = "To: $to\nSubject: PHPMailer Sent\nOTP: $otp\n---\n";
        file_put_contents(__DIR__ . '/../../otp_mail_log.txt', $mail_log, FILE_APPEND);

        return true;
    }
    catch (Exception $e) {
        // Log failure
        $mail_log = "To: $to\nSubject: PHPMailer FAILED\nError: {$mail->ErrorInfo}\n---\n";
        file_put_contents(__DIR__ . '/../../otp_mail_log.txt', $mail_log, FILE_APPEND);
        return false;
    }
}
