<?php
require 'db_connect.php';

// --- Fetch Sales Data ---
$query = $conn->query("
    SELECT o.OrderID, o.order_date, b.Name AS title, b.Category,
           o.Quantity, b.Price, (o.Quantity * b.Price) AS total
    FROM `order` o
    JOIN book b ON o.BookID = b.BookID
    ORDER BY o.order_date DESC
");
$sales_data = $query->fetch_all(MYSQLI_ASSOC);

// --- Calculate Summary ---
$total_revenue = array_sum(array_column($sales_data, 'total'));
$total_books = array_sum(array_column($sales_data, 'Quantity'));
$avg_order_value = $total_books > 0 ? $total_revenue / $total_books : 0;
$date_generated = date('d M Y, h:i A');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Heaven - Sales Report</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .invoice-wrapper {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, #17a34b, #12853cff);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }

        .invoice-header h1 {
            margin: 0;
            font-size: 26px;
        }

        .summary-cards {
            display: flex;
            justify-content: space-around;
            text-align: center;
            background: #f8fafc;
            padding: 20px;
            flex-wrap: wrap;
        }

        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            width: 250px;
            margin: 10px;
        }

        .summary-card h2 {
            font-size: 16px;
            color: #17a34b;
            margin-bottom: 8px;
        }

        .summary-card p {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px 10px;
            text-align: center;
            font-size: 14px;
        }

        th {
            background: #17a34b;
            color: white;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .footer {
            text-align: center;
            padding: 15px;
            font-size: 13px;
            color: #17a34b;
            border-top: 1px solid #e5e7eb;
        }

        .invoice-btn {
            display: inline-block;
            background: #17a34b;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin: 20px auto;
            transition: background 0.3s;
        }

        .invoice-btn:hover {
            background: #0b5827ff
        }

        .download-container {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="invoice-header">
            <h1><i class="fa-solid fa-book"></i> Book Heaven - Sales Report</h1>
            <p>Generated on <?= $date_generated ?></p>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <h2>Total Revenue</h2>
                <p>RM <?= number_format($total_revenue, 2) ?></p>
            </div>
            <div class="summary-card">
                <h2>Total Books Sold</h2>
                <p><?= $total_books ?></p>
            </div>
            <div class="summary-card">
                <h2>Average Order Value</h2>
                <p>RM <?= number_format($avg_order_value, 2) ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Book Title</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Price (RM)</th>
                    <th>Total (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_data as $row): ?>
                    <tr>
                        <td><?= $row['OrderID'] ?></td>
                        <td><?= date('Y-m-d', strtotime($row['order_date'])) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['Category']) ?></td>
                        <td><?= $row['Quantity'] ?></td>
                        <td><?= number_format($row['Price'], 2) ?></td>
                        <td><?= number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="download-container">
            <a href="download_sales_excel.php" class="invoice-btn">
                <i class="fa-solid fa-download"></i> Download excel
            </a>
        </div>

        <div class="footer">
            © <?= date('Y') ?> Book Heaven. All Rights Reserved.
        </div>
    </div>
</body>
</html>
