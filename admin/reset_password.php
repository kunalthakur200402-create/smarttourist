<?php
require_once '../config/database.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$update = $conn->admins->updateOne(
    ['username' => $username],
    ['$set' => ['password' => $hashed_password]]
);

if ($update->getModifiedCount() > 0) {
    echo "Password updated successfully for user 'admin'. New password: admin123";
} else {
    // Check if user exists first
    $user = $conn->admins->findOne(['username' => $username]);
    if (!$user) {
        $conn->admins->insertOne(['username' => $username, 'password' => $hashed_password]);
        echo "User 'admin' created with password: admin123";
    } else {
        echo "Password for 'admin' was already up to date.";
    }
}
?>