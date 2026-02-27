<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$city = isset($_GET['city']) ? $_GET['city'] : '';

if ($city) {
    if (OPENWEATHER_API_KEY === 'YOUR_OPENWEATHER_API_KEY') {
        echo json_encode(['error' => 'API Key not configured']);
        exit;
    }

    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . OPENWEATHER_API_KEY . "&units=metric";

    $response = file_get_contents($apiUrl);

    if ($response === FALSE) {
        echo json_encode(['error' => 'Failed to fetch weather data']);
    } else {
        echo $response;
    }
} else {
    echo json_encode(['error' => 'No city provided']);
}
?>