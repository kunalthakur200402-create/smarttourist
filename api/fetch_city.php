<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$result = $conn->query("SELECT * FROM cities");
$cities = [];

while ($row = $result->fetch_assoc()) {
    $cities[] = $row;
}

echo json_encode($cities);
?>
