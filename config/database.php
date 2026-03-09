<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->smart_guide; // Using the database 'smart_guide'
    $conn = $db;
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
