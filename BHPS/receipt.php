<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}
include 'db_connect.php';

if (!isset($_GET['order_id'])) {
    die("Invalid access");
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['userID'];

$stmt = $conn->prepare("
    SELECT o.OrderID, o.BookID, o.order_date, o.address, o.Quantity, o.Status, 
           o.Total_Amount, o.User_ID, b.Name AS book_name, b.Author
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo $order['OrderID']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
        <div class="text-center border-b pb-4 mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Book Heaven</h1>
            <p class="text-gray-500">Order Receipt</p>
        </div>

        <div class="mb-6">
            <p><strong>Order ID:</strong> <?php echo $order['OrderID']; ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
            <p><strong>Status:</strong> <?php echo $order['Status']; ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
        </div>

        <div class="border rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold mb-2">Book Details</h3>
            <p><strong>Book:</strong> <?php echo htmlspecialchars($order['book_name']); ?></p>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($order['Author']); ?></p>
            <p><strong>Quantity:</strong> <?php echo $order['Quantity']; ?></p>
            <p><strong>Total Amount:</strong> RM<?php echo number_format($order['Total_Amount'], 2); ?></p>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="order_user.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Back</a>
            <a href="download_receipt_pdf.php?order_id=<?php echo $order['OrderID']; ?>"
                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Download PDF
            </a>
        </div>
    </div>
</body>

</html>
