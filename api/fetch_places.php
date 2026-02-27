<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;

$stmt = $conn->prepare("SELECT * FROM places WHERE city_id = ?");
$stmt->bind_param("i", $city_id);
$stmt->execute();
$result = $stmt->get_result();

$places = [];
while ($row = $result->fetch_assoc()) {
    $places[] = $row;
}

echo json_encode($places);
?>
