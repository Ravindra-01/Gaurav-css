<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $class = $_POST['class'];
    $message  = $_POST['message'];

    $sql = "INSERT INTO enquiry(name,email, phone, class, message) VALUES ('$name', '$email', '$phone', '$class', '$message')";
    if (mysqli_query($conn, $sql)) {
        header("Location:../frontend/index.html?success=enquiry_submitted");
        exit;
    } else {
        header("Location:../frontend/index.html?error=enquiry_failed");
        exit;
    }
}   

?>