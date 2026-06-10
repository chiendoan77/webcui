<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/env.php";

loadEnv(__DIR__ . "/../.env");

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$otp = trim($data["otp"] ?? "");

if ($email === "" || $otp === "") {
    echo json_encode([
        "success" => false,
        "message" => "Thiếu email hoặc OTP"
    ]);
    exit;
}

$file = __DIR__ . "/../storage/otps.json";

if (!file_exists($file)) {
    echo json_encode([
        "success" => false,
        "message" => "OTP không tồn tại hoặc đã hết hạn"
    ]);
    exit;
}

$otps = json_decode(file_get_contents($file), true) ?: [];

if (!isset($otps[$email])) {
    echo json_encode([
        "success" => false,
        "message" => "OTP không tồn tại"
    ]);
    exit;
}

$item = $otps[$email];

if (time() > $item["expired_at"]) {
    unset($otps[$email]);
    file_put_contents($file, json_encode($otps));

    echo json_encode([
        "success" => false,
        "message" => "OTP đã hết hạn"
    ]);
    exit;
}

if ($item["otp"] !== $otp) {
    echo json_encode([
        "success" => false,
        "message" => "OTP không đúng"
    ]);
    exit;
}

unset($otps[$email]);
file_put_contents($file, json_encode($otps));

echo json_encode([
    "success" => true,
    "message" => "OTP hợp lệ"
]);