<?php
session_start();
include_once 'db_connect.php';

if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];

    $stmt = $conn->prepare("SELECT * FROM book WHERE BookID = ?");
    $stmt->bind_param("s", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode($book);
    } else {
        echo json_encode(['error' => 'Book not found']);
    }
} else {
    echo json_encode(['error' => 'No book ID provided']);
}
