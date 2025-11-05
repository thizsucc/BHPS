<?php
include('db_connect.php');

// --- Validate and gather filters from GET ---
$errors = [];
// default dates: last 30 days
$default_end = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-29 days'));

$start_date = $default_start;
$end_date = $default_end;
$sort_order = 'ASC'; // default

if (isset($_GET['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start_date'])) {
    $start_date = $_GET['start_date'];
}
if (isset($_GET['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['end_date'])) {
    $end_date = $_GET['end_date'];
}
// ensure start <= end
if (strtotime($start_date) > strtotime($end_date)) {
    $errors[] = "Start date cannot be after end date. Using defaults.";
    $start_date = $default_start;
    $end_date = $default_end;
}

// sort parameter allowed values
if (isset($_GET['sort']) && in_array(strtoupper($_GET['sort']), ['ASC', 'DESC'])) {
    $sort_order = strtoupper($_GET['sort']);
}

// Escape (though dates already validated) for safety in SQL strings
$start_esc = $conn->real_escape_string($start_date);
$end_esc = $conn->real_escape_string($end_date);

// === Build WHERE clause used for all queries ===
$where_clause = "
    WHERE o.Status != 'cancelled'
    AND o.order_date >= '{$start_esc}'
    AND o.order_date <= '{$end_esc}'
";

// === Fetch last X days summary ===
$summary_sql = "
    SELECT 
        SUM(o.Total_Amount) AS total_revenue,
        SUM(o.Quantity) AS books_sold
    FROM `order` o
    {$where_clause}
";
$summary_result = $conn->query($summary_sql);
$summary = $summary_result ? $summary_result->fetch_assoc() : ['total_revenue' => 0, 'books_sold' => 0];

// === Fetch daily data for chart ===
$chart_sql = "
    SELECT 
        o.order_date, 
        SUM(o.Total_Amount) AS daily_revenue
    FROM `order` o
    {$where_clause}
    GROUP BY o.order_date
    ORDER BY o.order_date ASC
";
$chart_result = $conn->query($chart_sql);
$dates = [];
$revenues = [];
if ($chart_result) {
    while ($row = $chart_result->fetch_assoc()) {
        $dates[] = date('M j', strtotime($row['order_date']));
        $revenues[] = (float)$row['daily_revenue'];
    }
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
    {$where_clause}
    ORDER BY o.order_date {$sort_order}, o.OrderID ASC
";
$sales_result = $conn->query($sales_sql);

// Helper to keep GET params in links/forms
function esc_attr($s)
{
    return htmlspecialchars($s, ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Report (Date Filter & Sort)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            #report-area {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                box-shadow: none !important;
            }

            .no-print {
                display: none !important;
            }
        }

        /* PDF-specific styles */
        .pdf-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
            box-sizing: border-box;
        }

        .pdf-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .pdf-section {
            margin-bottom: 15px;
        }

        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 10px;
        }

        .pdf-table th {
            background-color: #2563eb;
            color: white;
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #1d4ed8;
            font-weight: bold;
        }

        .pdf-table td {
            padding: 4px;
            border: 1px solid #d1d5db;
            line-height: 1.2;
        }

        .pdf-chart-container {
            height: 120px;
            margin: 10px 0;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }

        .summary-card {
            background: #dbeafe;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #93c5fd;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin: 15px 0 8px 0;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
        }

        /* Enhanced UI Styles */
        .filter-card {
            background: #1e40af;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .filter-input {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .filter-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        .filter-label {
            color: white;
            font-weight: 500;
            margin-bottom: 6px;
            display: block;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .apply-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(5, 150, 105, 0.4);
        }

        .reset-btn {
            color: #e2e8f0;
            transition: all 0.2s ease;
            padding: 10px 16px;
            border-radius: 6px;
        }

        .reset-btn:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Enhanced UI Section -->
    <div class="no-print max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Sales Report Dashboard</h1>
            <p class="text-lg text-gray-600">Generated on <?= date('d M Y, h:i A') ?></p>
        </div>

        <!-- Enhanced Filter Card -->
        <div class="filter-card p-6 mb-8">
            <div class="text-center mb-6">
                <h2 class="text-xl font-semibold text-white">Report Filters</h2>
                <p class="text-blue-100 mt-1">Select date range to generate report</p>
            </div>

            <form id="filterForm" method="get" class="flex flex-row items-end justify-center space-x-4">
                <!-- Start Date -->
                <div class="flex-1">
                    <label for="start_date" class="filter-label">
                        <i class="fas fa-calendar-alt mr-2"></i>Start Date
                    </label>
                    <input type="date" id="start_date" name="start_date" value="<?= esc_attr($start_date) ?>"
                        class="filter-input w-full">
                </div>

                <!-- End Date -->
                <div class="flex-1">
                    <label for="end_date" class="filter-label">
                        <i class="fas fa-calendar-day mr-2"></i>End Date
                    </label>
                    <input type="date" id="end_date" name="end_date" value="<?= esc_attr($end_date) ?>"
                        class="filter-input w-full">
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3 flex-shrink-0">
                    <button type="submit" class="apply-btn flex items-center justify-center whitespace-nowrap">
                        <i class="fas fa-play-circle mr-2"></i>
                        Generate
                    </button>
                    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>"
                        class="reset-btn flex items-center justify-center whitespace-nowrap">
                        <i class="fas fa-redo mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>

            <?php if (!empty($errors)): ?>
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        <span class="text-red-700 text-sm"><?= htmlspecialchars(implode(' ', $errors)) ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">RM<?= number_format($summary['total_revenue'] ?? 0, 2) ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Books Sold</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($summary['books_sold'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-book text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Reporting Period</p>
                        <h3 class="text-lg font-bold text-gray-800 mt-1">
                            <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>
                        </h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-calendar text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Content -->
    <div class="pdf-page" id="report-area">
        <!-- Header -->
        <div class="pdf-header">
            <h1 style="font-size: 20pt; font-weight: bold; color: #1e40af; margin: 0 0 5px 0;">Sales Report</h1>
            <p style="margin: 2px 0; color: #6b7280; font-size: 10pt;">Generated on <?= date('d M Y, h:i A') ?></p>
            <p style="margin: 2px 0; color: #6b7280; font-size: 10pt;">Period: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></p>
        </div>

        <!-- Summary -->
        <div class="pdf-section">
            <div class="section-title">Summary</div>
            <div class="summary-cards">
                <div class="summary-card">
                    <h3 style="margin: 0 0 5px 0; font-size: 10pt; color: #374151;">Total Revenue</h3>
                    <p style="margin: 0; font-size: 16pt; font-weight: bold; color: #059669;">RM <?= number_format($summary['total_revenue'] ?? 0, 2) ?></p>
                </div>
                <div class="summary-card">
                    <h3 style="margin: 0 0 5px 0; font-size: 10pt; color: #374151;">Books Sold</h3>
                    <p style="margin: 0; font-size: 16pt; font-weight: bold; color: #2563eb;"><?= $summary['books_sold'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="pdf-section">
            <div class="section-title">Sales Trend</div>
            <div class="pdf-chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Table -->
        <div class="pdf-section">
            <div class="section-title">Sales Details</div>
            <table class="pdf-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Order ID</th>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 40%;">Book Name</th>
                        <th style="width: 10%; text-align: center;">Qty</th>
                        <th style="width: 20%; text-align: right;">Total (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sales_result && $sales_result->num_rows > 0): ?>
                        <?php while ($row = $sales_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['OrderID']) ?></td>
                                <td><?= htmlspecialchars($row['order_date']) ?></td>
                                <td style="font-size: 8pt;"><?= htmlspecialchars($row['BookName']) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($row['Quantity']) ?></td>
                                <td style="text-align: right;"><?= number_format($row['Total_Amount'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 15px; color: #6b7280;">No records found for selected range.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #d1d5db; text-align: center;">
            <p style="color: #9ca3af; font-size: 8pt; margin: 0;">© <?= date('Y') ?> Book Heaven | Sales Report</p>
        </div>
    </div>

    <!-- Download Button -->
    <div class="no-print text-center mt-8 mb-12">
        <button id="downloadBtn" onclick="downloadPDF()"
            class="bg-blue text-white px-8 py-4 rounded-xl text-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i class="fas fa-download mr-3"></i>
            Download PDF Report
        </button>
    </div>

    <script>
        // Chart with smaller size for PDF
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
                    fill: true,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'RM ' + value;
                            },
                            font: {
                                size: 8
                            }
                        },
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 8
                            },
                            maxTicksLimit: 8
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 9
                            },
                            boxWidth: 12
                        }
                    }
                }
            }
        });

        // PDF Download with optimized settings for single page
        function downloadPDF() {
            const btn = document.getElementById('downloadBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-3"></i>Generating PDF...';

            const element = document.getElementById('report-area');

            // Wait for chart to render completely
            setTimeout(() => {
                const options = {
                    margin: [5, 5, 5, 5],
                    filename: 'Sales_Report_<?= date('Ymd_His') ?>.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.95
                    },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        width: 800,
                        height: 1120, // A4 ratio
                        scrollX: 0,
                        scrollY: 0
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait',
                        compress: true
                    }
                };

                html2pdf().set(options).from(element).save().finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-download mr-3"></i>Download PDF Report';
                });
            }, 1000);
        }
    </script>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>

</html>