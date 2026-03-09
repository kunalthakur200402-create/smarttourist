<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore | SmartGuide AI</title>
    <!-- Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --bg-dark: #060412;
            --glass-bg: rgba(28, 14, 56, 0.5);
            --glass-border: rgba(167, 139, 250, 0.12);
            --neon-cyan: #a78bfa;
            --neon-blue: #7c3aed;
            --neon-purple: #f59e0b;
            --text-main: #f5f0ff;
            --text-muted: #9ca3af;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-dark);
            color: var(--text-main);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Webkit Scrollbar Customization */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.2);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--neon-cyan);
        }
        
        /* Glassmorphic Navbar */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3rem;
            background: rgba(5, 5, 10, 0.6);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo { 
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem; 
            font-weight: 800; 
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logo span.gradient-text {
            background: linear-gradient(90deg, var(--neon-cyan), #f59e0b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-ring {
            width: 14px;
            height: 14px;
            border: 2px solid var(--neon-cyan);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,247,255, 0.5);
            display: inline-block;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-user {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-user::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.6);
        }

        .btn-logout { 
            color: var(--text-main); 
            text-decoration: none; 
            font-weight: 500; 
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 100px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease; 
        }

        .btn-logout:hover { 
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.2);
            color: #fff; 
        }

        /* Main Container content */
        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 4rem 2rem 2rem 2rem;
            text-align: center;
            flex: 1;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .hero-section {
            margin-bottom: 3rem;
            animation: fadeInDown 0.8s ease-out;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ai-badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            background: rgba(0, 247, 255, 0.1);
            border: 1px solid rgba(0, 247, 255, 0.3);
            color: var(--neon-cyan);
            margin-bottom: 1rem;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1px;
            margin-bottom: 1rem;
        }

        h1 span {
            background: linear-gradient(135deg, #fff 0%, #a0a0b0 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 .highlight {
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-blue));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle { 
            color: var(--text-muted); 
            font-size: 1.1rem; 
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* Premium AI Search Form */
        #aiForm {
            position: relative;
            max-width: 650px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: 100px;
            padding: 6px 6px 6px 24px;
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            box-shadow: 0 10px 40px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
        }
        
        #aiForm:focus-within {
            background: rgba(25, 25, 35, 0.6);
            border-color: rgba(0, 247, 255, 0.4);
            box-shadow: 0 0 30px rgba(0, 247, 255, 0.15), 0 20px 50px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .search-icon {
            color: var(--text-muted);
            margin-right: 12px;
            transition: color 0.3s;
        }

        #aiForm:focus-within .search-icon {
            color: var(--neon-cyan);
        }

        #city {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-main);
            font-size: 1.1rem;
            font-weight: 400;
            outline: none;
            width: 100%;
        }

        #city::placeholder { 
            color: rgba(255, 255, 255, 0.3); 
            transition: opacity 0.3s; 
        }

        #aiForm:focus-within #city::placeholder { 
            opacity: 0.5; 
        }

        #generateBtn {
            background: linear-gradient(135deg, var(--neon-cyan), #f59e0b);
            color: #0a0520;
            border: none;
            height: 48px;
            padding: 0 28px;
            border-radius: 100px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(167, 139, 250, 0.3);
        }
        
        #generateBtn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 30px rgba(167, 139, 250, 0.5);
            filter: brightness(1.1);
        }
        
        #generateBtn:active { 
            transform: scale(0.98); 
        }

        #generateBtn:disabled { 
            background: rgba(255,255,255,0.1); 
            color: rgba(255,255,255,0.3); 
            cursor: not-allowed; 
            transform: none; 
            box-shadow: none; 
        }

        /* Split Layout Grid for Map & Output */
        .explore-wrapper {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
            margin-top: 3rem;
        }

        .explore-wrapper.active {
            opacity: 1;
            transform: translateY(0);
        }

        .explore-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            height: 65vh;
            min-height: 500px;
        }
        
        .panel {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.05);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.2);
        }

        .panel-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: 0.5px;
        }

        .panel-icon {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        
        .icon-map { background: var(--neon-cyan); box-shadow: 0 0 10px var(--neon-cyan); }
        .icon-ai { background: var(--neon-purple); box-shadow: 0 0 10px var(--neon-purple); }

        #map-container {
            flex: 1;
            width: 100%;
            position: relative;
            background: #111; /* fallback before map loads */
        }

        #map { 
            width: 100%; 
            height: 100%; 
            position: absolute;
            top: 0; left: 0;
        }

        /* Results / Output Panel */
        .panel-tabs {
            display: flex;
            background: rgba(255,255,255,0.03);
            border-bottom: 1px solid var(--glass-border);
            padding: 0 10px;
        }
        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 15px 18px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-family: 'Inter', sans-serif;
        }
        .tab-btn.active {
            color: var(--neon-cyan);
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 15%;
            width: 70%;
            height: 2px;
            background: var(--neon-cyan);
            box-shadow: 0 0 10px var(--neon-cyan);
        }
        
        #result-body {
            flex: 1;
            padding: 2rem;
            text-align: left;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        
        #result-body h1, #result-body h2, #result-body h3 { 
            color: #fff; 
            font-family: 'Outfit', sans-serif;
            margin-top: 1.5rem; 
            margin-bottom: 0.8rem; 
            font-weight: 600;
            line-height: 1.3;
        }

        #result-body h1 { font-size: 1.8rem; background: linear-gradient(90deg, #fff, var(--neon-cyan)); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;}
        #result-body h2 { font-size: 1.4rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
        #result-body h3 { font-size: 1.1rem; color: var(--neon-cyan); }
        
        #result-body p { 
            margin-bottom: 1.2rem; 
            color: var(--text-muted); 
            font-size: 1rem; 
            line-height: 1.7;
        }

        #result-body ul, #result-body ol {
            margin-bottom: 1.2rem;
            padding-left: 1.5rem;
            color: var(--text-muted);
            line-height: 1.7;
        }
        
        #result-body li {
            margin-bottom: 0.5rem;
        }

        #result-body strong {
            color: #fff;
            font-weight: 600;
        }

        /* Ambient glow blobs */
        .ambient-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(130px);
            z-index: 0;
            pointer-events: none;
            opacity: 0;
        }

        /* Unified Loader */
        .loading-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(5, 5, 10, 0.8);
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .loading-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(0, 247, 255, 0.1);
            border-radius: 50%;
            border-top-color: var(--neon-cyan);
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        .loading-overlay p {
            color: var(--neon-cyan);
            font-family: 'Outfit', sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            animation: pulse 2s infinite ease-in-out;
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        /* Canvas dots background sits behind everything */
        #particles-bg { z-index: 0; }

        /* Page transition */
        .hero-section {
            max-height: 600px;
            overflow: hidden;
            transition: max-height 0.6s cubic-bezier(0.2, 0.8, 0.2, 1),
                        opacity 0.5s ease,
                        transform 0.5s ease,
                        margin 0.5s ease;
        }
        body.searching .hero-section {
            max-height: 0; opacity: 0; transform: translateY(-20px); margin-bottom: 0;
        }
        .container { transition: padding-top 0.5s ease; }
        body.searching .container { padding-top: 1.5rem; }
        body.searching .explore-wrapper { margin-top: 1rem; }

        /* Leaflet */
        .leaflet-tile-pane { filter: brightness(0.82) saturate(0.85); }
        .leaflet-container { background: #0a0418; }
        .leaflet-control-attribution { background:rgba(6,4,18,0.8)!important; color:#777!important; font-size:9px; }
        .leaflet-control-attribution a { color:var(--neon-cyan)!important; }
        .leaflet-control-zoom a { background:rgba(28,14,56,0.9)!important; color:#fff!important; border-color:rgba(167,139,250,0.15)!important; }
        .leaflet-popup-content-wrapper { background:rgba(10,4,30,0.95); color:#eee; border:1px solid rgba(167,139,250,0.3); border-radius:12px; backdrop-filter:blur(12px); }
        .leaflet-popup-tip { background:rgba(10,4,30,0.95); }

        /* Responsive */
        @media (max-width: 1024px) {
            h1 { font-size: 2.8rem; }
            .explore-grid { height: auto; display: flex; flex-direction: column; }
            .panel { height: 50vh; min-height: 400px; }
            .container { padding-top: 2rem; }
        }
        @media (max-width: 768px) {
            nav { padding: 1rem 1.5rem; }
            h1 { font-size: 2.2rem; }
            #generateBtn span { display: none; }
            #generateBtn::after { content: 'GO'; }
        }
    </style>
</head>
<body>
    <!-- Particles dots background -->

    <nav>
        <div class="logo">
            <span class="logo-ring"></span>
            <span class="gradient-text">Smart Guide AI</span>
        </div>
        <div class="nav-links">
            <div class="nav-user">
                <?php 
                    $user_display = $_SESSION['user'];
                    if (strpos($user_display, '@') !== false) {
                        $user_display = explode('@', $user_display)[0];
                    }
                    echo htmlspecialchars(ucfirst($user_display)); 
                ?>
            </div>
            <a href="auth/logout.php" class="btn-logout">Sign Out</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="hero-section">
            <div class="ai-badge">Agentic Engine 4.0</div>
            <h1>Plan Your Next <span>Journey</span> With <span class="highlight">Precision</span></h1>
            <p class="subtitle">Enter any destination worldwide. Our AI neural engine will instantly map coordinates, fetch live conditions, and synthesize a complete itinerary.</p>
        </div>
        
        <form id="aiForm" method="GET" action="results.php">
            <div class="search-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <div style="position: relative; flex: 1; display: flex; align-items: center;">
                <input type="text" id="city" name="city" placeholder="E.g., Tokyo, Japan or Paris, France..." required autocomplete="off">
                <button type="button" id="locateBtn" title="Detect My Location" style="position: absolute; right: 15px; background: transparent; border: none; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: all 0.3s ease;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </button>
            </div>
            <button type="submit" id="generateBtn">
                <span>Analyze Destination</span>
            </button>
        </form>
        
        <div class="explore-wrapper" id="exploreWrapper">
            <div class="explore-grid">
                
                <!-- Map Panel -->
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-icon icon-map"></span>
                        <span class="panel-title">Geospatial Intelligence</span>
                    </div>
                    <div id="map-container">
                        <div id="map"></div>
                    </div>
                    <div class="loading-overlay" id="map-loader">
                        <div class="spinner"></div>
                        <p>Locating Coordinates...</p>
                    </div>
                </div>

                <!-- AI Results Panel -->
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-icon icon-ai"></span>
                        <span class="panel-title">Neural Synthesis</span>
                    </div>
                    <div class="panel-tabs" id="panelTabs">
                        <button class="tab-btn active" data-tab="insights">Insights</button>
                        <button class="tab-btn" data-tab="itinerary">Plan</button>
                        <button class="tab-btn" data-tab="stay">Stay</button>
                        <button class="tab-btn" data-tab="dining">Dining</button>
                        <button class="tab-btn" data-tab="sights">Sights</button>
                    </div>
                    <div id="result-body">
                        <div id="result">
                            <h3 style="color:var(--text-muted); font-weight: 400; text-align: center; margin-top: 30%;">
                                Awaiting destination parameters...
                            </h3>
                        </div>
                    </div>
                    <div class="loading-overlay" id="ai-loader">
                        <div class="spinner"></div>
                        <p>Generating Insights...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require_once 'config/config.php'; ?>
    <script>
        const GEOAPIFY_API_KEY = '<?php echo GEOAPIFY_API_KEY; ?>';

        document.getElementById('aiForm').addEventListener('submit', function() {
            // Page-change transition: hide hero, expand results
            document.body.classList.add('searching');
            document.getElementById('exploreWrapper').classList.add('active');
            document.getElementById('map-loader').classList.add('active');
            document.getElementById('ai-loader').classList.add('active');
        });
        
        // This is a hook for the ai.js script to call when it finishes loading
        function hideLoaders() {
            const mapL = document.getElementById('map-loader');
            const aiL = document.getElementById('ai-loader');
            if(mapL) mapL.classList.remove('active');
            if(aiL) aiL.classList.remove('active');
        }
    </script>

    <!-- Leaflet Map JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/particles.js"></script>
    <script src="assets/js/ai.js"></script>
    <script src="assets/js/geolocation.js"></script>
</body>
</html>