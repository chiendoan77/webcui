<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';

loadEnv(__DIR__ . '/../.env');

function sendOtpMail(
    string $toEmail,
    string $otp
): bool {
    $apiKey = trim((string) env('RESEND_API_KEY'));
    $from = trim((string) env(
        'RESEND_FROM',
        'TravelFood <onboarding@resend.dev>'
    ));
    $expireMinutes = (int) env('OTP_EXPIRE_MINUTES', 5);

    if (
        !str_starts_with($apiKey, 're_')
        || $from === ''
        || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)
        || $expireMinutes < 1
    ) {
        error_log('Resend configuration or recipient email is invalid.');
        return false;
    }

    try {
        $resend = Resend::client($apiKey);
        $resend->emails->send([
            'from' => $from,
            'to' => [$toEmail],
            'subject' => 'Mã OTP đặt lại mật khẩu TravelFood',
            'html' => sprintf(
                '<p>Mã OTP của bạn là: <strong>%s</strong></p>'
                . '<p>OTP có hiệu lực trong %d phút.</p>',
                htmlspecialchars($otp, ENT_QUOTES, 'UTF-8'),
                $expireMinutes
            ),
            'text' => "Mã OTP của bạn là: $otp\n\n"
                . "OTP có hiệu lực trong $expireMinutes phút.",
        ]);

        return true;
    } catch (\Throwable $e) {
        error_log('Resend send failed: ' . $e->getMessage());
        return false;
    }
}
