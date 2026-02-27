<?php
require_once '../config/database.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE admins SET password = '$hashed_password' WHERE username = '$username'";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "Password updated successfully for user 'admin'. New password: admin123";
    } else {
        // User might not exist, let's create it
        $sql_insert = "INSERT INTO admins (username, password) VALUES ('$username', '$hashed_password')";
        if ($conn->query($sql_insert) === TRUE) {
            echo "User 'admin' created with password: admin123";
        } else {
            echo "Error creating user: " . $conn->error;
        }
    }
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>