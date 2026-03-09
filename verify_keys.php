<?php
include("config/config.php");
header('Content-Type: text/plain');

echo "=== SmartGuide AI API Key Verification Tool ===\n\n";

// 1. Google Maps / Gemini
echo "[1] Google (GOOGLE_MAPS_API_KEY)\n";
$key = GOOGLE_MAPS_API_KEY;
// Test Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . $key;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["contents" => [["parts" => [["text" => "Hi"]]]]]));
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($code == 200) echo "    SUCCESS: Gemini 2.0 AI is active!\n";
else if ($code == 429) echo "    QUOTA EXCEEDED (429): Gemini 2.0 limit reached.\n";
else echo "    FAILED ($code): Gemini 2.0 check failed.\n";

// Test Gemini 1.5
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $key;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["contents" => [["parts" => [["text" => "Hi"]]]]]));
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($code == 200) echo "    SUCCESS: Gemini 1.5 AI is active!\n";
else if ($code == 429) echo "    QUOTA EXCEEDED (429): Gemini 1.5 limit reached.\n";
else echo "    FAILED ($code): Gemini 1.5 check failed.\n";

// Test Maps Geocoding
$url = "https://maps.googleapis.com/maps/api/geocode/json?address=London&key=" . $key;
$res = @file_get_contents($url);
if ($res) {
    $g = json_decode($res, true);
    if (($g['status'] ?? '') == 'OK') echo "    SUCCESS: Maps Geocoding is active!\n";
    else echo "    FAILED: Maps Geocoding returned status: " . ($g['status'] ?? 'UNKNOWN') . " (" . ($g['error_message'] ?? 'Check billing/restrictions') . ")\n";
}

// 2. OpenAI
echo "\n[2] OpenAI (OPENAI_API_KEY)\n";
$openai_key = OPENAI_API_KEY;
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Bearer $openai_key"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["model" => "gpt-4o-mini", "messages" => [["role"=>"user","content"=>"Hi"]]]));
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($code == 200) echo "    SUCCESS: OpenAI Chat is active!\n";
else if ($code == 429) echo "    QUOTA EXCEEDED (429): OpenAI limit/billing issues.\n";
else echo "    FAILED ($code): OpenAI Chat check failed.\n";

// 3. Amadeus
echo "\n[3] Amadeus (AMADEUS_API_KEY)\n";
include_once("api/get_destination_info.php");
$apiKeyString = base64_decode(AMADEUS_API_KEY);
if ($apiKeyString === false || strpos($apiKeyString, ':') === false) { $apiKeyString = AMADEUS_API_KEY; }
if (strpos($apiKeyString, ':') !== false) {
    list($cid, $sec) = explode(':', $apiKeyString);
    $token = get_amadeus_token($cid, $sec);
    if ($token) echo "    SUCCESS: Amadeus is active!\n";
    else echo "    FAILED: Invalid Client ID or Secret.\n";
} else {
    echo "    FAILED: Incorrect format. Should be 'id:secret'.\n";
}

// 4. OpenWeather
echo "\n[4] OpenWeather (OPENWEATHER_API_KEY)\n";
$weather_key = OPENWEATHER_API_KEY;
$url = "https://api.openweathermap.org/data/2.5/weather?q=London&appid=" . $weather_key;
$res = @file_get_contents($url);
if ($res) echo "    SUCCESS: OpenWeather is active!\n";
else echo "    FAILED: Likely invalid key (currently set to: " . substr($weather_key, 0, 10) . "...)\n";

echo "\n============================================\n";
?>
