<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/config.php';
require_once '../config/database.php';

$city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;
if ($city_id == 0)
    header("Location: dashboard.php");

$city = $conn->query("SELECT * FROM cities WHERE id = $city_id")->fetch_assoc();

// Handle Add Place
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_place'])) {
    $place_name = $conn->real_escape_string($_POST['place_name']);
    $description = $conn->real_escape_string($_POST['description']); // Manual or AI
    $image_url = $conn->real_escape_string($_POST['image_url']);

    // If AI description is used, it replaces the manual one
    if (isset($_POST['use_ai']) && $_POST['use_ai'] == '1') {
        // AI Logic handled via separate API call usually, but here we might trust the form input
        // For now, assuming description field gets populated.
    }

    $sql = "INSERT INTO places (city_id, place_name, description, image_url) VALUES ('$city_id', '$place_name', '$description', '$image_url')";
    $conn->query($sql);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM places WHERE id = $id");
    header("Location: manage_places.php?city_id=$city_id");
    exit();
}

$places = $conn->query("SELECT * FROM places WHERE city_id = $city_id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Places - <?php echo $city['city_name']; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f4f7f6;
            padding-top: 0;
            display: block;
        }

        .admin-nav {
            background: #0f2027;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        h2 {
            border-bottom: 2px solid #f7b733;
            padding-bottom: 0.5rem;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input,
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            background: #0f2027;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
        }

        .btn-ai {
            background: #f7b733;
            color: #0f2027;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .place-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .place-item:last-child {
            border-bottom: none;
        }

        .place-info h4 {
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .place-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .delete-btn {
            color: #e74c3c;
            text-decoration: none;
            border: 1px solid #e74c3c;
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
        }

        .delete-btn:hover {
            background: #e74c3c;
            color: white;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="logo">WanderWise Admin</div>
        <a href="dashboard.php" style="color:white;">&larr; Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Manage Places for <?php echo $city['city_name']; ?></h2>
            <br><br>

            <h3>Add New Place</h3>
            <form method="POST" id="addPlaceForm">
                <div class="form-group">
                    <label>Place Name</label>
                    <input type="text" name="place_name" id="place_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="4" required></textarea>
                    <button type="button" class="btn btn-ai" onclick="generateAIDescription()">✨ Generate with
                        AI</button>
                    <div id="ai-loading" style="display:none; color: #f7b733; margin-top: 5px;">Generating...</div>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" placeholder="https://...">
                </div>
                <button type="submit" name="add_place" class="btn">Add Place</button>
            </form>
        </div>

        <div class="card">
            <h3>Existing Places</h3>
            <?php while ($place = $places->fetch_assoc()): ?>
                <div class="place-item">
                    <div class="place-info">
                        <h4><?php echo $place['place_name']; ?></h4>
                        <p><?php echo substr($place['description'], 0, 80) . '...'; ?></p>
                    </div>
                    <a href="manage_places.php?city_id=<?php echo $city_id; ?>&delete=<?php echo $place['id']; ?>"
                        class="delete-btn" onclick="return confirm('Delete this place?')">Delete</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function generateAIDescription() {
            const placeName = document.getElementById('place_name').value;
            const descField = document.getElementById('description');
            const loading = document.getElementById('ai-loading');

            if (!placeName) { alert('Please enter a Place Name first.'); return; }

            loading.style.display = 'block';

            fetch('../api/generate_description.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ place_name: placeName })
            })
                .then(res => res.json())
                .then(data => {
                    loading.style.display = 'none';
                    if (data.description) {
                        descField.value = data.description;
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    loading.style.display = 'none';
                    alert('Network error');
                });
        }
    </script>
</body>

</html>