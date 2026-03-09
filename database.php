<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->smart_guide;
    // We keep $conn as an object that mimics some basic functionality if needed, 
    // but primarily we will use $db for collection access.
    $conn = $db; 
} catch (Exception $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>