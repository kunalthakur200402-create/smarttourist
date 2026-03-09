<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: ../auth/login.php?error=All fields are required");
        exit();
    }

    $admin = $conn->admins->findOne(['username' => $username]);

    if ($admin) {
        $hashed_password = $admin['password'];
        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = (string)$admin['_id'];
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: ../auth/login.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: ../auth/login.php?error=Invalid username");
        exit();
    }
} else {
    header("Location: ../auth/login.php");
    exit();
}
$conn->close();
?>