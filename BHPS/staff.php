<?php
include 'db_connect.php';
session_start();

// Check if staff is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
}

// Fetch staff details from session
$staff_id = $_SESSION['userID'];
$staff_name = $_SESSION['name'];
$staff_jawatan = $_SESSION['jawatan'];

// Define categories and formats
$categories = ["Fiction",  "Children", "Academic", "Business", "Food & Drink", "Romance"];
$formats = ["Physical", "E-book"];

// Fetch dashboard statistics
$delivery_orders = $conn->query("SELECT COUNT(*) as count FROM `order` WHERE Status = 'Delivery'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) as count FROM `order` WHERE Status = 'Completed'")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM book WHERE Quantity < 10")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(DISTINCT User_ID) as count FROM `order`")->fetch_assoc()['count'];

// Fetch recent orders
$orders_query = $conn->query("
    SELECT o.*, b.Name as book_name 
    FROM `order` o 
    LEFT JOIN book b ON o.BookID = b.BookID 
    ORDER BY o.order_date DESC 
    LIMIT 4
");

// Fetch recent books
$books_query = $conn->query("SELECT * FROM book ORDER BY BookID DESC LIMIT 4");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_book'])) {
        $book_id = trim($_POST['bookID']);
        $name = $_POST['bookTitle'];
        $author = $_POST['bookAuthor'];
        $category = $_POST['bookCategory'];
        $format = $_POST['bookFormat'];
        $price = $_POST['bookPrice'];
        $quantity = $_POST['bookStock'];
        $description = $_POST['bookDescription'];

        // Check if Book ID already exists
        $check_query = $conn->prepare("SELECT * FROM book WHERE BookID = ?");
        $check_query->bind_param("s", $book_id);
        $check_query->execute();
        $result = $check_query->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Book ID '$book_id' already exists. Please use a different Book ID.";
            header("Location: staff.php");
            exit();
        }

        // Handle image upload
        $imagePath = 'default-book.jpg'; // default image
        if (isset($_FILES['bookCover']) && $_FILES['bookCover']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = time() . '_' . basename($_FILES['bookCover']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['bookCover']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            }
        }

        $stmt = $conn->prepare("INSERT INTO book (BookID, Name, Author, Category, Format, Price, Quantity, Description, ImagePath) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdiss", $book_id, $name, $author, $category, $format, $price, $quantity, $description, $imagePath);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Book added successfully!";
        } else {
            $_SESSION['error'] = "Error adding book: " . $conn->error;
        }
        header("Location: staff.php");
        exit();
    }

    if (isset($_POST['update_book'])) {
        $old_book_id = $_POST['old_book_id']; // The original Book ID
        $new_book_id = trim($_POST['editBookID']); // The new Book ID (can be same or different)
        $name = $_POST['editBookTitle'];
        $author = $_POST['editBookAuthor'];
        $category = $_POST['editBookCategory'];
        $format = $_POST['editBookFormat'];
        $price = $_POST['editBookPrice'];
        $quantity = $_POST['editBookStock'];
        $description = $_POST['editBookDescription'];

        // Check if new Book ID already exists (if it's different from old one)
        if ($new_book_id != $old_book_id) {
            $check_query = $conn->prepare("SELECT * FROM book WHERE BookID = ?");
            $check_query->bind_param("s", $new_book_id);
            $check_query->execute();
            $result = $check_query->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Book ID '$new_book_id' already exists. Please use a different Book ID.";
                header("Location: staff.php");
                exit();
            }
        }

        // Handle image upload for update
        $imagePath = $_POST['currentImagePath'];
        if (isset($_FILES['editBookCover']) && $_FILES['editBookCover']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = time() . '_' . basename($_FILES['editBookCover']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['editBookCover']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
                // Delete old image if it's not the default
                if ($_POST['currentImagePath'] != 'default-book.jpg' && file_exists($_POST['currentImagePath'])) {
                    unlink($_POST['currentImagePath']);
                }
            }
        }

        //update book details
        $stmt = $conn->prepare("UPDATE book SET BookID = ?, Name = ?, Author = ?, Category = ?, Format = ?, Price = ?, Quantity = ?, Description = ?, ImagePath = ? WHERE BookID = ?");
        $stmt->bind_param("sssssdisss", $new_book_id, $name, $author, $category, $format, $price, $quantity, $description, $imagePath, $old_book_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Book updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating book: " . $conn->error;
        }
        header("Location: staff.php");
        exit();
    }

    if (isset($_POST['delete_book'])) {
        $book_id = $_POST['book_id'];

        // Get image path before deleting
        $result = $conn->query("SELECT ImagePath FROM book WHERE BookID = '$book_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $imagePath = $row['ImagePath'];
            // Delete image file if it's not the default
            if ($imagePath != 'default-book.jpg' && file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $stmt = $conn->prepare("DELETE FROM book WHERE BookID = ?");
        $stmt->bind_param("s", $book_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Book deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting book: " . $conn->error;
        }
        header("Location: staff.php");
        exit();
    }

    if (isset($_POST['update_order_status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE `order` SET Status = ? WHERE OrderID = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        header("Location: staff.php");
        exit();
    }

    if (isset($_POST['set_promotion'])) {
        $book_id = $_POST['book_id'];
        $promotion_price = $_POST['promotion_price'];

        // Validate promotion price is less than original price
        $book_query = $conn->query("SELECT Price FROM book WHERE BookID = '$book_id'");
        if ($book_query->num_rows > 0) {
            $book = $book_query->fetch_assoc();
            if ($promotion_price >= $book['Price']) {
                $_SESSION['error'] = "Promotion price must be less than original price.";
                header("Location: staff.php");
                exit();
            }

            $stmt = $conn->prepare("UPDATE book SET is_promotion = 1, promotion_price = ? WHERE BookID = ?");
            $stmt->bind_param("ds", $promotion_price, $book_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Promotion set successfully!";
            } else {
                $_SESSION['error'] = "Error setting promotion: " . $conn->error;
            }
        }
        header("Location: staff.php");
        exit();
    }

    if (isset($_POST['remove_promotion'])) {
        $book_id = $_POST['book_id'];

        $stmt = $conn->prepare("UPDATE book SET is_promotion = 0, promotion_price = NULL WHERE BookID = ?");
        $stmt->bind_param("s", $book_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Promotion removed successfully!";
        } else {
            $_SESSION['error'] = "Error removing promotion: " . $conn->error;
        }
        header("Location: staff.php");
        exit();
    }
}

// Display success/error messages
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .book-card:hover .book-actions {
            opacity: 1;
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

        .book-cover-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <!-- Top Announcement Bar -->
    <div class="bg-blue-800 text-white text-center py-2 px-4 text-sm">
        Staff Portal | Book Heaven Purchasing System
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <!-- Top Header -->
            <div class="flex items-center justify-between py-4 border-b">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="staff.php">
                        <span class="text-2xl font-bold text-blue-800">Book Heaven</span>
                        <span class="text-xs text-gray-600 block">Staff Portal</span>
                    </a>
                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button id="staffDropdownBtn" onclick="toggleStaffDropdown()" class="flex items-center text-gray-700 hover:text-blue-600 focus:outline-none">
                            <i class="fas fa-user-circle text-xl mr-1"></i>
                            <span><?php echo htmlspecialchars($staff_name); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200"></i>
                        </button>
                        <div id="staffDropdownMenu" class="absolute right-0 bg-white shadow-lg rounded mt-2 py-2 w-48 z-50" style="display:none;">
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
                    <li><a href="staff.php" class="text-blue-800 font-medium border-b-2 border-blue-600 pb-1">Dashboard</a></li>
                    <li><a href="orders.php" class="text-gray-800 hover:text-blue-600 font-medium">Orders</a></li>
                    <li><a href="books.php" class="text-gray-800 hover:text-blue-600 font-medium">Books</a></li>
                    <li><a href="promotion_management.php" class="text-gray-800 hover:text-blue-600 font-medium">Promotions</a></li>
                    <li><a href="reports.php" class="text-gray-800 hover:text-blue-600 font-medium">Reports</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <script>
        // Toggle staff dropdown menu on click
        function toggleStaffDropdown() {
            const menu = document.getElementById('staffDropdownMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }
        // Close dropdown if clicking outside
        document.addEventListener('click', function(event) {
            const btn = document.getElementById('staffDropdownBtn');
            const menu = document.getElementById('staffDropdownMenu');
            if (!btn.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Staff Dashboard</h1>
            <div class="flex space-x-2">
                <div class="relative">
                    <input type="text" placeholder="Search orders..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700" onclick="openAddBookModal()">
                    <i class="fas fa-plus mr-2"></i>New Book
                </button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-600 text-sm">Delivery Orders</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $delivery_orders; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-clock text-blue-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Need attention</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-600 text-sm">Completed Orders</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $completed_orders; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Successful deliveries</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-600 text-sm">Low Stock Items</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $low_stock; ?></h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-purple-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Need restocking</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-600 text-sm">Total Customers</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_customers; ?></h3>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-users text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Registered customers</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($order = $orders_query->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#<?php echo $order['OrderID']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Customer #<?php echo $order['User_ID']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $order['Quantity']; ?> items</div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['book_name'] ?: 'No book'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($order['order_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-pill <?php echo getStatusClass($order['Status']); ?> border-0 cursor-pointer">
                                            <option value="Processing" <?php echo $order['Status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Delivery" <?php echo $order['Status'] == 'Delivery' ? 'selected' : ''; ?>>Delivery</option>
                                            <option value="Completed" <?php echo $order['Status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo $order['Status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_order_status">
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="openOrderDetail(<?php echo $order['OrderID']; ?>)">View</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing recent orders
                    </div>
                    <a href="orders.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All Orders</a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Manage Books</h3>
                <div class="space-y-3">
                    <a href="books.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                        <div class="bg-blue-100 p-2 rounded-full mr-3">
                            <i class="fas fa-list text-blue-600"></i>
                        </div>
                        <span class="text-gray-700">View/Edit Books</span>
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Order Management</h3>
                <div class="space-y-3">
                    <a href="orders.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                        <div class="bg-yellow-100 p-2 rounded-full mr-3">
                            <i class="fas fa-clipboard-list text-yellow-600"></i>
                        </div>
                        <span class="text-gray-700">View/Edit orders</span>
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Reports</h3>
                <div class="space-y-3">
                    <a href="download_report.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50">
                        <div class="bg-pink-100 p-2 rounded-full mr-3">
                            <i class="fas fa-chart-bar text-pink-600"></i>
                        </div>
                        <span class="text-gray-700">Sales Reports</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Promotion Management -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Promotion Management</h2>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Set Book Promotion</h3>
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-gray-700 mb-2" for="promotionBook">Select Book</label>
                            <select id="promotionBook" name="book_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Choose a book</option>
                                <?php
                                $all_books = $conn->query("SELECT BookID, Name, Author, Price FROM book ORDER BY Name");
                                while ($book = $all_books->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $book['BookID']; ?>" data-price="<?php echo $book['Price']; ?>">
                                        <?php echo htmlspecialchars($book['Name']); ?> by <?php echo htmlspecialchars($book['Author']); ?> (RM<?php echo number_format($book['Price'], 2); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2" for="promotionPrice">Promotion Price (RM)</label>
                            <input type="number" id="promotionPrice" name="promotion_price" step="0.01" min="0.01" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <button type="submit" name="set_promotion" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                Set Promotion
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Current Promotions -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Current Promotions</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promotion Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $promotions = $conn->query("SELECT * FROM book WHERE is_promotion = 1 ORDER BY Name");
                                while ($promo = $promotions->fetch_assoc()):
                                    $discount = round((($promo['Price'] - $promo['promotion_price']) / $promo['Price']) * 100);
                                ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($promo['Name']); ?></div>
                                            <div class="text-sm text-gray-500">by <?php echo htmlspecialchars($promo['Author']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">RM<?php echo number_format($promo['Price'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm font-bold text-red-600">RM<?php echo number_format($promo['promotion_price'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded"><?php echo $discount; ?>% OFF</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="book_id" value="<?php echo $promo['BookID']; ?>">
                                                <button type="submit" name="remove_promotion" class="text-red-600 hover:text-red-900" onclick="return confirm('Remove this book from promotion?')">
                                                    Remove Promotion
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Books Added -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Recently Added Books</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($book = $books_query->fetch_assoc()): ?>
                    <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                        <div class="relative">
                            <img src="<?php echo $book['ImagePath'] ?: 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80'; ?>"
                                alt="Book Cover" class="w-full h-48 object-cover">
                            <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                                <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100" onclick="editBook('<?php echo $book['BookID']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700" onclick="deleteBook('<?php echo $book['BookID']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs bg-gray-200 text-gray-800 px-2 py-1 rounded">ID: <?php echo $book['BookID']; ?></span>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded"><?php echo htmlspecialchars($book['Format']); ?></span>
                            </div>
                            <h3 class="font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($book['Name']); ?></h3>
                            <p class="text-sm text-gray-600 mb-1">by <?php echo htmlspecialchars($book['Author']); ?></p>
                            <div class="mb-2">
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"><?php echo htmlspecialchars($book['Category']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900">RM<?php echo number_format($book['Price'], 2); ?></span>
                                <span class="text-xs <?php echo getStockClass($book['Quantity']); ?> px-2 py-1 rounded">
                                    <?php echo getStockText($book['Quantity']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <!-- Order Detail Modal -->
    <div id="orderDetailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header flex justify-between items-center mb-4 pb-2 border-b">
                <h2 class="text-xl font-bold text-gray-800">Order Details</h2>
                <span class="close text-2xl cursor-pointer" onclick="closeModal('orderDetailModal')">&times;</span>
            </div>
            <div id="orderDetailContent">
                <!-- Content will be filled by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div id="addBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header flex justify-between items-center mb-4 pb-2 border-b">
                <h2 class="text-xl font-bold text-gray-800">Add New Book</h2>
                <span class="close text-2xl cursor-pointer" onclick="closeModal('addBookModal')">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="bookID">Book ID <span class="text-red-500">*</span></label>
                    <input type="text" id="bookID" name="bookID" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required
                        placeholder="Enter unique Book ID (e.g., B001, ISBN123)">
                    <p class="text-xs text-gray-500 mt-1">Must be unique. This will be used as the book identifier.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="bookTitle">Book Title <span class="text-red-500">*</span></label>
                    <input type="text" id="bookTitle" name="bookTitle" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="bookAuthor">Author <span class="text-red-500">*</span></label>
                    <input type="text" id="bookAuthor" name="bookAuthor" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2" for="bookCategory">Category <span class="text-red-500">*</span></label>
                        <select id="bookCategory" name="bookCategory" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" for="bookFormat">Format <span class="text-red-500">*</span></label>
                        <select id="bookFormat" name="bookFormat" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a format</option>
                            <?php foreach ($formats as $format): ?>
                                <option value="<?php echo $format; ?>"><?php echo $format; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2" for="bookPrice">Price (RM) <span class="text-red-500">*</span></label>
                        <input type="number" id="bookPrice" name="bookPrice" step="0.01" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" for="bookStock">Stock Quantity <span class="text-red-500">*</span></label>
                        <input type="number" id="bookStock" name="bookStock" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="bookCover">Book Cover</label>
                    <input type="file" id="bookCover" name="bookCover" accept="image/*" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="bookCoverPreview" class="mt-2 hidden">
                        <img src="" alt="Book Cover Preview" class="book-cover-preview">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="bookDescription">Description</label>
                    <textarea id="bookDescription" name="bookDescription" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400" onclick="closeModal('addBookModal')">Cancel</button>
                    <button type="submit" name="add_book" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div id="editBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header flex justify-between items-center mb-4 pb-2 border-b">
                <h2 class="text-xl font-bold text-gray-800">Edit Book</h2>
                <span class="close text-2xl cursor-pointer" onclick="closeModal('editBookModal')">&times;</span>
            </div>
            <form method="POST" id="editBookForm" enctype="multipart/form-data">
                <input type="hidden" name="old_book_id" id="oldBookId">
                <input type="hidden" name="currentImagePath" id="currentImagePath">
                <input type="hidden" name="update_book">

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookID">Book ID <span class="text-red-500">*</span></label>
                    <input type="text" id="editBookID" name="editBookID" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">You can change the Book ID if needed.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookTitle">Book Title <span class="text-red-500">*</span></label>
                    <input type="text" id="editBookTitle" name="editBookTitle" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookAuthor">Author <span class="text-red-500">*</span></label>
                    <input type="text" id="editBookAuthor" name="editBookAuthor" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookCategory">Category <span class="text-red-500">*</span></label>
                        <select id="editBookCategory" name="editBookCategory" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookFormat">Format <span class="text-red-500">*</span></label>
                        <select id="editBookFormat" name="editBookFormat" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a format</option>
                            <?php foreach ($formats as $format): ?>
                                <option value="<?php echo $format; ?>"><?php echo $format; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookPrice">Price (RM) <span class="text-red-500">*</span></label>
                        <input type="number" id="editBookPrice" name="editBookPrice" step="0.01" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookStock">Stock Quantity <span class="text-red-500">*</span></label>
                        <input type="number" id="editBookStock" name="editBookStock" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookCover">Book Cover</label>
                    <div id="currentBookCover" class="mb-2">
                        <img id="currentBookCoverImg" src="" alt="Current Book Cover" class="book-cover-preview">
                    </div>
                    <input type="file" id="editBookCover" name="editBookCover" accept="image/*" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="editBookCoverPreview" class="mt-2 hidden">
                        <img src="" alt="New Book Cover Preview" class="book-cover-preview">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookDescription">Description</label>
                    <textarea id="editBookDescription" name="editBookDescription" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400" onclick="closeModal('editBookModal')">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Book Form -->
    <form method="POST" id="deleteBookForm" style="display: none;">
        <input type="hidden" name="book_id" id="deleteBookId">
        <input type="hidden" name="delete_book">
    </form>

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
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="staff.php" class="text-gray-400 hover:text-white">Dashboard</a></li>
                        <li><a href="orders.php" class="text-gray-400 hover:text-white">Orders</a></li>
                        <li><a href="books.php" class="text-gray-400 hover:text-white">Books</a></li>
                        <li><a href="reports.php" class="text-gray-400 hover:text-white">Reports</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Track Order</a></li>
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
                    <a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a>
                    <a href="#" class="text-gray-400 hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Open order detail modal
        function openOrderDetail(orderId) {
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(order => {
                    if (order.error) {
                        alert(order.error);
                        return;
                    }

                    // Format the order date
                    const orderDate = new Date(order.order_date);
                    const formattedDate = orderDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    const detailHtml = `
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Order Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Order ID</p>
                                        <p class="font-medium">#${order.OrderID}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Customer ID</p>
                                        <p class="font-medium">#${order.User_ID}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Status</p>
                                        <p class="font-medium">
                                            <span class="status-pill ${getStatusClass(order.Status)}">${order.Status}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Order Date</p>
                                        <p class="font-medium">${formattedDate}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Items</p>
                                        <p class="font-medium">${order.Quantity}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total Amount</p>
                                        <p class="font-medium">RM${parseFloat(order.total_amount).toFixed(2)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Book Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-start space-x-4">
                                    <img src="${order.ImagePath || 'default-book.jpg'}" 
                                         alt="${order.book_name}" 
                                         class="w-20 h-24 object-cover rounded">
                                    <div>
                                        <h4 class="font-medium text-gray-800">${order.book_name || 'Book information not available'}</h4>
                                        <p class="text-sm text-gray-600">by ${order.Author || 'Unknown author'}</p>
                                        <div class="mt-2 flex space-x-2">
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${order.Category || 'No category'}</span>
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">${order.Format || 'No format'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Shipping Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Shipping Address</p>
                                <p class="font-medium">${order.address || 'No shipping address provided'}</p>
                            </div>
                        </div>
                    `;

                    document.getElementById('orderDetailContent').innerHTML = detailHtml;
                    document.getElementById('orderDetailModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    alert('Error loading order details. Please try again.');
                });
        }

        // Function for status classes
        function getStatusClass(status) {
            switch (status.toLowerCase()) {
                case 'processing':
                    return 'bg-blue-100 text-blue-800';
                case 'delivery':
                    return 'bg-blue-100 text-blue-800';
                case 'completed':
                    return 'bg-green-100 text-green-800';
                case 'cancelled':
                    return 'bg-red-100 text-red-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        // Open add book modal
        function openAddBookModal() {
            document.getElementById('addBookModal').style.display = 'flex';
        }

        // Image preview for add book form
        document.getElementById('bookCover').addEventListener('change', function(e) {
            const preview = document.getElementById('bookCoverPreview');
            const img = preview.querySelector('img');

            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                }

                reader.readAsDataURL(this.files[0]);
            }
        });

        // Image preview for edit book form
        document.getElementById('editBookCover').addEventListener('change', function(e) {
            const preview = document.getElementById('editBookCoverPreview');
            const img = preview.querySelector('img');

            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                }

                reader.readAsDataURL(this.files[0]);
            }
        });

        // Edit book function - UPDATED TO SUPPORT EDITABLE BOOK ID
        function editBook(bookId) {
            window.location.href = 'books.php?edit=' + bookId;
        }

        // Delete book
        function deleteBook(bookId) {
            if (confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                document.getElementById('deleteBookId').value = bookId;
                document.getElementById('deleteBookForm').submit();
            }
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        };

        // Promotion form functionality
        document.getElementById('promotionBook').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const originalPrice = selectedOption.getAttribute('data-price');
            const promotionPriceInput = document.getElementById('promotionPrice');

            if (originalPrice) {
                // Set promotion price to 10% less than original as default
                const suggestedPrice = (parseFloat(originalPrice) * 0.9).toFixed(2);
                promotionPriceInput.value = suggestedPrice;
                promotionPriceInput.max = originalPrice - 0.01;
            }
        });
    </script>
</body>

</html>

<?php
// Helper functions
function getStatusClass($status)
{
    switch (strtolower($status)) {
        case 'processing':
            return 'bg-blue-100 text-blue-800';
        case 'delivery':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStockClass($quantity)
{
    if ($quantity == 0) return 'bg-red-100 text-red-800';
    if ($quantity < 10) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
}

function getStockText($quantity)
{
    if ($quantity == 0) return 'Out of Stock';
    if ($quantity < 10) return 'Low Stock: ' . $quantity;
    return 'In Stock: ' . $quantity;
}

$conn->close();

?>

