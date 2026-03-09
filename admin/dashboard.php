<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';
$cities = $conn->cities->find();
$total_cities = $conn->cities->countDocuments();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Smart Guide AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f4f7f6;
            display: block;
            padding-top: 0;
        }

        .admin-nav {
            background: #0f2027;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #f7b733;
        }

        .stat-card h3 {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 0.5rem;
        }

        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-add {
            background: #f7b733;
            color: #0f2027;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(247, 183, 51, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1.2rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #fafafa;
            font-weight: 600;
            color: #555;
        }

        tr:hover {
            background: #fafafa;
        }

        .actions a {
            margin-right: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .actions .edit {
            color: #3498db;
        }

        .actions .delete {
            color: #e74c3c;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="logo">Smart Guide AI Admin</div>
        <a href="logout.php" style="color: rgba(255,255,255,0.8); text-decoration: none;">Logout</a>
    </nav>

    <div class="admin-container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Cities</h3>
                <p><?php echo $total_cities; ?></p>
            </div>
            <!-- Additional stats could go here -->
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>Manage Cities</h2>
                <div>
                    <a href="diagnostics.php" style="margin-right: 15px; color: #7f8c8d; text-decoration: none; font-size: 0.9rem;">🛠 API Diagnostics</a>
                    <a href="add_city.php" class="btn-add">+ Add New City</a>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>City</th>
                        <th>Country</th>
                        <th>Weather Info</th>
                        <th>Distance/Dir.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cities as $row): ?>
                        <tr>
                            <td style="font-weight: 600; color: #2c3e50;">
                                <?php echo htmlspecialchars($row['city_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['country']); ?></td>
                            <td style="font-size: 0.85rem;">
                                <?php if (isset($row['weather_temp'])): ?>
                                    <b><?php echo htmlspecialchars($row['weather_temp']); ?>°C</b> - <?php echo htmlspecialchars($row['weather_desc']); ?>
                                <?php else: ?>
                                    <span style="color: #ccc;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.85rem;">
                                <?php if (isset($row['directions'])): ?>
                                    <div title="<?php echo htmlspecialchars($row['directions']); ?>" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($row['directions']); ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #ccc;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="manage_places.php?city_id=<?php echo (string)$row['_id']; ?>" class="edit">Manage Places</a>
                                <!-- Add edit/delete for city here if needed -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>