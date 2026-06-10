<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/db.php";
require_once "../utils/mail.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");

if ($email === "") {
    echo json_encode([
        "success" => false,
        "message" => "Email không được để trống"
    ]);
    exit;
}

$otp = random_int(100000, 999999);
$expiredAt = date("Y-m-d H:i:s", time() + 5 * 60);

try {
    $sql = "INSERT INTO password_otps (email, otp, expired_at, is_used)
            VALUES (:email, :otp, :expired_at, 0)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":email" => $email,
        ":otp" => $otp,
        ":expired_at" => $expiredAt
    ]);

    $sent = sendOtpMail($email, $otp);

    if (!$sent) {
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

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Lỗi gửi OTP",
        "error" => $e->getMessage()
    ]);
}