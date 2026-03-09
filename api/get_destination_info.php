<?php
include_once(__DIR__ . "/../config/config.php");

function get_amadeus_token($client_id, $client_secret) {
    $ch = curl_init("https://api.amadeus.com/v1/security/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    $auth = base64_encode("$client_id:$client_secret");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded",
        "Authorization: Basic $auth"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        return $data['access_token'];
    }
    
    // Try test environment as fallback
    $ch = curl_init("https://test.api.amadeus.com/v1/security/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded",
        "Authorization: Basic $auth"
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $data = json_decode($response, true);
        return ["token" => $data['access_token'], "env" => "test"];
    }

    return null;
}

function get_pois($token, $lat, $lng, $env = "prod") {
    $base_url = ($env === "prod") ? "https://api.amadeus.com" : "https://test.api.amadeus.com";
    $url = "$base_url/v1/reference-data/locations/pois?latitude=$lat&longitude=$lng&radius=2&page[limit]=10";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $data = json_decode($response, true);
        return $data['data'] ?? [];
    }
    return [];
}

// If called directly via AJAX
if (basename($_SERVER['PHP_SELF']) == 'get_destination_info.php') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);
    $lat = $data['lat'] ?? null;
    $lng = $data['lng'] ?? null;

    if (!$lat || !$lng) {
        echo json_encode(["error" => "Coordinates required"]);
        exit;
    }

    $apiKeyString = base64_decode(AMADEUS_API_KEY);
    if ($apiKeyString === false || strpos($apiKeyString, ':') === false) {
        $apiKeyString = AMADEUS_API_KEY;
    }

    if (strpos($apiKeyString, ':') !== false) {
        list($client_id, $client_secret) = explode(':', $apiKeyString);
        $tokenData = get_amadeus_token($client_id, $client_secret);
        
        if ($tokenData) {
            $token = is_array($tokenData) ? $tokenData['token'] : $tokenData;
            $env = is_array($tokenData) ? $tokenData['env'] : "prod";
            $pois = get_pois($token, $lat, $lng, $env);
            echo json_encode(["pois" => $pois]);
            exit;
        }
    }
    echo json_encode(["error" => "Amadeus authentication failed", "pois" => []]);
}
?>
