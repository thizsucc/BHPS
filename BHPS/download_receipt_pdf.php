<?php
require('fpdf/fpdf.php');
include 'db_connect.php';
session_start();

if (!isset($_GET['order_id']) || !isset($_SESSION['userID'])) {
    die("Unauthorized access");
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['userID'];

$stmt = $conn->prepare("
    SELECT o.OrderID, o.order_date, o.address, o.Quantity, o.Status, 
           o.Total_Amount, o.payment_method, o.bank_name, o.card_number, 
           b.Name AS book_name, b.Author
    FROM `order` o
    LEFT JOIN book b ON o.BookID = b.BookID
    WHERE o.OrderID = ? AND o.User_ID = ?
");
$stmt->bind_param("is", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Book Heaven - Order Receipt', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 8, 'Order ID: ' . $order['OrderID'], 0, 1);
$pdf->Cell(100, 8, 'Order Date: ' . date('F j, Y g:i A', strtotime($order['order_date'])), 0, 1);
$pdf->Cell(100, 8, 'Status: ' . $order['Status'], 0, 1);
$pdf->Cell(100, 8, 'Address: ' . $order['address'], 0, 1);
$pdf->Ln(8);

$pdf->Cell(100, 8, 'Payment Method: ' . $order['payment_method'], 0, 1);
$pdf->Cell(100, 8, 'Bank: ' . $order['bank_name'], 0, 1);
$pdf->Cell(100, 8, 'Card Number: ' . substr($order['card_number'], -4), 0, 1); // show only last 4 digits
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 8, 'Book Details', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 8, 'Book Name: ' . $order['book_name'], 0, 1);
$pdf->Cell(100, 8, 'Author: ' . $order['Author'], 0, 1);
$pdf->Cell(100, 8, 'Quantity: ' . $order['Quantity'], 0, 1);
$pdf->Cell(100, 8, 'Total Amount: RM' . number_format($order['Total_Amount'], 2), 0, 1);

$pdf->Ln(20);
$pdf->Cell(190, 8, 'Thank you for shopping with Book Heaven!', 0, 1, 'C');

$pdf->Output('D', 'Receipt_Order_' . $order['OrderID'] . '.pdf');
exit();
