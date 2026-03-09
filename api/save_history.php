<?php
require_once '../config/database.php';
use MongoDB\BSON\UTCDateTime;


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['query'])) {
        $query = $data['query'];
        $insert = $conn->search_history->insertOne([
            'user_query' => $query,
            'created_at' => new UTCDateTime()
        ]);
        if ($insert->getInsertedCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save history']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No query provided']);
    }
}
?>