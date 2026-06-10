<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../utils/mail.php";

loadEnv(__DIR__ . "/../.env");

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");

if ($email === "") {
    echo json_encode([
        "success" => false,
        "message" => "Email không được để trống"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Email không hợp lệ"
    ]);
    exit;
}

$otpLength = (int) env("OTP_LENGTH", 6);
$expireMinutes = (int) env("OTP_EXPIRE_MINUTES", 5);

$min = (int) str_pad("1", $otpLength, "0");
$max = (int) str_repeat("9", $otpLength);

$otp = (string) random_int($min, $max);

$otpDir = __DIR__ . "/../storage";

if (!is_dir($otpDir) && !mkdir($otpDir, 0777, true) && !is_dir($otpDir)) {
    error_log("Không thể tạo thư mục lưu OTP.");
    echo json_encode([
        "success" => false,
        "message" => "Không gửi được email OTP"
    ]);
    exit;
}

$file = $otpDir . "/otps.json";

$sent = sendOtpMail($email, $otp);

if (!$sent) {
    echo json_encode([
        "success" => false,
        "message" => "Không gửi được email OTP"
    ]);
    exit;
}

$otps = [];

if (file_exists($file)) {
    $content = file_get_contents($file);
    $otps = json_decode($content, true) ?: [];
}

$otps[$email] = [
    "otp" => $otp,
    "expired_at" => time() + ($expireMinutes * 60)
];

$saved = file_put_contents(
    $file,
    json_encode($otps, JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($saved === false) {
    error_log("Không thể lưu OTP sau khi gửi email.");
    echo json_encode([
        "success" => false,
        "message" => "Không gửi được email OTP"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Đã gửi OTP về email"
]);
