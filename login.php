<?php
require_once "config/database.php";

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM logininfo WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    echo "User found";
} else {
    echo "Invalid login";
}
?>