<?php

header("Content-Type: application/json; charset=UTF-8");

echo json_encode([
    "success" => true,
    "message" => "TravelFood OTP API is running",
    "endpoints" => [
        "send_otp" => "/auth/send-otp.php",
        "reset_password" => "/auth/reset-password.php"
    ]
]);