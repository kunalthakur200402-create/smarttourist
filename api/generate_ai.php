<?php
include("../config/config.php");

// Enable error reporting for debugging
ini_set('display_errors', 0); // Hide raw PHP errors from JSON response

$data = json_decode(file_get_contents("php://input"), true);
$city = isset($data['city']) ? $data['city'] : null;

if (!$city) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "City name is required."]);
    exit;
}

$prompt = "Act as a universal travel expert. Create a comprehensive tourist guide for the city of $city.
Return ONLY a valid JSON object with the following structure:
{
  \"city_name\": \"Name of the city\",
  \"lat\": latitude_float,
  \"lng\": longitude_float,
  \"guide_markdown\": \"A brief overview of the city.\",
  \"hotels\": [
    {\"name\": \"Hotel Name\", \"desc\": \"Brief description\", \"price_range\": \"$\"},
    {\"name\": \"Hotel Name 2\", \"desc\": \"Brief description\", \"price_range\": \"$$\"}
  ],
  \"restaurants\": [
    {\"name\": \"Restaurant Name\", \"desc\": \"Brief signature dish\", \"cuisine\": \"Local\"},
    {\"name\": \"Restaurant Name 2\", \"desc\": \"Brief signature dish\", \"cuisine\": \"International\"}
  ],
  \"itinerary\": [
    {
      \"day\": 1,
      \"activities\": [
        {\"time\": \"Morning\", \"desc\": \"...\"},
        {\"time\": \"Afternoon\", \"desc\": \"...\"},
        {\"time\": \"Evening\", \"desc\": \"...\"}
      ]
    },
    {
      \"day\": 2,
      \"activities\": [
        {\"time\": \"Morning\", \"desc\": \"...\"},
        {\"time\": \"Afternoon\", \"desc\": \"...\"},
        {\"time\": \"Evening\", \"desc\": \"...\"}
      ]
    },
    {
      \"day\": 3,
      \"activities\": [
        {\"time\": \"Morning\", \"desc\": \"...\"},
        {\"time\": \"Afternoon\", \"desc\": \"...\"},
        {\"time\": \"Evening\", \"desc\": \"...\"}
      ]
    }
  ],
  \"top_attractions\": [
    {\"name\": \"Attraction 1\", \"desc\": \"...\"},
    {\"name\": \"Attraction 2\", \"desc\": \"...\"}
  ]
}";

$response_content = null;
$error_msg = null;

// Helper to call Gemini API
function call_gemini($key, $prompt, $model = 'gemini-2.0-flash-lite') {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $key;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $payload = ["contents" => [["parts" => [["text" => $prompt . " Respond with ONLY the JSON object, no extra text, no markdown code blocks."]]]]];
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch); // Deprecated in PHP 8.5+; effectively a no-op in 8.0+ as CurlHandle is an object now.
    
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $raw_text = $result['candidates'][0]['content']['parts'][0]['text'];
            // Strip markdown code blocks if present
            $raw_text = preg_replace('/```json\s*/i', '', $raw_text);
            $raw_text = preg_replace('/```\s*/', '', $raw_text);
            if (preg_match('/\{.*\}/s', $raw_text, $matches)) {
                return $matches[0];
            }
        }
    }
    return null;
}

// --- 1. Try Gemini with Google API Key (primary) ---
if (!$response_content && defined('GOOGLE_MAPS_API_KEY') && strpos(GOOGLE_MAPS_API_KEY, 'AIza') === 0) {
    // Try fast model first, then fallback to regular, then fallback to 1.5
    $response_content = call_gemini(GOOGLE_MAPS_API_KEY, $prompt, 'gemini-2.0-flash-lite');
    if (!$response_content) {
        $response_content = call_gemini(GOOGLE_MAPS_API_KEY, $prompt, 'gemini-2.0-flash');
    }
    if (!$response_content) {
        $response_content = call_gemini(GOOGLE_MAPS_API_KEY, $prompt, 'gemini-1.5-flash');
    }
}

// --- 2. Try OpenAI (fallback) ---
if (!$response_content && defined('AI_API_KEY') && strpos(AI_API_KEY, 'sk-') === 0) {
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
    $openai_response = curl_exec($ch);
    // curl_close($ch); // Removed as per instruction
    if (!curl_errno($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $result = json_decode($openai_response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            $response_content = $result['choices'][0]['message']['content'];
        }
    } else {
        $error_msg = $openai_response;
        // error_log("OpenAI Error: " . $error_msg); // Optional: if you have log access
    }
}

// --- 3. Try Gemini via OpenWeather slot if it's an AIza key ---
if (!$response_content && defined('OPENWEATHER_API_KEY') && strpos(OPENWEATHER_API_KEY, 'AIza') === 0) {
    $response_content = call_gemini(OPENWEATHER_API_KEY, $prompt, 'gemini-2.0-flash-lite');
}

// --- 4. Weather Data (Live) ---
$weather = null;
// Only call weather if key looks like an OpenWeatherMap key (no AIza, no gen-lang)
if ($response_content && defined('OPENWEATHER_API_KEY') && strpos(OPENWEATHER_API_KEY, 'AIza') === false && strpos(OPENWEATHER_API_KEY, 'gen-lang') === false) {
    $weather_url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . OPENWEATHER_API_KEY . "&units=metric";
    $weather_res = @file_get_contents($weather_url);
    if ($weather_res) {
        $w_data = json_decode($weather_res, true);
        if (isset($w_data['main']['temp'])) {
            $weather = ["temp" => round($w_data['main']['temp']), "desc" => ucfirst($w_data['weather'][0]['description'])];
        }
    }
}

// --- 5. Amadeus POIs (Standard integration) ---
$pois = [];
if ($response_content) {
    $temp_data = json_decode($response_content, true);
    if (isset($temp_data['lat']) && isset($temp_data['lng'])) {
        include_once("get_destination_info.php");
        $apiKeyString = base64_decode(AMADEUS_API_KEY);
        if ($apiKeyString === false || strpos($apiKeyString, ':') === false) { $apiKeyString = AMADEUS_API_KEY; }
        if (strpos($apiKeyString, ':') !== false) {
            list($client_id, $client_secret) = explode(':', $apiKeyString);
            $tokenData = get_amadeus_token($client_id, $client_secret);
            if ($tokenData) {
                $token = is_array($tokenData) ? $tokenData['token'] : $tokenData;
                $env = is_array($tokenData) ? $tokenData['env'] : "prod";
                $pois = get_pois($token, $temp_data['lat'], $temp_data['lng'], $env);
            }
        }
    }
}

// --- 6. Final Output ---
header('Content-Type: application/json');
if ($response_content) {
    $final_data = json_decode($response_content, true);
    if ($final_data) {
        $final_data['weather'] = $weather;
        $final_data['pois'] = $pois;
        echo json_encode($final_data);
    } else {
        echo json_encode(["error" => "Failed to parse travel guide. Raw response was invalid JSON.", "raw" => substr($response_content, 0, 100)]);
    }
} else {
    // If we're here, it means both Gemini and OpenAI failed (likely 429 quota)
    // TRY TO GET AT LEAST COORDINATES AND POIS VIA GOOGLE MAPS + AMADEUS
    $fallback_data = ["error" => "Search limit exceeded. Please check your API quotas (Gemini/OpenAI).", "pois" => []];
    if (isset($error_msg)) {
        $fallback_data['debug_openai'] = $error_msg;
    }
    
    // Use Google Maps Geocoding to get lat/lng for the city
    $geo_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($city) . "&key=" . GOOGLE_MAPS_API_KEY;
    $geo_res = @file_get_contents($geo_url);
    if ($geo_res) {
        $g_data = json_decode($geo_res, true);
        if (isset($g_data['results'][0]['geometry']['location'])) {
            $lat = $g_data['results'][0]['geometry']['location']['lat'];
            $lng = $g_data['results'][0]['geometry']['location']['lng'];
            $fallback_data['lat'] = $lat;
            $fallback_data['lng'] = $lng;
            $fallback_data['city_name'] = $g_data['results'][0]['address_components'][0]['long_name'];
            
            // Now get POIs via Amadeus
            include_once("get_destination_info.php");
            $apiKeyString = base64_decode(AMADEUS_API_KEY);
            if ($apiKeyString === false || strpos($apiKeyString, ':') === false) { $apiKeyString = AMADEUS_API_KEY; }
            if (strpos($apiKeyString, ':') !== false) {
                list($client_id, $client_secret) = explode(':', $apiKeyString);
                $tokenData = get_amadeus_token($client_id, $client_secret);
                if ($tokenData) {
                    $token = is_array($tokenData) ? $tokenData['token'] : $tokenData;
                    $env = is_array($tokenData) ? $tokenData['env'] : "prod";
                    $fallback_data['pois'] = get_pois($token, $lat, $lng, $env);
                }
            }
        }
    }
    echo json_encode($fallback_data);
}
?>