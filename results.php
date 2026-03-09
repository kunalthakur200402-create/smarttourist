<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}
$city = isset($_GET['city']) ? htmlspecialchars(trim($_GET['city'])) : '';
if (!$city) {
    header("Location: dashboard.php");
    exit;
}
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $city; ?> | SmartGuide AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-dark);
            color: var(--text-main);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--neon-cyan); }

        /* ── Navbar ─────────────────────────────────────────────── */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3rem;
            background: rgba(5, 5, 10, 0.7);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 200;
            gap: 1rem;
        }

        .nav-left { display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; }

        .btn-back {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 100px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        .btn-back:hover { color: #fff; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); }
        .btn-back svg { transition: transform 0.3s; }
        .btn-back:hover svg { transform: translateX(-3px); }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logo .gradient-text {
            background: linear-gradient(90deg, var(--neon-cyan), #f59e0b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logo-ring {
            width: 12px; height: 12px;
            border: 2px solid var(--neon-cyan);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,247,255,0.5);
            display: inline-block;
            flex-shrink: 0;
        }

        /* Inline search form in nav */
        #navSearchForm {
            display: flex;
            align-items: center;
            background: rgba(20, 20, 30, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 100px;
            padding: 5px 5px 5px 18px;
            gap: 8px;
            transition: all 0.3s ease;
            flex: 1;
            max-width: 420px;
        }
        #navSearchForm:focus-within {
            border-color: rgba(0,247,255,0.4);
            box-shadow: 0 0 20px rgba(0,247,255,0.12);
        }
        #navSearchForm input {
            flex: 1;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
        }
        #navSearchForm input::placeholder { color: rgba(255,255,255,0.3); }
        #navSearchBtn {
            background: linear-gradient(135deg, var(--neon-cyan), #f59e0b);
            color: #0a0520;
            border: none;
            height: 36px;
            padding: 0 18px;
            border-radius: 100px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        #navSearchBtn:hover { filter: brightness(1.15); }
        #navSearchBtn:disabled { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.3); cursor: not-allowed; }

        .nav-right { display: flex; align-items: center; gap: 1rem; }
        .nav-user {
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-user::before {
            content: '';
            width: 7px; height: 7px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(34,197,94,0.6);
            display: inline-block;
        }
        .btn-logout {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.87rem;
            padding: 7px 14px;
            border-radius: 100px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.1); color: #fff; }

        /* ── City hero strip ─────────────────────────────────────── */
        .city-hero {
            padding: 1.8rem 3rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            position: relative;
            z-index: 5;
            animation: fadeSlideIn 0.6s ease-out;
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .city-badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            background: rgba(0,247,255,0.1);
            border: 1px solid rgba(0,247,255,0.25);
            color: var(--neon-cyan);
        }
        .city-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .city-title .city-name {
            background: linear-gradient(135deg, var(--neon-cyan), #f59e0b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ── Results grid — fills remaining height ───────────────── */
        .results-container {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 0 2rem 2rem;
            min-height: 0;
        }

        .panel {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.05);
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 500px;
            max-height: calc(100vh - 200px);
        }

        .panel-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.2);
            flex-shrink: 0;
        }
        .panel-title {
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .panel-icon { width: 10px; height: 10px; border-radius: 50%; }
        .icon-map  { background: var(--neon-cyan);   box-shadow: 0 0 10px var(--neon-cyan); }
        .icon-ai   { background: var(--neon-purple); box-shadow: 0 0 10px var(--neon-purple); }

        /* Map */
        #map-container { flex: 1; position: relative; background: #0a0418; }
        #map { width: 100%; height: 100%; position: absolute; top: 0; left: 0; }

        /* Tabs */
        .panel-tabs {
            display: flex;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--glass-border);
            padding: 0 10px;
            flex-shrink: 0;
            overflow-x: auto;
        }
        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 14px 16px;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }
        .tab-btn.active { color: var(--neon-cyan); }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0; left: 15%;
            width: 70%; height: 2px;
            background: var(--neon-cyan);
            box-shadow: 0 0 10px var(--neon-cyan);
        }

        #result-body {
            flex: 1;
            padding: 1.8rem;
            text-align: left;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        #result-body h1,#result-body h2,#result-body h3 {
            color: #fff;
            font-family: 'Outfit', sans-serif;
            margin-top: 1.4rem;
            margin-bottom: 0.7rem;
            font-weight: 600;
        }
        #result-body h1 { font-size: 1.7rem; background: linear-gradient(90deg,#fff,var(--neon-cyan)); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
        #result-body h2 { font-size: 1.3rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.4rem; }
        #result-body h3 { font-size: 1rem; color: var(--neon-cyan); }
        #result-body p  { margin-bottom: 1rem; color: var(--text-muted); font-size: 0.97rem; line-height: 1.7; }
        #result-body ul,#result-body ol { margin-bottom: 1rem; padding-left: 1.4rem; color: var(--text-muted); line-height: 1.7; }
        #result-body li { margin-bottom: 0.4rem; }
        #result-body strong { color: #fff; font-weight: 600; }

        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(5,5,10,0.82);
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
        .loading-overlay.active { opacity: 1; pointer-events: all; }
        .spinner {
            width: 40px; height: 40px;
            border: 3px solid rgba(0,247,255,0.1);
            border-radius: 50%;
            border-top-color: var(--neon-cyan);
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        .loading-overlay p {
            color: var(--neon-cyan);
            font-family: 'Outfit', sans-serif;
            font-weight: 500;
            font-size: 0.85rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            animation: pulse 2s infinite ease-in-out;
        }
        @keyframes spin  { 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0%,100% { opacity: 0.6; } 50% { opacity: 1; } }

        /* Legend */
        .map-legend {
            position: absolute;
            bottom: 14px;
            left: 14px;
            z-index: 500;
            background: rgba(5,5,10,0.85);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            backdrop-filter: blur(10px);
        }
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--text-muted); }
        .legend-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        .leaflet-tile-pane { filter: brightness(0.82) saturate(0.85); }
        .leaflet-container { background: #0a0418; }
        .leaflet-control-attribution { background:rgba(6,4,18,0.8)!important; color:#777!important; font-size:9px; }
        .leaflet-control-attribution a { color:var(--neon-cyan)!important; }
        .leaflet-control-zoom a { background:rgba(28,14,56,0.9)!important; color:#fff!important; border-color:rgba(167,139,250,0.15)!important; }
        .leaflet-popup-content-wrapper { background:rgba(10,4,30,0.95); color:#eee; border:1px solid rgba(167,139,250,0.3); border-radius:12px; backdrop-filter:blur(12px); }
        .leaflet-popup-tip { background:rgba(10,4,30,0.95); }

        /* Ambient glows — hidden, particles.js handles bg */
        .glow { display: none; }

        @media (max-width: 1024px) {
            .results-container { grid-template-columns: 1fr; padding: 0 1rem 1.5rem; }
            .panel { max-height: 60vh; }
            nav { padding: 0.8rem 1.2rem; }
            .city-hero { padding: 1.2rem 1.2rem 0.8rem; }
            .city-title { font-size: 1.6rem; }
        }
        @media (max-width: 640px) {
            #navSearchForm { max-width: 200px; }
            .nav-user { display: none; }
        }
    </style>
</head>
<body>
    <!-- Particles background -->

    <!-- Navbar -->
    <nav>
        <div class="nav-left">
            <a href="dashboard.php" class="btn-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Back
            </a>
            <div class="logo">
                <span class="logo-ring"></span>
                <span class="gradient-text">SmartGuide AI</span>
            </div>
        </div>

        <!-- Re-search form in nav -->
        <form id="navSearchForm" method="GET" action="results.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted); flex-shrink:0;">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="city" id="navCityInput"
                   value="<?php echo $city; ?>"
                   placeholder="Search another city…" autocomplete="off" required>
            <button type="submit" id="navSearchBtn">Search</button>
        </form>

        <div class="nav-right">
            <div class="nav-user">
                <?php
                    $u = $_SESSION['user'];
                    if (strpos($u, '@') !== false) $u = explode('@', $u)[0];
                    echo htmlspecialchars(ucfirst($u));
                ?>
            </div>
            <a href="auth/logout.php" class="btn-logout">Sign Out</a>
        </div>
    </nav>

    <!-- City title strip -->
    <div class="city-hero">
        <span class="city-badge">AI Destination</span>
        <h1 class="city-title">Exploring <span class="city-name" id="cityDisplayName"><?php echo $city; ?></span></h1>
    </div>

    <!-- Main results grid -->
    <div class="results-container">

        <!-- Map panel -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-icon icon-map"></span>
                <span class="panel-title">Geospatial Intelligence</span>
            </div>
            <div id="map-container">
                <div id="map"></div>
                <!-- Legend -->
                <div class="map-legend" id="mapLegend" style="display:none;">
                    <div class="legend-item"><div class="legend-dot" style="background:#a78bfa; box-shadow:0 0 6px #a78bfa;"></div> City Centre</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#ff6b6b; box-shadow:0 0 6px #ff6b6b;"></div> Restaurants</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#ffd93d; box-shadow:0 0 6px #ffd93d;"></div> Hotels</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#6bcb77; box-shadow:0 0 6px #6bcb77;"></div> Attractions</div>
                </div>
            </div>
            <div class="loading-overlay active" id="map-loader">
                <div class="spinner"></div>
                <p>Mapping Coordinates…</p>
            </div>
        </div>

        <!-- AI Results panel -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-icon icon-ai"></span>
                <span class="panel-title">Neural Synthesis</span>
            </div>
            <div class="panel-tabs" id="panelTabs">
                <button class="tab-btn active" data-tab="insights">Insights</button>
                <button class="tab-btn" data-tab="stay">Stay</button>
                <button class="tab-btn" data-tab="dining">Dining</button>
                <button class="tab-btn" data-tab="sights">Sights</button>
            </div>
            <div id="result-body">
                <div id="result">
                    <h3 style="color:var(--text-muted); font-weight:400; text-align:center; margin-top:30%;">
                        Loading destination data…
                    </h3>
                </div>
            </div>
            <div class="loading-overlay active" id="ai-loader">
                <div class="spinner"></div>
                <p>Generating Insights…</p>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        const GEOAPIFY_API_KEY = '<?php echo GEOAPIFY_API_KEY; ?>';
        const SEARCH_CITY      = <?php echo json_encode($city); ?>;
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/particles.js"></script>
    <script src="assets/js/results.js"></script>
</body>
</html>
