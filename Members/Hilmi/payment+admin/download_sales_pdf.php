<?php
include('db_connect.php');

// === Fetch last 30 days summary ===
$summary_sql = "
    SELECT 
        SUM(o.Total_Amount) AS total_revenue,
        SUM(o.Quantity) AS books_sold
    FROM `order` o
    WHERE o.Status != 'cancelled'
    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
";
$summary_result = $conn->query($summary_sql);
$summary = $summary_result->fetch_assoc();

// === Fetch daily data for chart ===
$chart_sql = "
    SELECT 
        o.order_date, 
        SUM(o.Total_Amount) AS daily_revenue
    FROM `order` o
    WHERE o.Status != 'cancelled'
    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY o.order_date
    ORDER BY o.order_date ASC
";
$chart_result = $conn->query($chart_sql);
$dates = [];
$revenues = [];
while ($row = $chart_result->fetch_assoc()) {
    $dates[] = $row['order_date'];
    $revenues[] = $row['daily_revenue'];
}

// === Fetch sales table ===
$sales_sql = "
    SELECT 
        o.OrderID, 
        o.order_date, 
        b.Name AS BookName, 
        o.Quantity, 
        o.Total_Amount
    FROM `order` o
    JOIN book b ON o.BookID = b.BookID
    WHERE o.Status != 'cancelled'
    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY o.order_date ASC
";
$sales_result = $conn->query($sales_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report (Last 30 Days)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-gray-100">
<div class="max-w-6xl mx-auto p-8 bg-white shadow-md mt-10 rounded-xl" id="report-area">
    <h1 class="text-3xl font-bold text-center text-blue-700 mb-6">Sales Report (Last 30 Days)</h1>
    <p class="text-center text-gray-500 mb-8">Generated on <?= date('d M Y, h:i A') ?></p>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        <div class="bg-blue-50 p-6 rounded-xl text-center">
            <h2 class="text-gray-600 text-lg font-semibold">Total Revenue</h2>
            <p class="text-3xl font-bold text-green-600 mt-2">RM <?= number_format($summary['total_revenue'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-blue-50 p-6 rounded-xl text-center">
            <h2 class="text-gray-600 text-lg font-semibold">Books Sold</h2>
            <p class="text-3xl font-bold text-blue-600 mt-2"><?= $summary['books_sold'] ?? 0 ?></p>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-gray-50 p-6 rounded-xl mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Sales Trend (Last 30 Days)</h2>
        <canvas id="salesChart" height="120"></canvas>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Order ID</th>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Book Name</th>
                    <th class="px-4 py-2 text-right">Quantity</th>
                    <th class="px-4 py-2 text-right">Total (RM)</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $sales_result->fetch_assoc()): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2"><?= $row['OrderID'] ?></td>
                    <td class="px-4 py-2"><?= $row['order_date'] ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['BookName']) ?></td>
                    <td class="px-4 py-2 text-right"><?= $row['Quantity'] ?></td>
                    <td class="px-4 py-2 text-right"><?= number_format($row['Total_Amount'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <p class="text-center text-gray-400 text-sm mt-8">© <?= date('Y') ?> Bookstore | Sales Report (Last 30 Days)</p>
</div>

<!-- Download Button -->
<div class="text-center mt-8 mb-12">
    <button onclick="downloadPDF()" class="bg-blue-600 text-white px-6 py-3 rounded-lg text-sm hover:bg-blue-700 transition">
        Download PDF
    </button>
</div>

<script>
    // Chart
    const ctx = document.getElementById('salesChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Revenue (RM)',
                data: <?= json_encode($revenues) ?>,
                borderColor: 'rgb(37,99,235)',
                backgroundColor: 'rgba(37,99,235,0.3)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true },
                x: { title: { display: true, text: 'Date' } }
            }
        }
    });

    // PDF Download
    function downloadPDF() {
        const element = document.getElementById('report-area');
        html2pdf().set({
            margin: 0.5,
            filename: 'Sales_Report_Last30Days.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        }).from(element).save();
    }
</script>
</body>
</html>
