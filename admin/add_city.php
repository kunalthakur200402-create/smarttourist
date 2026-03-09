<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';
require_once '../config/config.php';
use MongoDB\BSON\UTCDateTime;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_data = [
        'city_name' => $_POST['city_name'],
        'country' => $_POST['country'],
        'description' => $_POST['description'],
        'image_url' => $_POST['image_url'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'weather_temp' => (int)$_POST['weather_temp'],
        'weather_desc' => $_POST['weather_desc'],
        'directions' => $_POST['directions'],
        'created_at' => new UTCDateTime()
    ];

    $insert = $conn->cities->insertOne($city_data);

    if ($insert->getInsertedCount() > 0) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Error: Could not add city to database.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add City - Smart Guide AI Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f4f7f6; padding-top: 0; display: block; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .admin-nav { background: #0f2027; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; }
        .container { max-width: 800px; margin: 3rem auto; padding: 0 1rem; }
        .card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        h2 { margin-bottom: 1.5rem; color: #2c3e50; border-bottom: 2px solid #f7b733; display: inline-block; padding-bottom: 0.5rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .full-width { grid-column: span 2; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555; }
        input, textarea { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; transition: 0.3s; box-sizing: border-box; }
        input:focus, textarea:focus { border-color: #f7b733; outline: none; box-shadow: 0 0 0 3px rgba(247, 183, 51, 0.2); }
        .btn-submit { width: 100%; padding: 1rem; background: #0f2027; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 1rem; }
        .btn-submit:hover { background: #2c5364; transform: translateY(-2px); }
        .cancel-link { display: block; text-align: center; margin-top: 1rem; color: #7f8c8d; text-decoration: none; }
        .loader { display: none; margin-left: 10px; border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 20px; height: 20px; animation: spin 2s linear infinite; vertical-align: middle; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="logo">Smart Guide AI Admin</div>
        <a href="dashboard.php" style="color:white; text-decoration:none;">&larr; Dashboard</a>
    </nav>
    <div class="container">
        <div class="card">
            <h2>Add New Destination <span id="api-loader" class="loader"></span></h2>
            <?php if (isset($error)) echo "<p style='color:red; margin-bottom:1rem;'>$error</p>"; ?>
            
            <form method="POST" id="cityForm">
                <div class="form-group full-width">
                    <label>Search City (Autocomplete)</label>
                    <input type="text" id="autocomplete" name="city_name" required placeholder="Type city name...">
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" id="country" name="country" required readonly>
                    </div>
                    <div class="form-group">
                        <label>Image URL</label>
                        <input type="text" id="image_url" name="image_url" placeholder="Fetched image or custom URL">
                    </div>
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" id="lat" name="latitude" readonly>
                    </div>
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" id="lng" name="longitude" readonly>
                    </div>
                    <div class="form-group">
                        <label>Temp (°C)</label>
                        <input type="text" id="temp" name="weather_temp" readonly>
                    </div>
                    <div class="form-group">
                        <label>Weather</label>
                        <input type="text" id="weather_desc" name="weather_desc" readonly>
                    </div>
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea id="description" name="description" rows="3" required placeholder="Auto-fetched summary..."></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>Directions (from New Delhi)</label>
                        <textarea id="directions" name="directions" rows="3" readonly placeholder="Auto-calculated route..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Add City</button>
                <a href="dashboard.php" class="cancel-link">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places"></script>
    <script>
        function initAutocomplete() {
            const autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'), {
                types: ['(cities)']
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                document.getElementById('api-loader').style.display = 'inline-block';

                // Fill Coordinates
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                document.getElementById('lat').value = lat;
                document.getElementById('lng').value = lng;

                // Fill Country
                let country = "";
                for (const component of place.address_components) {
                    if (component.types.includes("country")) {
                        country = component.long_name;
                    }
                }
                document.getElementById('country').value = country;

                // Fill Description (Placeholder if no summary available)
                document.getElementById('description').value = "Discover the beauty of " + place.name + ", " + country + ".";

                // Fetch Weather
                fetch(`../api/get_weather.php?city=${place.name}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.main) {
                            document.getElementById('temp').value = Math.round(data.main.temp);
                            document.getElementById('weather_desc').value = data.weather[0].description;
                        }
                    });

                // Fetch Directions (Mock or via Directions Service)
                const directionsService = new google.maps.DirectionsService();
                directionsService.route({
                    origin: "New Delhi, India",
                    destination: place.formatted_address,
                    travelMode: google.maps.TravelMode.DRIVING
                }, function(response, status) {
                    if (status === 'OK') {
                        const route = response.routes[0].legs[0];
                        document.getElementById('directions').value = `Total distance: ${route.distance.text}. Duration: ${route.duration.text}. Route via ${route.summary || 'main highways'}.`;
                    } else {
                        document.getElementById('directions').value = "Directions not available for driving mode.";
                    }
                    document.getElementById('api-loader').style.display = 'none';
                });

                // Photos (Optional)
                if (place.photos && place.photos.length > 0) {
                    document.getElementById('image_url').value = place.photos[0].getUrl({maxWidth: 800});
                }
            });
        }
        google.maps.event.addDomListener(window, 'load', initAutocomplete);
    </script>
</body>

</html>
