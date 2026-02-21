<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL query to prevent SQL injection
    $sql = "SELECT * FROM students WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            // Password correct — start session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email']   = $row['email'];
            $_SESSION['name']    = $row['name'];

            // ✅ Fixed path
            header("Location: /index.html?success=signin");
            exit;
        } else {
            // ✅ Fixed path
            header("Location: /signin.html?error=invalid_credentials");
            exit;
        }
    } else {
        // ✅ Fixed path
        header("Location: /signin.html?error=user_not_found");
        exit;
    }
}

// If accessed directly without POST
// ✅ Fixed path
header("Location: /signin.html");
exit;
?>