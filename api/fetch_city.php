<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$cursor = $conn->cities->find();
$cities = [];

foreach ($cursor as $row) {
    $row['_id'] = (string)$row['_id'];
    // If the frontend expects 'id' instead of '_id', we can add it
    $row['id'] = $row['_id'];
    $cities[] = $row;
}

echo json_encode($cities);
?>
