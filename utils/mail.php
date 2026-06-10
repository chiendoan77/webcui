<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../config/env.php';

loadEnv(__DIR__ . '/../.env');

function sendOtpMail(
    string $toEmail,
    string $otp
): bool {

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host =
            $_ENV['MAIL_HOST'];

        $mail->SMTPAuth = true;

        $mail->Username =
            $_ENV['MAIL_FROM'];

        $mail->Password =
            $_ENV['MAIL_PASSWORD'];

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port =
            (int)$_ENV['MAIL_PORT'];

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            $_ENV['MAIL_FROM'],
            $_ENV['MAIL_FROM_NAME']
        );

        $mail->addAddress($toEmail);

        $mail->Subject =
            'Mã OTP đặt lại mật khẩu TravelFood';

        $mail->Body =
            "Mã OTP của bạn là: $otp\n\n"
            ."OTP có hiệu lực trong "
            .$_ENV['OTP_EXPIRE_MINUTES']
            ." phút.";

        return $mail->send();

    } catch (Exception $e) {

        error_log($e->getMessage());

        return false;
    }
}