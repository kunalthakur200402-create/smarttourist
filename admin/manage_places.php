<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/config.php';
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

require_once '../config/database.php';

$city_id = isset($_GET['city_id']) ? $_GET['city_id'] : '';
if (empty($city_id))
    header("Location: dashboard.php");

try {
    $city = $conn->cities->findOne(['_id' => new ObjectId($city_id)]);
} catch (Exception $e) {
    header("Location: dashboard.php");
    exit();
}

// Handle Add Place
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_place'])) {
    $place_name = $_POST['place_name'];
    $description = $_POST['description']; 
    $image_url = $_POST['image_url'];

    $insert = $conn->places->insertOne([
        'city_id' => $city_id,
        'place_name' => $place_name,
        'description' => $description,
        'image_url' => $image_url,
        'created_at' => new UTCDateTime()
    ]);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $conn->places->deleteOne(['_id' => new ObjectId($id)]);
    } catch (Exception $e) {}
    header("Location: manage_places.php?city_id=$city_id");
    exit();
}

$places = $conn->places->find(['city_id' => $city_id]);
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
        <div class="logo">Smart Guide AI Admin</div>
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
            <?php foreach ($places as $place): ?>
                <div class="place-item">
                    <div class="place-info">
                        <h4><?php echo htmlspecialchars($place['place_name']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($place['description'], 0, 80)) . '...'; ?></p>
                    </div>
                    <a href="manage_places.php?city_id=<?php echo $city_id; ?>&delete=<?php echo (string)$place['_id']; ?>"
                        class="delete-btn" onclick="return confirm('Delete this place?')">Delete</a>
                </div>
            <?php endforeach; ?>
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