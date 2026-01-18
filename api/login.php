<?php
include "config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$email = $data['username'];
$password = $data['password'];

if ($email === "test@gmail.com" && $password === "123456") {
    echo json_encode([
        "success" => true,
        "message" => "Login successful"
    ]);
    exit;
} 

echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);
exit;
?>
