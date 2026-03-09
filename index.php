<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartGuide | AI Travel Intelligence</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
</head>
<body>
    <!-- Cinematic 3D Background -->
    <div id="ai-preloader">
        <div class="scan-beam"></div>
        <div class="loader-content">
            <div class="loader-text">WELCOME TO SMART GUIDE AI</div>
            <div class="telemetry">
                STATUS: ENCRYPTED_CHANNEL_ACTIVE<br>
                SATELLITE: SG-AI-01 SYNCED<br>
                COORDS: 28.6139° N, 77.2090° E
            </div>
        </div>
    </div>
    <div id="canvas-container"></div>

    <nav>
        <div class="logo">
            Smart Guide AI <span class="ai-tag">AI</span>
        </div>
        <div class="nav-links">
            <?php if(isset($_SESSION['user'])) { ?>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="auth/logout.php" class="nav-link logout">Logout</a>
            <?php } else { ?>
                <a href="auth/login.php" class="nav-link">Sign In</a>
                <a href="auth/register.php" class="nav-cta">Get Started</a>
            <?php } ?>
        </div>
    </nav>

    <main class="hero">
        <div class="hero-content">
            <h1 class="glow-text">Explore Anywhere <br> <span class="cyan-text">Instantly.</span></h1>
            <p class="hero-subtitle">Your personal AI travel expert for every corner of the globe.</p>
            <div class="hero-btns">
                <?php if(!isset($_SESSION['user'])) { ?>
                    <a href="auth/register.php" class="btn-primary">Start Your Adventure</a>
                <?php } else { ?>
                    <a href="dashboard.php" class="btn-primary">Launch Explorer</a>
                <?php } ?>
            </div>
        </div>
    </main>

    <script src="assets/js/loader.js"></script>
    <script src="assets/js/landing-3d.js"></script>
    <script src="assets/js/cursor.js"></script>
</body>
</html>