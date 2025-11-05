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

// Fetch order header (grouped) - get payment info and total
$hdr_stmt = $conn->prepare("
    SELECT 
        o.OrderID,
        o.order_date,
        o.address,
        o.Status,
        o.payment_method,
        o.card_number,
        o.bank_name,
        SUM(o.Total_Amount) AS Total_Amount
    FROM `order` o
    WHERE o.OrderID = ? AND o.User_ID = ?
    GROUP BY o.OrderID, o.order_date, o.address, o.Status, o.payment_method, o.card_number, o.bank_name
    LIMIT 1
");
$hdr_stmt->bind_param("ss", $order_id, $user_id);
$hdr_stmt->execute();
$hdr_result = $hdr_stmt->get_result();
$order = $hdr_result->fetch_assoc();
$hdr_stmt->close();

if (!$order) {
    die("Order not found.");
}

// Fetch each order item (multiple rows for same OrderID)
$item_stmt = $conn->prepare("
    SELECT o.OrderItemID, o.BookID, o.Quantity, o.Total_Amount AS item_total, b.Name AS book_name, b.Author, b.Price, b.ImagePath
    FROM `order` o
    LEFT JOIN book b ON o.BookID = b.BookID
    WHERE o.OrderID = ? AND o.User_ID = ?
    ORDER BY o.OrderItemID ASC
");
$item_stmt->bind_param("ss", $order_id, $user_id);
$item_stmt->execute();
$items_result = $item_stmt->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo htmlspecialchars($order['OrderID']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
        <div class="text-center border-b pb-4 mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Book Heaven</h1>
            <p class="text-gray-500">Order Receipt</p>
        </div>

        <div class="mb-6">
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['OrderID']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['Status']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
        </div>

        <div class="border rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold mb-2">Items Ordered</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Book</th>
                            <th class="px-3 py-2 text-left">Author</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Unit Price (RM)</th>
                            <th class="px-3 py-2 text-right">Subtotal (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it):
                            // Determine unit price: prefer book.Price if available, otherwise compute from item_total/Quantity
                            $unit_price = null;
                            if (!empty($it['Price']) && is_numeric($it['Price'])) {
                                $unit_price = (float)$it['Price'];
                            } elseif ($it['Quantity'] > 0 && is_numeric($it['item_total'])) {
                                $unit_price = (float)$it['item_total'] / (int)$it['Quantity'];
                            } else {
                                $unit_price = 0.00;
                            }
                        ?>
                            <tr class="border-t">
                                <td class="px-3 py-2"><?php echo htmlspecialchars($it['book_name'] ?? $it['BookID']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($it['Author']); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo (int)$it['Quantity']; ?></td>
                                <td class="px-3 py-2 text-right"><?php echo number_format($unit_price, 2); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo number_format((float)$it['item_total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold mb-2">Payment Details</h3>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')); ?></p>

            <?php if (!empty($order['payment_method']) && strtolower($order['payment_method']) === 'card'): ?>
                <p><strong>Card (masked):</strong>
                    <?php
                    $cn = preg_replace('/\s+/', '', $order['card_number']);
                    if (!empty($cn) && strlen($cn) >= 8) {
                        $masked = substr($cn, 0, 4) . ' **** **** ' . substr($cn, -4);
                    } else {
                        $masked = htmlspecialchars($order['card_number']);
                    }
                    echo $masked;
                    ?>
                </p>
            <?php elseif (!empty($order['payment_method']) && strtolower($order['payment_method']) === 'bank'): ?>
                <p><strong>Bank:</strong> <?php echo htmlspecialchars($order['bank_name']); ?></p>
            <?php endif; ?>

            <div class="mt-3">
                <p><strong>Grand Total:</strong> RM <?php echo number_format((float)$order['Total_Amount'], 2); ?></p>
            </div>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="order_user.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Back</a>

            <a href="download_receipt_pdf.php?order_id=<?php echo urlencode($order['OrderID']); ?>"
                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Download PDF
            </a>
        </div>
    </div>
</body>

</html>
