<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$otp = trim($data["otp"] ?? "");
$newPasswordHash = trim($data["newPasswordHash"] ?? "");

if ($email === "" || $otp === "" || $newPasswordHash === "") {
    echo json_encode([
        "success" => false,
        "message" => "Thiếu email, OTP hoặc mật khẩu mới"
    ]);
    exit;
}

try {
    $sql = "SELECT TOP 1 *
            FROM password_otps
            WHERE email = :email
            AND otp = :otp
            AND is_used = 0
            AND expired_at >= GETDATE()
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":email" => $email,
        ":otp" => $otp
    ]);

    $otpRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$otpRow) {
        echo json_encode([
            "success" => false,
            "message" => "OTP không đúng hoặc đã hết hạn"
        ]);
        exit;
    }

    $updateSql = "UPDATE password_otps
                  SET is_used = 1
                  WHERE id = :id";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute([
        ":id" => $otpRow["id"]
    ]);

    echo json_encode([
        "success" => true,
        "message" => "OTP hợp lệ, có thể đổi mật khẩu"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Lỗi xác thực OTP",
        "error" => $e->getMessage()
    ]);
}