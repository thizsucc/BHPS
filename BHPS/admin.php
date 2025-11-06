<?php
include 'db_connect.php';
session_start();

// Get filter parameters
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'last_30_days';
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'sales_summary';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Staff Management Actions
if (isset($_POST['add_staff'])) {
    $staff_id = $_POST['staff_id'];
    $staff_name = $_POST['staff_name'];
    $jawatan = $_POST['jawatan'];
    $password = $_POST['password'];

    $sql = "INSERT INTO staff (StaffID, Staff_Name, Jawatan, Password) 
            VALUES ('$staff_id', '$staff_name', '$jawatan', '$password')";
    $conn->query($sql);
}

if (isset($_POST['edit_staff'])) {
    $staff_id = $_POST['staff_id'];
    $staff_name = $_POST['staff_name'];
    $jawatan = $_POST['jawatan'];
    $password = $_POST['password'];

    $sql = "UPDATE staff SET Staff_Name='$staff_name', Jawatan='$jawatan', Password='$password' 
            WHERE StaffID='$staff_id'";
    $conn->query($sql);
}

if (isset($_GET['delete_staff'])) {
    $staff_id = $_GET['delete_staff'];
    $sql = "DELETE FROM staff WHERE StaffID='$staff_id'";
    $conn->query($sql);
}

// Calculate date range based on selection
$date_condition = "";
switch ($date_range) {
    case 'last_7_days':
        $date_condition = "o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'last_30_days':
        $date_condition = "o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'last_90_days':
        $date_condition = "o.order_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
        break;
    case 'year_to_date':
        $date_condition = "YEAR(o.order_date) = YEAR(CURDATE())";
        break;
    default:
        $date_condition = "o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

// Category filter condition
$category_condition = "";
if ($category_filter != 'all') {
    $category_condition = "AND b.Category = '" . $conn->real_escape_string($category_filter) . "'";
}

$admin_id = $_SESSION['userID'];
$admin_name = $_SESSION['name'];
$admin_jawatan = $_SESSION['jawatan'];

// Total Revenue and Books Sold with filters
$revenue_sql = "SELECT SUM(b.Price * o.Quantity) as total_revenue, SUM(o.Quantity) as books_sold 
                FROM `order` o 
                JOIN book b ON o.BookID = b.BookID 
                WHERE $date_condition 
                AND o.Status != 'cancelled'
                $category_condition";
$revenue_result = $conn->query($revenue_sql);
if ($revenue_result->num_rows > 0) {
    $row = $revenue_result->fetch_assoc();
    $total_revenue = $row['total_revenue'] ? $row['total_revenue'] : 0;
    $books_sold = $row['books_sold'] ? $row['books_sold'] : 0;
} else {
    $total_revenue = 0;
    $books_sold = 0;
}

// Average Order Value with filters
$avg_sql = "SELECT AVG(b.Price * o.Quantity) as avg_order_value 
            FROM `order` o 
            JOIN book b ON o.BookID = b.BookID 
            WHERE $date_condition 
            AND o.Status != 'cancelled'
            $category_condition";
$avg_result = $conn->query($avg_sql);
if ($avg_result->num_rows > 0) {
    $row = $avg_result->fetch_assoc();
    $avg_order_value = $row['avg_order_value'] ? round($row['avg_order_value'], 2) : 0;
} else {
    $avg_order_value = 0;
}

// Sales data for chart with filters
$sales_data = array_fill(0, 30, 0);
$sales_labels = [];

// Generate labels based on date range
if ($date_range == 'last_7_days') {
    for ($i = 6; $i >= 0; $i--) {
        $sales_labels[] = date('M j', strtotime("-$i days"));
    }
} else {
    for ($i = 29; $i >= 0; $i--) {
        $sales_labels[] = date('M j', strtotime("-$i days"));
    }
}

// Get daily sales data with filters
$days = $date_range == 'last_7_days' ? 7 : 30;
$chart_sql = "SELECT DATE(o.order_date) as sale_date, SUM(b.Price * o.Quantity) as daily_revenue
              FROM `order` o 
              JOIN book b ON o.BookID = b.BookID 
              WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) 
              AND o.Status != 'cancelled'
              $category_condition
              GROUP BY DATE(o.order_date) 
              ORDER BY sale_date";
$chart_result = $conn->query($chart_sql);

if ($chart_result->num_rows > 0) {
    while ($row = $chart_result->fetch_assoc()) {
        $date_index = array_search(date('M j', strtotime($row['sale_date'])), $sales_labels);
        if ($date_index !== false) {
            $sales_data[$date_index] = $row['daily_revenue'];
        }
    }
}

// Analytics Data - Sales by Category with filters
$category_data = [];
$category_labels = [];
$category_colors = ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#6b7280'];

$category_sql = "SELECT b.Category, SUM(b.Price * o.Quantity) as category_revenue, 
                        SUM(o.Quantity) as category_quantity
                 FROM `order` o 
                 JOIN book b ON o.BookID = b.BookID 
                 WHERE $date_condition 
                 AND o.Status != 'cancelled'
                 GROUP BY b.Category 
                 ORDER BY category_revenue DESC";
$category_result = $conn->query($category_sql);

if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $category_labels[] = $row['Category'] ? $row['Category'] : 'Uncategorized';
        $category_data[] = $row['category_revenue'];
    }
} else {
    // Default data if no categories found
    $category_labels = ['Fiction', 'Non-Fiction', 'Academic', 'Children'];
    $category_data = [35, 25, 20, 15];
}

// Top Selling Books for Analytics with filters
$top_books_sql = "SELECT b.Name, b.Category, SUM(o.Quantity) as total_sold,
                         SUM(b.Price * o.Quantity) as total_revenue
                  FROM `order` o 
                  JOIN book b ON o.BookID = b.BookID 
                  WHERE $date_condition 
                  AND o.Status != 'cancelled'
                  $category_condition
                  GROUP BY b.BookID, b.Name, b.Category
                  ORDER BY total_sold DESC 
                  LIMIT 5";
$top_books_result = $conn->query($top_books_sql);

// Inventory Status
$inventory_sql = "SELECT 
                    COUNT(*) as total_books,
                    SUM(Quantity) as total_stock,
                    SUM(CASE WHEN Quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                    SUM(CASE WHEN Quantity < 10 THEN 1 ELSE 0 END) as low_stock
                  FROM book";
$inventory_result = $conn->query($inventory_sql);
$inventory_data = $inventory_result->fetch_assoc();

// Fetch all staff for staff management
$staff_sql = "SELECT * FROM staff ORDER BY StaffID";
$staff_result = $conn->query($staff_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dropdown-menu {
            visibility: hidden;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            display: block;
            pointer-events: none;
        }

        .dropdown:hover .dropdown-menu {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .dropdown:hover .fa-chevron-down {
            transform: rotate(180deg);
        }

        .status-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .search-box:focus-within {
            border-color: #3b82f6;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-shipped {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #dc2626;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <!-- Top Announcement Bar -->
    <div class="bg-blue-800 text-white text-center py-2 px-4 text-sm">
        Admin Portal | Book Heaven Reporting System
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <!-- Top Header -->
            <div class="flex items-center justify-between py-4 border-b">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center">
                        <img src="img/logo.png" alt="Logo" class="h-10 mr-3" style="height: 40px; width: auto;">
                        <div>
                            <span class="text-2xl font-bold text-blue-800">Book Heaven</span>
                            <span class="text-xs text-gray-600 block">Admin Reports</span>
                        </div>
                    </a>

                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button id="adminDropdownBtn" onclick="toggleAdminDropdown()" class="flex items-center text-gray-700 hover:text-blue-600 focus:outline-none">
                            <i class="fas fa-user-circle text-xl mr-1"></i>
                            <span><?php echo htmlspecialchars($admin_name); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200"></i>
                        </button>
                        <div id="adminDropdownMenu" class="absolute right-0 bg-white shadow-lg rounded mt-2 py-2 w-48 z-50" style="display:none;">
                            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 transition-colors duration-150">
                                <i class="fas fa-sign-out-alt mr-2 text-gray-600"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:block">
                <ul class="flex items-center space-x-6 py-3">
                    <li class="tab-link active" data-tab="reports">
                        <a href="#" class="text-blue-600 font-medium border-b-2 border-blue-600 pb-1">Reports</a>
                    </li>
                    <li class="tab-link" data-tab="analytics">
                        <a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Analytics</a>
                    </li>
                    <li class="tab-link" data-tab="staff">
                        <a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Staff Management</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Reports Tab -->
        <div id="reports-tab" class="tab-content active">
            <!-- ... (existing reports tab content remains exactly the same) ... -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Report Generation</h1>
            </div>

            <!-- Report Filters -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-medium text-gray-800">Report Parameters</h2>
                </div>
                <div class="p-6">
                    <form method="GET" action="admin.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <select name="date_range" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="last_7_days" <?php echo ($date_range == 'last_7_days') ? 'selected' : ''; ?>>Last 7 days</option>
                                <option value="last_30_days" <?php echo ($date_range == 'last_30_days' || !isset($_GET['date_range'])) ? 'selected' : ''; ?>>Last 30 days</option>
                                <option value="last_90_days" <?php echo ($date_range == 'last_90_days') ? 'selected' : ''; ?>>Last 90 days</option>
                                <option value="year_to_date" <?php echo ($date_range == 'year_to_date') ? 'selected' : ''; ?>>Year to date</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                            <select name="report_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="sales_summary" <?php echo ($report_type == 'sales_summary' || !isset($_GET['report_type'])) ? 'selected' : ''; ?>>Sales Summary</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all" <?php echo ($category_filter == 'all' || !isset($_GET['category'])) ? 'selected' : ''; ?>>All Categories</option>
                                <option value="Fiction" <?php echo ($category_filter == 'Fiction') ? 'selected' : ''; ?>>Fiction</option>
                                <option value="Children" <?php echo ($category_filter == 'Children') ? 'selected' : ''; ?>>Children</option>
                                <option value="Academic" <?php echo ($category_filter == 'Academic') ? 'selected' : ''; ?>>Academic</option>
                                <option value="Business" <?php echo ($category_filter == 'Business') ? 'selected' : ''; ?>>Business</option>
                                <option value="Food & Drink" <?php echo ($category_filter == 'Food & Drink') ? 'selected' : ''; ?>>Food & Drink</option>
                                <option value="Romance" <?php echo ($category_filter == 'Romance') ? 'selected' : ''; ?>>Romance</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                                Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-600 text-sm">Total Revenue</p>
                            <h3 class="text-2xl font-bold text-gray-800">RM<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-dollar-sign text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Last 30 days</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-600 text-sm">Books Sold</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($books_sold); ?></h3>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-book text-green-600"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Last 30 days</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-600 text-sm">Avg. Order Value</p>
                            <h3 class="text-2xl font-bold text-gray-800">RM<?php echo number_format($avg_order_value, 2); ?></h3>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-shopping-cart text-yellow-600"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Last 30 days</p>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-800">
                        Sales Performance
                        <?php
                        echo "(" . ucfirst(str_replace('_', ' ', $date_range)) .
                            ($category_filter != 'all' ? " - " . htmlspecialchars($category_filter) : "") . ")";
                        ?>
                    </h2>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-xs <?php echo ($date_range == 'last_7_days') ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800'; ?> rounded"
                            onclick="updateDateRange('last_7_days')">7D</button>
                        <button class="px-3 py-1 text-xs <?php echo ($date_range == 'last_30_days' || !isset($_GET['date_range'])) ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800'; ?> rounded"
                            onclick="updateDateRange('last_30_days')">30D</button>
                        <button class="px-3 py-1 text-xs <?php echo ($date_range == 'last_90_days') ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800'; ?> rounded"
                            onclick="updateDateRange('last_90_days')">90D</button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-medium text-gray-800">Recent Orders</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OrderID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BookID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer_ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            // Fetch orders from database
                            $sql = "SELECT * FROM `order` ORDER BY order_date DESC LIMIT 10";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $status_class = "";
                                    switch (strtolower($row["Status"])) {
                                        case "pending":
                                            $status_class = "status-pending";
                                            break;
                                        case "completed":
                                            $status_class = "status-completed";
                                            break;
                                        case "shipped":
                                            $status_class = "status-shipped";
                                            break;
                                        case "cancelled":
                                            $status_class = "status-cancelled";
                                            break;
                                        default:
                                            $status_class = "status-pending";
                                    }

                                    echo "<tr>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["OrderID"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["BookID"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["order_date"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 truncate max-w-xs'>" . $row["address"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["Quantity"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["User_ID"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap'><span class='status-pill $status_class'>" . $row["Status"] . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center'>No orders found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Report Actions -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-medium text-gray-800">Report Actions</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6 justify-center">
                        <div class="bg-blue-50 p-6 rounded-lg text-center flex-1 max-w-md">
                            <div class="bg-blue-100 p-3 rounded-full inline-flex mb-4">
                                <i class="fas fa-file-pdf text-blue-600 text-2xl"></i>
                            </div>
                            <h3 class="font-medium text-blue-800 mb-2">Export as PDF</h3>
                            <p class="text-sm text-blue-600 mb-4">Download a printable PDF version of this report</p>
                            <form action="download_sales_pdf.php" method="GET">
                                <input type="hidden" name="date_range" value="<?php echo htmlspecialchars($date_range); ?>">
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                                <input type="hidden" name="report_type" value="<?php echo htmlspecialchars($report_type); ?>">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 w-full">
                                    Download PDF
                                </button>
                            </form>
                        </div>

                        <div class="bg-green-50 p-6 rounded-lg text-center flex-1 max-w-md">
                            <div class="bg-green-100 p-3 rounded-full inline-flex mb-4">
                                <i class="fas fa-file-excel text-green-600 text-2xl"></i>
                            </div>
                            <h3 class="font-medium text-green-800 mb-2">Export as Excel</h3>
                            <p class="text-sm text-green-600 mb-4">Download an Excel spreadsheet for further analysis</p>
                            <form action="download_report.php" method="POST">
                                <input type="hidden" name="date_range" value="<?php echo htmlspecialchars($date_range); ?>">
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                                <input type="hidden" name="report_type" value="<?php echo htmlspecialchars($report_type); ?>">
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 w-full">
                                    Download Excel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Sales Analytics</h1>
            </div>
        </div>

        <!-- Analytics Charts - Single Chart Now -->
        <div class="grid grid-cols-1 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-medium text-gray-800">Sales by Category (Last 30 Days)</h2>
                </div>
                <div class="p-6">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling Books -->
        <div class="bg-white rounded-lg shadowSm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Top Selling Books (Last 30 Days)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Units Sold</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        if ($top_books_result->num_rows > 0) {
                            while ($row = $top_books_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . $row["Name"] . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $row["Category"] . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["total_sold"] . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>RM" . number_format($row["total_revenue"], 2) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center'>No sales data found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Performance Metrics</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-green-800">Average Order Value</h3>
                        <i class="fas fa-shopping-cart text-green-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-green-800">RM<?php echo number_format($avg_order_value, 2); ?></p>
                    <p class="text-sm text-green-600">Last 30 days</p>
                </div>

                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-purple-800">Total Books Sold</h3>
                        <i class="fas fa-book text-purple-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-purple-800"><?php echo number_format($books_sold); ?></p>
                    <p class="text-sm text-purple-600">Last 30 days</p>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-blue-800">Total Revenue</h3>
                        <i class="fas fa-dollar-sign text-blue-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-blue-800">RM<?php echo number_format($total_revenue, 2); ?></p>
                    <p class="text-sm text-blue-600">Last 30 days</p>
                </div>
            </div>
        </div>

        <!-- Inventory Status -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Inventory Status</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="bg-blue-100 p-4 rounded-full inline-flex mb-3">
                        <i class="fas fa-book text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-medium text-blue-800">Total Books</h3>
                    <p class="text-2xl font-bold text-blue-800"><?php echo $inventory_data['total_books']; ?></p>
                </div>

                <div class="text-center">
                    <div class="bg-green-100 p-4 rounded-full inline-flex mb-3">
                        <i class="fas fa-boxes text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-medium text-green-800">Total Stock</h3>
                    <p class="text-2xl font-bold text-green-800"><?php echo $inventory_data['total_stock']; ?></p>
                </div>

                <div class="text-center">
                    <div class="bg-yellow-100 p-4 rounded-full inline-flex mb-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="font-medium text-yellow-800">Low Stock</h3>
                    <p class="text-2xl font-bold text-yellow-800"><?php echo $inventory_data['low_stock']; ?></p>
                </div>

                <div class="text-center">
                    <div class="bg-red-100 p-4 rounded-full inline-flex mb-3">
                        <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="font-medium text-red-800">Out of Stock</h3>
                    <p class="text-2xl font-bold text-red-800"><?php echo $inventory_data['out_of_stock']; ?></p>
                </div>
            </div>
        </div>
        </div>

        <!-- Staff Management Tab -->
        <div id="staff-tab" class="tab-content">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Staff Management</h1>
                <button onclick="openAddStaffModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Add Staff
                </button>
            </div>

            <!-- Staff Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-medium text-gray-800">Staff List</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jawatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Password</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            if ($staff_result->num_rows > 0) {
                                while ($row = $staff_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["StaffID"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["Staff_Name"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["Jawatan"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $row["Password"] . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium'>";
                                    echo "<button onclick='openEditStaffModal(\"" . $row["StaffID"] . "\", \"" . $row["Staff_Name"] . "\", \"" . $row["Jawatan"] . "\", \"" . $row["Password"] . "\")' class='text-blue-600 hover:text-blue-900 mr-3'>Edit</button>";
                                    echo "<a href='admin.php?delete_staff=" . $row["StaffID"] . "' class='text-red-600 hover:text-red-900' onclick='return confirm(\"Are you sure you want to delete this staff?\");'>Delete</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center'>No staff found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-800">Add New Staff</h3>
                <button onclick="closeAddStaffModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="admin.php">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff ID</label>
                        <input type="text" name="staff_id" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff Name</label>
                        <input type="text" name="staff_name" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jawatan</label>
                        <input type="text" name="jawatan" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="text" name="password" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAddStaffModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="add_staff" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Staff</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div id="editStaffModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-800">Edit Staff</h3>
                <button onclick="closeEditStaffModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="admin.php">
                <input type="hidden" name="staff_id" id="edit_staff_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff Name</label>
                        <input type="text" name="staff_name" id="edit_staff_name" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jawatan</label>
                        <input type="text" name="jawatan" id="edit_jawatan" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="text" name="password" id="edit_password" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditStaffModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" name="edit_staff" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Staff</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-12 pb-6">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div>
                    <h3 class="text-xl font-bold mb-4">About Book Heaven</h3>
                    <p class="text-gray-400 mb-4">We're passionate about books and committed to bringing you the best selection of titles from around the world.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="newRelease.php" class="text-gray-400 hover:text-white">New Releases</a></li>
                        <li><a href="bestseller.php" class="text-gray-400 hover:text-white">Bestsellers</a></li>
                        <li><a href="promotion.php" class="text-gray-400 hover:text-white">Promotions</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="order_user.php" class="text-gray-400 hover:text-white">Track Order</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i> 123 Book Street, Kuala Lumpur, Malaysia
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt mr-2"></i> +603-1234 5678
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i> hello@bookheaven.com
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-clock mr-2"></i> Mon-Fri: 9am-6pm
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 mb-4 md:mb-0">© 2025 Book Heaven. All rights reserved.</p>
                <div class="flex space-x-6">
                    <a class="text-gray-400 hover:text-white">Privacy Policy</a>
                    <a class="text-gray-400 hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all tabs
                    tabLinks.forEach(tab => tab.querySelector('a').classList.remove('text-blue-600', 'border-b-2', 'border-blue-600'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked tab
                    this.querySelector('a').classList.add('text-blue-600', 'border-b-2', 'border-blue-600');

                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });

            // Initialize charts
            initCharts();
        });

        // Initialize charts
        function initCharts() {
            // Sales Chart - Using PHP data
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($sales_labels); ?>,
                    datasets: [{
                        label: 'Daily Sales (RM)',
                        data: <?php echo json_encode($sales_data); ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Category Chart - Using PHP data from database
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($category_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($category_data); ?>,
                        backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#6b7280']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        // Export report function
        function exportReport(format) {
            alert(`Exporting report as ${format.toUpperCase()}...`);
        }

        // Share report function
        function shareReport() {
            alert('Sharing report with team members...');
        }

        // Toggle admin dropdown menu on click
        function toggleAdminDropdown() {
            const menu = document.getElementById('adminDropdownMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }
        // Close dropdown if clicking outside
        document.addEventListener('click', function(event) {
            const btn = document.getElementById('adminDropdownBtn');
            const menu = document.getElementById('adminDropdownMenu');
            if (!btn.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = 'none';
            }
        });

        // Update date range quickly
        function updateDateRange(range) {
            const url = new URL(window.location.href);
            url.searchParams.set('date_range', range);
            window.location.href = url.toString();
        }

        // Update category quickly
        function updateCategory(category) {
            const url = new URL(window.location.href);
            url.searchParams.set('category', category);
            window.location.href = url.toString();
        }

        // Staff Management Modal Functions
        function openAddStaffModal() {
            document.getElementById('addStaffModal').style.display = 'flex';
        }

        function closeAddStaffModal() {
            document.getElementById('addStaffModal').style.display = 'none';
        }

        function openEditStaffModal(staffId, staffName, jawatan, password) {
            document.getElementById('edit_staff_id').value = staffId;
            document.getElementById('edit_staff_name').value = staffName;
            document.getElementById('edit_jawatan').value = jawatan;
            document.getElementById('edit_password').value = password;
            document.getElementById('editStaffModal').style.display = 'flex';
        }

        function closeEditStaffModal() {
            document.getElementById('editStaffModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addStaffModal');
            const editModal = document.getElementById('editStaffModal');

            if (event.target == addModal) {
                closeAddStaffModal();
            }
            if (event.target == editModal) {
                closeEditStaffModal();
            }
        }
    </script>
</body>
