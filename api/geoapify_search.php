<?php
include("../config/config.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$city = isset($data['city']) ? trim($data['city']) : null;

if (!$city) {
    echo json_encode(["error" => "City name is required."]);
    exit;
}

$apiKey = GEOAPIFY_API_KEY;

/* ── cURL helper ──────────────────────────────────────────────── */
function geoapify_get($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err || $code !== 200) return null;
    return json_decode($res, true);
}

/* ── 1. Geocode the city ──────────────────────────────────────── */
$geoUrl  = "https://api.geoapify.com/v1/geocode/search?" . http_build_query([
    'text'   => $city,
    'limit'  => 1,
    'apiKey' => $apiKey,
]);
$geoData = geoapify_get($geoUrl);

if (!$geoData || empty($geoData['features'])) {
    echo json_encode(["error" => "City not found. Try a more specific name (e.g., Mumbai, India)."]);
    exit;
}

$feature  = $geoData['features'][0];
$lat      = $feature['geometry']['coordinates'][1];
$lng      = $feature['geometry']['coordinates'][0];
$props    = $feature['properties'];
$cityName = $props['city'] ?? $props['county'] ?? $props['state'] ?? $city;
$country  = $props['country'] ?? '';

/* ── 2. Fetch places by category ──────────────────────────────── */
function getPlaces($lat, $lng, $categories, $limit, $apiKey) {
    $url  = "https://api.geoapify.com/v2/places?" . http_build_query([
        'categories' => $categories,
        'filter'     => "circle:$lng,$lat,5000",
        'limit'      => $limit,
        'apiKey'     => $apiKey,
    ]);
    $data = geoapify_get($url);
    return $data['features'] ?? [];
}

/* Restaurants */
$restaurants = array_values(array_filter(array_map(function($f) {
    $p = $f['properties'];
    if (empty($p['name'])) return null;
    $cuisine = $p['datasource']['raw']['cuisine'] ?? ($p['datasource']['raw']['amenity'] ?? 'Local Cuisine');
    return [
        'name'    => $p['name'],
        'desc'    => trim(($p['address_line1'] ?? '') . ', ' . ($p['address_line2'] ?? ''), ', '),
        'cuisine' => ucwords(str_replace(['_', ';'], [' ', ' / '], $cuisine)),
        'lat'     => $f['geometry']['coordinates'][1],
        'lng'     => $f['geometry']['coordinates'][0],
    ];
}, getPlaces($lat, $lng, 'catering.restaurant', 8, $apiKey))));

/* Hotels */
$hotels = array_values(array_filter(array_map(function($f) {
    $p = $f['properties'];
    if (empty($p['name'])) return null;
    $stars = $p['datasource']['raw']['stars'] ?? null;
    return [
        'name'        => $p['name'],
        'desc'        => trim(($p['address_line1'] ?? '') . ', ' . ($p['address_line2'] ?? ''), ', '),
        'price_range' => $stars ? str_repeat('$', min((int)$stars, 4)) : '$$',
        'lat'         => $f['geometry']['coordinates'][1],
        'lng'         => $f['geometry']['coordinates'][0],
    ];
}, getPlaces($lat, $lng, 'accommodation.hotel,accommodation.hostel,accommodation.guest_house', 8, $apiKey))));

/* Tourist Attractions */
$attractions = array_values(array_filter(array_map(function($f) {
    $p = $f['properties'];
    if (empty($p['name'])) return null;
    $cats   = $p['categories'] ?? [];
    $cat    = end($cats);
    $source = ucwords(str_replace(['.', '_'], ' ', $cat ?? 'Attraction'));
    return [
        'name'   => $p['name'],
        'desc'   => trim(($p['address_line1'] ?? '') . ', ' . ($p['address_line2'] ?? ''), ', '),
        'source' => $source,
        'lat'    => $f['geometry']['coordinates'][1],
        'lng'    => $f['geometry']['coordinates'][0],
    ];
}, getPlaces($lat, $lng, 'tourism,entertainment', 12, $apiKey))));

/* ── 3. Optional AI guide via Gemini ─────────────────────────── */
$guide_markdown = null;
if (defined('GOOGLE_MAPS_API_KEY') && strpos(GOOGLE_MAPS_API_KEY, 'AIza') === 0) {
    $prompt    = "Write a concise, engaging travel guide for $cityName, $country in markdown. Include: short overview, best time to visit, local culture, and 3-5 quick tips. Keep it under 300 words.";
    $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . GOOGLE_MAPS_API_KEY;
    $ch = curl_init($geminiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode(["contents" => [["parts" => [["text" => $prompt]]]]]),
    ]);
    $res = curl_exec($ch);
    if (!curl_errno($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $r = json_decode($res, true);
        $guide_markdown = $r['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
    curl_close($ch);
}

if (!$guide_markdown) {
    $guide_markdown = "## $cityName\n\n**$cityName** is a destination full of culture, cuisine, and landmarks. "
        . "Use the tabs above to explore **hotels**, **restaurants**, and **tourist attractions**.";
}

/* ── 4. Final response ────────────────────────────────────────── */
echo json_encode([
    'city_name'       => trim("$cityName, $country", ', '),
    'lat'             => $lat,
    'lng'             => $lng,
    'guide_markdown'  => $guide_markdown,
    'restaurants'     => $restaurants,
    'hotels'          => $hotels,
    'top_attractions' => $attractions,
    'pois'            => [],
    'itinerary'       => [],
    'weather'         => null,
]);
?>
