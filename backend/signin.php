<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL query to prevent SQL injection
    $sql = "SELECT * FROM students WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // User found, verify password
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            
            // Redirect to dashboard or home page
            header("Location: ../frontend/index.html?success=signin");
            exit;
        } else {
            // Password is incorrect
            header("Location: ../frontend/signin.html?error=invalid_credentials");
            exit;
        }
    } else {

        // User not found
        header("Location: ../frontend/signin.html?error=user_not_found");
        exit;
    }
}

// If accessed directly without POST
header("Location: ../frontend/signin.html");
exit;
?>





