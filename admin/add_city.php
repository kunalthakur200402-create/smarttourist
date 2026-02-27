<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_name = $conn->real_escape_string($_POST['city_name']);
    $country = $conn->real_escape_string($_POST['country']);
    $description = $conn->real_escape_string($_POST['description']);
    $image_url = $conn->real_escape_string($_POST['image_url']);

    $sql = "INSERT INTO cities (city_name, country, description, image_url) VALUES ('$city_name', '$country', '$description', '$image_url')";

    if ($conn->query($sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add City - WanderWise Admin</title>
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
            max-width: 600px;
            margin: 3rem auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        h2 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
            border-bottom: 2px solid #f7b733;
            display: inline-block;
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        input,
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: 0.3s;
        }

        input:focus,
        textarea:focus {
            border-color: #f7b733;
            outline: none;
            box-shadow: 0 0 0 3px rgba(247, 183, 51, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #0f2027;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: #2c5364;
            transform: translateY(-2px);
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #7f8c8d;
            text-decoration: none;
        }

        .cancel-link:hover {
            color: #333;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="logo">WanderWise Admin</div>
        <a href="dashboard.php" style="color:white; text-decoration:none;">&larr; Dashboard</a>
    </nav>
    <div class="container">
        <div class="card">
            <h2>Add New Destintestation</h2>
            <?php if (isset($error))
                echo "<p style='color:red; margin-bottom:1rem;'>$error</p>"; ?>
            <form method="POST">
                <div class="form-group">
                    <label>City Name</label>
                    <input type="text" name="city_name" required placeholder="e.g. Kyoto">
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" required placeholder="e.g. Japan">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" required placeholder="Brief overview..."></textarea>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" placeholder="https://example.com/kyoto.jpg">
                </div>
                <button type="submit" class="btn-submit">Add City</button>
                <a href="dashboard.php" class="cancel-link">Cancel</a>
            </form>
        </div>
    </div>
</body>

</html>