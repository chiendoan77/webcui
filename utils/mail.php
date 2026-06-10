<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';

loadEnv(__DIR__ . '/../.env');

function sendOtpMail(
    string $toEmail,
    string $otp
): bool {
    $host = trim((string) env('MAIL_HOST'));
    $port = (int) env('MAIL_PORT', 587);
    $from = trim((string) env('MAIL_FROM'));
    $password = (string) preg_replace('/\s+/', '', (string) env('MAIL_PASSWORD'));
    $fromName = trim((string) env('MAIL_FROM_NAME'));
    $expireMinutes = (int) env('OTP_EXPIRE_MINUTES', 5);

    if (
        $host === ''
        || $port < 1
        || $port > 65535
        || !filter_var($from, FILTER_VALIDATE_EMAIL)
        || $password === ''
        || $fromName === ''
    ) {
        error_log('SMTP configuration is missing or invalid.');
        return false;
    }

    if (strcasecmp($host, 'smtp.gmail.com') === 0 && strlen($password) !== 16) {
        error_log('Gmail SMTP requires a 16-character App Password.');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $from;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($from, $fromName);
        $mail->addAddress($toEmail);

        $mail->Subject = 'Mã OTP đặt lại mật khẩu TravelFood';
        $mail->Body = "Mã OTP của bạn là: $otp\n\n"
            . "OTP có hiệu lực trong $expireMinutes phút.";

        return $mail->send();
    } catch (\Throwable $e) {
        error_log('SMTP send failed: ' . $e->getMessage());
        return false;
    }
}
