<?php 
$username = "root";
$serverhost = "localhost";
$password = "";
$dbname = "peb_let";

$conn = new mysqli($serverhost,$username,$password,$dbname);

if($conn-> connect_error){
  die("failed to connect ".$conn-> connect_error);
}



?>