<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $uid      = $_POST['uid'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Check if email or uid already exists
    $check = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ? OR uid = ?");
    mysqli_stmt_bind_param($check, "ss", $email, $uid);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        // Email or UID already registered
        header("Location: /index.html?error=already_exists");
        exit;
    }

    // ✅ Safe prepared statement — no SQL injection
    $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, uid, password) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $uid, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        // ✅ Fixed: added space after "Location:"
        header("Location: /index.html?success=registered");
        exit;
    } else {
        header("Location: /index.html?error=registration_failed");
        exit;
    }
}

// If accessed directly without POST
header("Location: /index.html");
exit;
?>