<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "cluny_convent_school";

  $conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>