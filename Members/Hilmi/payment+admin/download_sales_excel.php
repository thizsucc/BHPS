<?php
include 'db_connect.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=bookshop_report.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Write the header row
fputcsv($output, ['Order ID', 'User Name', 'Book Name', 'Order Date', 'Quantity', 'Total Amount (RM)', 'Status']);

// Query to get all order data with user and book info
$sql = "
    SELECT 
        o.OrderID, 
        u.user_Name AS UserName, 
        b.Name AS BookName, 
        o.order_date, 
        o.Quantity, 
        o.Total_Amount, 
        o.Status
    FROM `order` o
    JOIN user u ON o.User_ID = u.User_ID
    JOIN book b ON o.BookID = b.BookID
    ORDER BY o.order_date DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No data found']);
}

fclose($output);
$conn->close();
exit();
?>
