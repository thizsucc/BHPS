<?php
session_start();
include 'db_connect.php';

// Only staff can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: login.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE `order` SET Status = ? WHERE OrderID = ?");
    $stmt->bind_param('si', $status, $order_id);
    $stmt->execute();
    $stmt->close();
    header('Location: orders.php');
    exit();
}

// Fetch all orders with complete book details
$orders_query = $conn->query("
    SELECT o.*, b.Name as book_name, b.Author, b.Category, b.Format, b.ImagePath, b.Price as book_price
    FROM `order` o 
    LEFT JOIN book b ON o.BookID = b.BookID 
    ORDER BY o.order_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders - Book Heaven Staff</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
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

        .status-processing {
            background: #fff3cd;
            color: #856404;
        }

        .status-delivery {
            background: #d1edff;
            color: #004085;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
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
            <div class="flex items-center justify-between py-4 border-b">
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
                            <span><?php echo htmlspecialchars(isset($_SESSION['name']) ? $_SESSION['name'] : 'Staff'); ?></span>
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
            <nav class="hidden md:block">
                <ul class="flex items-center space-x-6 py-3">
                    <li><a href="staff.php" class="text-gray-800 hover:text-blue-600 font-medium">Dashboard</a></li>
                    <li><a href="orders.php" class="text-blue-600 font-medium border-b-2 border-blue-600 pb-1">Orders</a></li>
                    <li><a href="books.php" class="text-gray-800 hover:text-blue-600 font-medium">Books</a></li>
                    <li><a href="promotion_management.php" class="text-gray-800 hover:text-blue-600 font-medium">Promotion</a></li>
                    <li><a href="reports.php" class="text-gray-800 hover:text-blue-600 font-medium">Reports</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Order List</h1>
            <div class="flex space-x-2">
                <a href="books.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-book mr-2"></i>Manage Books
                </a>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">All Orders</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs">
                        <tr>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Book</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Format</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Shipping Address</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($order = $orders_query->fetch_assoc()): ?>
                            <tr>
                                <td class="px-2 py-2 whitespace-nowrap font-medium text-gray-900">#<?php echo $order['OrderID']; ?></td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900">#<?php echo $order['User_ID']; ?></td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <img src="<?php echo $order['ImagePath'] ?: 'default-book.jpg'; ?>" alt="Book Cover" class="h-8 w-8 object-cover rounded">
                                        <span class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($order['book_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($order['Author']); ?></td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($order['Category']); ?></td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($order['Format']); ?></td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900"><?php echo $order['Quantity']; ?></td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900">RM <?php echo number_format($order['Total_Amount'], 2); ?></td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900"><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-pill <?php echo getStatusClass($order['Status']); ?> border-0 cursor-pointer text-xs">
                                            <option value="Processing" <?php echo $order['Status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Delivery" <?php echo $order['Status'] == 'Delivery' ? 'selected' : ''; ?>>Delivery</option>
                                            <option value="Completed" <?php echo $order['Status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo $order['Status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 max-w-xs truncate"><?php echo htmlspecialchars($order['address']); ?></td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs font-medium">
                                    <button class="bg-blue-100 text-blue-800 px-2 py-1 rounded hover:bg-blue-200" onclick="openOrderDetail(<?php echo $order['OrderID']; ?>)">View</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($orders_query->num_rows == 0): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-box-open text-4xl mb-4"></i>
                    <p>No orders found.</p>
                </div>
            <?php endif; ?>
        </div>

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
            // Open order detail modal (sync logic with staff.php)
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
                                        <p class="text-sm text-gray-600">User ID</p>
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
                                        <p class="text-sm text-gray-600">Quantity</p>
                                        <p class="font-medium">${order.Quantity}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total Amount</p>
                                        <p class="font-medium">RM ${parseFloat(order.Total_Amount || 0).toFixed(2)}</p>
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
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-800">${order.book_name || 'Book information not available'}</h4>
                                        <p class="text-sm text-gray-600">by ${order.Author || 'Unknown author'}</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${order.Category || 'No category'}</span>
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">${order.Format || 'No format'}</span>
                                            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">Book ID: ${order.BookID || 'N/A'}</span>
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
                        return 'status-processing';
                    case 'delivery':
                        return 'status-delivery';
                    case 'completed':
                        return 'status-completed';
                    case 'cancelled':
                        return 'status-cancelled';
                    default:
                        return 'bg-gray-100 text-gray-800';
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
        </script>
</body>

</html>

<?php
// Helper functions
function getStatusClass($status)
{
    switch (strtolower($status)) {
        case 'processing':
            return 'status-processing';
        case 'delivery':
            return 'status-delivery';
        case 'completed':
            return 'status-completed';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>