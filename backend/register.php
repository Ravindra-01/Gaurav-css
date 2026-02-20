<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $uid    = $_POST['uid'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO students(name,email, uid, password) VALUES ('$name', '$email', '$uid', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../frontend/index.html?success=registered");
        exit;
    } else {
        header("Location: ../frontend/index.html?error=registration_failed");
        exit;
    }
}   

?>