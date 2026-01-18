<?php
// Simple error logging
ini_set('log_errors', 1);
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");

try {
    // Include database
    if (!file_exists("../config/database.php")) {
        throw new Exception("Database config file not found");
    }
    
    require_once "../config/database.php";
    
    if (!isset($conn)) {
        throw new Exception("Database connection not initialized");
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    // GET - Fetch all loans
    if($method === 'GET') {
        // First, let's check what columns exist in loans table
        $columns_check = $conn->query("SHOW COLUMNS FROM loans");
        $columns = array();
        while($col = $columns_check->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        
        // Check which ID column exists
        $has_user_id = in_array('user_id', $columns);
        $has_userlogin_id = in_array('userlogin_id', $columns);
        
        if ($has_userlogin_id) {
            // Join with userlogin table using userlogin_id
            $sql = "SELECT l.loan_id, ul.username as user_name, l.loan_name, l.amount, l.status, l.created_at 
                    FROM loans l 
                    LEFT JOIN userlogin ul ON l.userlogin_id = ul.id";
        } else if ($has_user_id) {
            // Join with userinfo table using user_id
            $sql = "SELECT l.loan_id, u.name as user_name, l.loan_name, l.amount, l.status, l.created_at 
                    FROM loans l 
                    LEFT JOIN userinfo u ON l.user_id = u.user_id";
        } else {
            // Just select from loans table without JOIN
            $sql = "SELECT loan_id, loan_name, amount, status, created_at FROM loans";
        }
        
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $loans = array();
        while($row = $result->fetch_assoc()) {
            // Add user_name as 'N/A' if not present
            if (!isset($row['user_name'])) {
                $row['user_name'] = 'N/A';
            }
            $loans[] = $row;
        }
        
        echo json_encode($loans);
        exit;
    }
    
    // POST - Add new loan
    if($method === 'POST') {
        if (!$data) {
            throw new Exception("No data received");
        }
        
        $loan_id = uniqid('loan_');
        // Check if using user_id or userlogin_id
        $user_id = isset($data['user_id']) ? $data['user_id'] : '';
        $userlogin_id = isset($data['userlogin_id']) ? $data['userlogin_id'] : $user_id;
        $loan_name = isset($data['loan_name']) ? $data['loan_name'] : '';
        $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
        $status = isset($data['status']) ? $data['status'] : 'pending';
        
        if (empty($userlogin_id) || empty($loan_name) || $amount <= 0) {
            throw new Exception("Missing required fields");
        }
        
        // Check which column exists in loans table
        $columns_check = $conn->query("SHOW COLUMNS FROM loans");
        $has_userlogin_id = false;
        while($col = $columns_check->fetch_assoc()) {
            if ($col['Field'] === 'userlogin_id') {
                $has_userlogin_id = true;
                break;
            }
        }
        
        if ($has_userlogin_id) {
            $stmt = $conn->prepare("INSERT INTO loans (loan_id, userlogin_id, loan_name, amount, status) VALUES (?, ?, ?, ?, ?)");
        } else {
            $stmt = $conn->prepare("INSERT INTO loans (loan_id, user_id, loan_name, amount, status) VALUES (?, ?, ?, ?, ?)");
        }
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssds", $loan_id, $userlogin_id, $loan_name, $amount, $status);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        echo json_encode(["success" => true, "message" => "Loan added successfully"]);
        exit;
    }
    
    // PUT - Update loan
    if($method === 'PUT') {
        if (!$data) {
            throw new Exception("No data received");
        }
        
        $loan_id = isset($data['loan_id']) ? $data['loan_id'] : '';
        $loan_name = isset($data['loan_name']) ? $data['loan_name'] : '';
        $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
        $status = isset($data['status']) ? $data['status'] : '';
        
        if (empty($loan_id) || empty($loan_name) || $amount <= 0 || empty($status)) {
            throw new Exception("Missing required fields");
        }
        
        $stmt = $conn->prepare("UPDATE loans SET loan_name = ?, amount = ?, status = ? WHERE loan_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sdss", $loan_name, $amount, $status, $loan_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        echo json_encode(["success" => true, "message" => "Loan updated successfully"]);
        exit;
    }
    
    // DELETE - Delete loan
    if($method === 'DELETE') {
        if (!$data) {
            throw new Exception("No data received");
        }
        
        $loan_id = isset($data['loan_id']) ? $data['loan_id'] : '';
        
        if (empty($loan_id)) {
            throw new Exception("Loan ID required");
        }
        
        $stmt = $conn->prepare("DELETE FROM loans WHERE loan_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $loan_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        echo json_encode(["success" => true, "message" => "Loan deleted successfully"]);
        exit;
    }
    
    // Invalid method
    throw new Exception("Invalid request method: " . $method);
    
} catch (Exception $e) {
    http_response_code(200); // Still send 200 so JavaScript can parse JSON
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
    exit;
}
?>