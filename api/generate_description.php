<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
    echo json_encode(['error' => 'OpenAI API Key not configured']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['place_name'])) {
    $placeName = $data['place_name'];
    $prompt = "Write a short, engaging description for a tourist place called '$placeName'. Keep it under 50 words.";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful travel guide assistant.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 100
    ]));

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Request Error:' . curl_error($ch)]);
    } else {
        $response = json_decode($result, true);
        if (isset($response['choices'][0]['message']['content'])) {
            echo json_encode(['description' => trim($response['choices'][0]['message']['content'])]);
        } else {
            echo json_encode(['error' => 'API Error', 'details' => $response]);
        }
    }
    curl_close($ch);
} else {
    echo json_encode(['error' => 'Place Name not provided']);
}
?>