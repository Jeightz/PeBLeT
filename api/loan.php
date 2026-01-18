<?php
header("Content-Type: application/json");
include "../config/database.php";

$method = $_SERVER['REQUEST_METHOD'];

$data = json_decode(file_get_contents("php://input"), true);

if($method === 'POST') {
    $loan_id = uniqid('loan_');
    $user_id = $data['user_id'] ?? '';
    $loan_name = $data['loan_name'] ?? '';
    $amount = $data['amount'] ?? 0;
    $status = $data['status'] ?? 'pending';

    $stmt = $conn->prepare("INSERT INTO loans (loan_id, user_id, loan_name, amount, status) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $loan_id, $user_id, $loan_name, $amount, $status);
    $stmt->execute();

    echo json_encode(["success"=>true,"message"=>"Loan added"]);
    exit;
}

if($method === 'GET') {
    $result = $conn->query("SELECT l.loan_id, u.name as user_name, l.loan_name, l.amount, l.status, l.created_at FROM loans l JOIN userinfo u ON l.user_id = u.user_id");
    $loans = [];
    while($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
    echo json_encode($loans);
    exit;
}

if($method === 'PUT') {
    $loan_id = $data['loan_id'] ?? '';
    $loan_name = $data['loan_name'] ?? '';
    $amount = $data['amount'] ?? '';
    $status = $data['status'] ?? '';

    $stmt = $conn->prepare("UPDATE loans SET loan_name=?, amount=?, status=? WHERE loan_id=?");
    $stmt->bind_param("ssss", $loan_name, $amount, $status, $loan_id);
    $stmt->execute();

    echo json_encode(["success"=>true,"message"=>"Loan updated"]);
    exit;
}

if($method === 'DELETE') {
    $loan_id = $data['loan_id'] ?? '';
    $stmt = $conn->prepare("DELETE FROM loans WHERE loan_id=?");
    $stmt->bind_param("s", $loan_id);
    $stmt->execute();

    echo json_encode(["success"=>true,"message"=>"Loan deleted"]);
    exit;
}
?>
