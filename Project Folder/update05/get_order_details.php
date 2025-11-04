<?php
include 'db_connect.php';

// Get order_id from query string
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    // Join book table to get book name and other details
    $stmt = $conn->prepare("SELECT o.*, b.Name as book_name, b.Author, b.Category, b.Format, b.ImagePath FROM `order` o LEFT JOIN book b ON o.BookID = b.BookID WHERE o.OrderID = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid OrderID']);
}

$conn->close();
?>