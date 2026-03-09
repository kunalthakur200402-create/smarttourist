<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$city = isset($_GET['city']) ? $_GET['city'] : '';

if ($city) {
    if (OPENWEATHER_API_KEY === 'YOUR_OPENWEATHER_API_KEY' || !defined('OPENWEATHER_API_KEY')) {
        echo json_encode(['error' => 'Weather API Key not configured']);
        exit;
    }

    // Check if the key provided is actually a Gemini key (incorrect for this endpoint)
    if (strpos(OPENWEATHER_API_KEY, 'gen-lang-') === 0) {
        echo json_encode(['error' => 'Mismatch: A Google Gemini key was provided for the OpenWeatherMap service. Please provide a valid OpenWeatherMap API key.']);
        exit;
    }

    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . OPENWEATHER_API_KEY . "&units=metric";

    $response = @file_get_contents($apiUrl);

    if ($response === FALSE) {
        echo json_encode(['error' => 'Failed to fetch weather data. The API key might be invalid or expired.']);
    } else {
        echo $response;
    }
} else {
    echo json_encode(['error' => 'No city provided']);
}
?>