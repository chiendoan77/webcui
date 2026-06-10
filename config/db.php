<?php

require_once __DIR__ . "/env.php";

loadEnv(__DIR__ . "/../.env");

$serverName = $_ENV["DB_SERVER"];
$database = $_ENV["DB_NAME"];
$username = $_ENV["DB_USER"];
$password = $_ENV["DB_PASS"];

try {
    $conn = new PDO(
        "sqlsrv:server=$serverName;Database=$database;TrustServerCertificate=true",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Kết nối SQL Server thất bại",
        "error" => $e->getMessage()
    ]);
    exit;
}