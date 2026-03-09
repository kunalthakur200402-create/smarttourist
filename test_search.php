<?php
include("config/config.php");
ini_set('display_errors', 1);

$city = "Paris";
$prompt = "Act as a universal travel expert. Create a comprehensive tourist guide for the city of $city.
Return ONLY a valid JSON object with the following structure:
{
  \"city_name\": \"Name of the city\",
  \"lat\": latitude_float,
  \"lng\": longitude_float,
  \"guide_markdown\": \"A detailed tourist guide in Markdown format\"
}";

echo "=== Testing OpenAI ===\n";
echo "Key starts with: " . substr(AI_API_KEY, 0, 10) . "...\n";

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . AI_API_KEY
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-4o-mini",
    "response_format" => ["type" => "json_object"],
    "messages" => [
        ["role" => "system", "content" => "You are a helpful travel assistant that only responds in JSON."],
        ["role" => "user", "content" => $prompt]
    ]
]));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// curl_close($ch); // Deprecated in PHP 8.5+; effectively a no-op in 8.0+ as CurlHandle is an object now.

echo "HTTP Code: $http_code\n";
if ($http_code == 200) {
    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? 'no content';
    echo "SUCCESS! Content preview:\n" . substr($content, 0, 300) . "\n";
} else {
    $err = json_decode($response, true);
    echo "FAILED: " . ($err['error']['message'] ?? $response) . "\n";
}
?>
