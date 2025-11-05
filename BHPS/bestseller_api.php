<?php
// bestseller_api.php
// Simple API to get/set bestseller books (BookID list) in a file or DB
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'staff') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$file = __DIR__ . '/bestseller_books.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['bestsellers']) && is_array($data['bestsellers'])) {
        file_put_contents($file, json_encode($data['bestsellers']));
        echo json_encode(['success' => true]);
        exit();
    }
    echo json_encode(['error' => 'Invalid data']);
    exit();
}

// GET: return current bestsellers
if (file_exists($file)) {
    $bestsellers = json_decode(file_get_contents($file), true);
    if (!is_array($bestsellers)) $bestsellers = [];
} else {
    $bestsellers = [];
}
echo json_encode($bestsellers);
