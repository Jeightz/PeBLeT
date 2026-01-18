<?php
session_start();
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);

include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success"=>false, "message"=>"Invalid request"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success"=>false, "message"=>"No data received"]);
    exit;
}

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password){
    echo json_encode(["success"=>false, "message"=>"Missing credentials"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password FROM userlogin WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
    echo json_encode(["success"=>false, "message"=>"User not found"]);
    exit;
}

$user = $res->fetch_assoc();
// Hash the input password with SHA256 before verifying
$sha256_password = hash('sha256', $password);
if(!password_verify($sha256_password, $user['password'])){
    echo json_encode(["success"=>false, "message"=>"Invalid password"]);
    exit;
}

$_SESSION['userlogin_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

$stmt = $conn->prepare("SELECT role FROM userinfo WHERE userlogin_id=?");
$stmt->bind_param("s", $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$role = "user";
if($res->num_rows > 0){
    $role = $res->fetch_assoc()['role'];
}

echo json_encode([
    "success"=>true,
    "message"=>"Login successful",
    "role"=>$role,
    "redirect"=>($role==="admin")?"admin/dashboard.html":"loans.html"
]);
?>