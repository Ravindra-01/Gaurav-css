<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = $_POST['name'];
    $email   = $_POST['email'];
    $phone   = $_POST['phone'];
    $class   = $_POST['class'];
    $message = $_POST['message'];

    // ✅ Safe prepared statement — no SQL injection
    $stmt = mysqli_prepare($conn, "INSERT INTO enquiry (name, email, phone, class, message) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $class, $message);

    if (mysqli_stmt_execute($stmt)) {
        // ✅ Fixed: added space after "Location:"
        header("Location: /index.html?success=enquiry_submitted");
        exit;
    } else {
        header("Location: /index.html?error=enquiry_failed");
        exit;
    }
}

// If accessed directly without POST
header("Location: /index.html");
exit;
?>