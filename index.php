<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WanderWise | Experience the World</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500&display=swap"
        rel="stylesheet">
</head>

<body>

    <header>
        <div class="logo">WanderWise</div>
        <div class="search-container">
            <input type="text" id="city-search" class="search-bar" placeholder="Where do you want to go?">
            <!-- History Dropdown could go here -->
        </div>
        <nav>
            <a href="admin/login.php" style="color:white; font-weight:500;">Admin</a>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section id="hero">
            <div id="hero-content">
                <h1>Discover Your Next Adventure</h1>
                <p>Explore top destinations, hidden gems, and local secrets.</p>
                <button class="btn-explore" onclick="startExploring()">Start Exploring</button>
            </div>
        </section>

        <!-- Explorer Interface (Map + Sidebar) -->
        <section id="explorer-interface">
            <!-- Sidebar -->
            <aside id="sidebar">
                <div id="cities-section">
                    <h2>Popular Destinations</h2>
                    <div id="city-list">
                        <!-- Cities Injected via JS -->
                    </div>
                </div>

                <div id="places-section" style="display: none;">
                    <button id="back-btn" class="back-btn">&larr; Back to Cities</button>
                    <div id="weather-widget" class="weather-widget">Loading Weather...</div>
                    <h2 id="current-city-name">City Name</h2>
                    <div id="places-list">
                        <!-- Places Injected via JS -->
                    </div>
                </div>
            </aside>

            <!-- Dynamic Map -->
            <div id="map-container">
                <div id="map"></div>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script>
        function startExploring() {
            document.getElementById('hero').style.display = 'none';
            document.getElementById('explorer-interface').style.display = 'flex';
            initMap(); // Re-trigger map resize/init if needed
        }
    </script>
    <script src="assets/js/app.js"></script>
    <?php require_once 'config/config.php'; ?>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap&libraries=places"
        async defer></script>

</body>

</html>