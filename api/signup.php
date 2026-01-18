<?php
session_start();
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);

include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success"=>false, "message"=>"Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if(!$data){
    echo json_encode(["success"=>false, "message"=>"No data received"]);
    exit;
}

$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirmpassword = $data['confirmpassword'] ?? '';

if (!$username || !$email || !$password || !$confirmpassword){
    echo json_encode(["success"=>false, "message"=>"All fields required"]);
    exit;
}

if($password !== $confirmpassword){
    echo json_encode(["success"=>false, "message"=>"Passwords do not match"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success"=>false, "message"=>"Invalid email format"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM userinfo WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows > 0){
    echo json_encode(["success"=>false, "message"=>"Email already registered"]);
    exit;
}

$login_id = uniqid("login_");
// First hash with SHA256, then use password_hash for extra security
$sha256_password = hash('sha256', $password);
$hashed_password = password_hash($sha256_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO userlogin (id, username, password) VALUES (?,?,?)");
$stmt->bind_param("sss", $login_id, $username, $hashed_password);
$stmt->execute();

$user_id = uniqid("user_");
$role = "user";
$stmt = $conn->prepare("INSERT INTO userinfo (user_id, name, email, role, userlogin_id) VALUES (?,?,?,?,?)");
$stmt->bind_param("sssss", $user_id, $username, $email, $role, $login_id);
$stmt->execute();

echo json_encode(["success"=>true, "message"=>"Signup successful"]);
?>