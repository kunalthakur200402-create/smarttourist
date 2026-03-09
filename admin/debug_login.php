<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Use __DIR__ to ensure correct path resolution regardless of execution context
require_once __DIR__ . '/../config/database.php';

echo "<h2>Admin Debug & Reset Tool</h2>";

// 1. Check Database Connection
try {
    $conn->command(['ping' => 1]);
    echo "<p style='color:green'>Database Connected Successfully (Ping OK).</p>";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// 2. Reset Password
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$update = $conn->admins->updateOne(
    ['username' => $username],
    ['$set' => ['password' => $hashed_password]]
);

if ($update->getModifiedCount() > 0) {
    echo "<p style='color:green'>Password for 'admin' RESET to: <strong>admin123</strong></p>";
} else {
    echo "<p style='color:orange'>User 'admin' found, but password was already correct (or no change made), or user doesn't exist.</p>";
}

// 3. List Admins
echo "<h3>Existing Admins:</h3><ul>";
$admins = $conn->admins->find();
$count = 0;
foreach ($admins as $row) {
    $count++;
    echo "<li>ID: " . (string)$row['_id'] . " | User: " . $row['username'] . " | Hash: " . substr($row['password'], 0, 10) . "...</li>";
}

if ($count == 0) {
    echo "<li>No admins found! (Creating one now...)</li>";
    $conn->admins->insertOne(['username' => 'admin', 'password' => $hashed_password]);
    echo "<li>Admin 'admin' created.</li>";
}
echo "</ul>";

// 4. Test Password Verify
if (password_verify('admin123', $hashed_password)) {
    echo "<p style='color:green'>Self-check: The script's generated hash matches 'admin123'.</p>";
} else {
    echo "<p style='color:red'>Self-check: Hash mismatch!</p>";
}
?>