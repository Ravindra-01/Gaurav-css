<?php
// Database connection
$servername = "localhost";
$username = "root";      // default for XAMPP
$password = "";          // default for XAMPP
$database = "cluny_convent_school";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$uid = $_POST['uid'];
$pass = $_POST['password'];

// Encrypt password (important!)
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

// Insert into database
$sql = "INSERT INTO students (name, email, uid, password)
        VALUES ('$name', '$email', '$uid', '$hashed_password')";

if ($conn->query($sql) === TRUE) {
    echo "Registration successful 🎉";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
