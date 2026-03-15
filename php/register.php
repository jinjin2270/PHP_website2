<?php
require_once("../config/db.php");

// Check if POST data is set
if (
    isset($_POST['name']) && 
    isset($_POST['email']) && 
    isset($_POST['password']) && 
    isset($_POST['confirmPassword'])
) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Email already exists.";
        exit;
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Registration failed.";
    }

    $stmt->close();
    $check->close();
    $conn->close();
} else {
    echo "All fields are required.";
}
?>
