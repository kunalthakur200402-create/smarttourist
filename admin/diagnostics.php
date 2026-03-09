<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    die("Access Denied. Please login as admin first.");
}
require_once '../config/config.php';

function test_api($name, $status, $details, $advice = "") {
    $color = $status === 'OK' ? '#2ecc71' : ($status === 'WARNING' ? '#f39c12' : '#e74c3c');
    echo "<div style='background: white; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 5px solid $color;'>";
    echo "<strong>$name:</strong> <span style='color: $color; font-weight: bold;'>$status</span><br>";
    echo "<p style='margin: 5px 0; font-size: 0.9rem;'>$details</p>";
    if ($advice) echo "<p style='margin: 5px 0; font-size: 0.85rem; color: #555; font-style: italic;'>💡 $advice</p>";
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Diagnostics - Smart Guide AI</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; color: #333; line-height: 1.5; }
        .container { max-width: 700px; margin: 0 auto; }
        h1 { color: #0f2027; margin-bottom: 0.5rem; }
        .info-box { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Smart Guide AI API Status</h1>
        <div class="info-box">
            Use this page to verify your API configurations. Search functionality relies on several different keys working together.
        </div>

        <?php
        // 1. Google Maps Check
        $maps_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
        if (strpos($maps_key, 'AIza') === 0) {
            test_api("Google Maps (Autocomplete)", "OK", "Key format looks correct.", "Make sure 'Places API' and 'Maps JavaScript API' are enabled in your Google Cloud Console.");
        } else {
            test_api("Google Maps (Autocomplete)", "ERROR", "Invalid or missing key.", "The key should start with 'AIza'. Check your config/config.php file.");
        }

        // 2. OpenAI Check
        $openai_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
        if (strpos($openai_key, 'sk-') === 0) {
            test_api("OpenAI (Primary AI)", "OK", "Key format looks correct (starts with sk-).", "This is used for generating the travel guides.");
        } else {
            test_api("OpenAI (Primary AI)", "WARNING", "Key is missing or invalid.", "The system will attempt to fallback to Gemini if this is not set.");
        }

        // 3. Gemini Check (Weather Slot)
        $weather_key = defined('OPENWEATHER_API_KEY') ? OPENWEATHER_API_KEY : '';
        if (strpos($weather_key, 'gen-lang-') === 0) {
            test_api("Google Gemini (Fallback AI)", "CONFIGURED", "Identifier detected: $weather_key", "Note: This is an ID, not the secret key itself. Gemini usually uses keys starting with 'AIza'. If AI search fails, try using your Google Maps key in this slot instead.");
        } elseif (strpos($weather_key, 'AIza') === 0) {
            test_api("Google Gemini (Fallback AI)", "POTENTIAL", "Detected a Google-style key.", "If the AI guide works, this key is valid for Gemini.");
        } else {
            test_api("Google Gemini (Fallback AI)", "NOT SET", "No Gemini key detected.", "AI guidance will strictly require a valid OpenAI key.");
        }

        // 4. OpenWeatherMap Check
        if ($weather_key && strpos($weather_key, 'gen-lang-') === false && strpos($weather_key, 'AIza') === false) {
             test_api("OpenWeatherMap (Live Weather)", "OK", "Key exists.", "Used for live temperature and weather icons.");
        } else {
             test_api("OpenWeatherMap (Live Weather)", "INACTIVE", "No valid OpenWeatherMap key found.", "Live weather features will show 'N/A' or errors.");
        }
        ?>

        <div style="margin-top: 20px; padding: 15px; background: #fff; border-radius: 8px; border: 1px solid #ddd;">
            <h3>Quick Fix Tips</h3>
            <ul style="font-size: 0.9rem;">
                <li><strong>Search doesn't show suggestions?</strong> Your Google Maps key needs 'Places API' enabled.</li>
                <li><strong>"Plan My Journey" fails?</strong> Your AI key (OpenAI or Gemini) is likely invalid or expired.</li>
                <li><strong>Weather says Error?</strong> You need a separate key from <a href="https://openweathermap.org/api" target="_blank">OpenWeatherMap.org</a>.</li>
            </ul>
        </div>

        <br>
        <div style="display: flex; gap: 10px;">
            <a href="dashboard.php" style="background: #0f2027; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Dashboard</a>
            <a href="add_city.php" style="background: #f7b733; color: #0f2027; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">Test Add City</a>
        </div>
    </div>
</body>
</html>
