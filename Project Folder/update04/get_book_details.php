<?php
include 'db_connect.php';

if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];

    $result = $conn->query("SELECT * FROM book WHERE BookID = '$book_id'");

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode($book);
    } else {
        echo json_encode(['error' => 'Book not found']);
    }
} else {
    echo json_encode(['error' => 'No book ID provided']);
}
