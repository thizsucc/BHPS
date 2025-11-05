<?php
include 'db_connect.php';
header('Content-Type: application/json');

// Get books that are not currently in promotion for the "Add Promotion" modal
$result = $conn->query("SELECT BookID, Name, Author, Price FROM book WHERE is_promotion = 0 ORDER BY Name");
$books = [];

while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

echo json_encode($books);
$conn->close();
