<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Use __DIR__ to ensure correct path resolution regardless of execution context
require_once __DIR__ . '/../config/database.php';

echo "<h2>Admin Debug & Reset Tool</h2>";

// 1. Check Database Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p style='color:green'>Database Connected Successfully.</p>";

// 2. Reset Password
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE admins SET password = '$hashed_password' WHERE username = '$username'";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "<p style='color:green'>Password for 'admin' RESET to: <strong>admin123</strong></p>";
    } else {
        echo "<p style='color:orange'>User 'admin' found, but password was already correct (or no change made).</p>";
    }
} else {
    echo "<p style='color:red'>Error updating password: " . $conn->error . "</p>";
}

// 3. List Admins
echo "<h3>Existing Admins:</h3><ul>";
$result = $conn->query("SELECT id, username, password FROM admins");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: " . $row['id'] . " | User: " . $row['username'] . " | Hash: " . substr($row['password'], 0, 10) . "...</li>";
    }
} else {
    echo "<li>No admins found! (Creating one now...)</li>";
    $conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$hashed_password')");
    echo "<li>Admin 'admin' created.</li>";
}
echo "</ul>";

// 4. Test Password Verify
if (password_verify('admin123', $hashed_password)) {
    echo "<p style='color:green'>Self-check: The script's generated hash matches 'admin123'.</p>";
} else {
    echo "<p style='color:red'>Self-check: Hash mismatch!</p>";
}

$conn->close();
?>