<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$city = $data['city'] ?? 'Unknown City';
$days = $data['days'] ?? 3;

$itinerary = [];
for ($i = 1; $i <= $days; $i++) {
    $itinerary[] = [
        "day" => $i,
        "activities" => [
            "Morning visit to popular landmarks in $city.",
            "Lunch at a local authentic restaurant.",
            "Evening walk and dinner at the city center."
        ]
    ];
}

echo json_encode(["itinerary" => $itinerary]);
?>
