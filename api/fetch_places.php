<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$city_id = isset($_GET['city_id']) ? $_GET['city_id'] : '';

$cursor = $conn->places->find(['city_id' => $city_id]);
$places = [];

foreach ($cursor as $row) {
    $row['_id'] = (string)$row['_id'];
    $row['id'] = $row['_id'];
    $places[] = $row;
}

echo json_encode($places);
?>
