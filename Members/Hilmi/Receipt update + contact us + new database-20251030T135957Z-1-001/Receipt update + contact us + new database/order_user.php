<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}
include 'db_connect.php';

$user_id = $_SESSION['userID'] ?? null;

// Handle cancel order POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_order_id = intval($_POST['cancel_order_id']);
    $stmt = $conn->prepare("SELECT Status FROM `order` WHERE OrderID = ? AND User_ID = ?");
    $stmt->bind_param("is", $cancel_order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!in_array(strtolower($row['Status']), ['completed', 'cancelled'])) {
            $update = $conn->prepare("UPDATE `order` SET Status = 'Cancelled' WHERE OrderID = ?");
            $update->bind_param("i", $cancel_order_id);
            $update->execute();
            $update->close();
        }
    }
    $stmt->close();
    header("Location: order_user.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT 
        o.OrderID,
        o.order_date,
        o.address,
        o.Quantity,
        o.Status,
        o.Total_Amount,
        o.User_ID,
        o.payment_method,
        o.card_number,
        o.bank_name,
        b.BookID,
        b.Name AS book_name,
        b.ImagePath,
        b.Author
    FROM `order` o
    LEFT JOIN book b ON o.BookID = b.BookID
    WHERE o.User_ID = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ✅ Group books by OrderID
$groupedOrders = [];
foreach ($rows as $row) {
    $oid = $row['OrderID'];
    if (!isset($groupedOrders[$oid])) {
        $groupedOrders[$oid] = [
            'OrderID' => $oid,
            'order_date' => $row['order_date'],
            'address' => $row['address'],
            'Quantity' => $row['Quantity'],
            'Status' => $row['Status'],
            'Total_Amount' => $row['Total_Amount'],
            'User_ID' => $row['User_ID'],
            'payment_method' => $row['payment_method'],
            'card_number' => $row['card_number'],
            'bank_name' => $row['bank_name'],
            'books' => []
        ];
    }

    // add book
    $groupedOrders[$oid]['books'][] = [
        'BookID' => $row['BookID'],
        'book_name' => $row['book_name'],
        'ImagePath' => $row['ImagePath'],
        'Author' => $row['Author']
    ];
}

function maskCardNumber($cardNumber) {
    if (!$cardNumber || strlen($cardNumber) < 8) return "**** **** **** ****";
    return substr($cardNumber, 0, 4) . str_repeat('*', strlen($cardNumber) - 8) . substr($cardNumber, -4);
}

$orders = array_values($groupedOrders);
$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $logged_in ? $_SESSION['name'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Book Heaven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: white;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-menu a:hover {
            background-color: #f1f1f1;
        }

        .rotate-180 {
            transform: rotate(180deg);
        }

        .search-box:focus-within {
            border-color: #3b82f6;
        }

        .order-status {
            position: relative;
            display: inline-block;
        }

        .order-status::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }

        .user-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            min-width: 160px;
            border-radius: 4px;
        }

        .user-menu.show {
            display: block;
        }

        .action-panel {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            width: 320px;
            border-radius: 4px;
            max-height: 400px;
            overflow-y: auto;
        }

        .action-panel.show {
            display: block;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .cart-item img {
            width: 50px;
            height: 70px;
            object-fit: cover;
            margin-right: 10px;
        }

        .cart-item-details {
            flex-grow: 1;
        }

        .empty-state {
            padding: 20px;
            text-align: center;
            color: #6b7280;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-processing {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d1edff;
            color: #004085;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 24px;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Top Announcement Bar -->
    <div class="bg-blue-800 text-white text-center py-2 px-4 text-sm">
        🎉 Special Promotion! Limited time offers on selected books 🎉 | Free shipping on orders over RM200
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <!-- Top Header -->
            <div class="flex items-center justify-between py-4 border-b">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php">
                        <img src="img/logo.png" alt="Book Heaven" width="200" height="150">
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="hidden md:flex flex-1 mx-8">
                    <div class="relative w-full max-w-xl">
                        <div class="search-box flex items-center border rounded-full overflow-hidden">
                            <input type="text" placeholder="Search books, authors, ISBN..."
                                class="py-2 px-6 w-full focus:outline-none" style="min-width: 300px;">
                            <button class="bg-blue-600 text-white px-6 py-2 hover:bg-blue-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <?php if ($logged_in): ?>
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="text-gray-700 hover:text-blue-600 flex items-center">
                                <i class="fas fa-user-circle text-xl mr-1"></i>
                                <span class="hidden md:inline"><?php echo htmlspecialchars($username); ?></span>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div id="userDropdown" class="user-menu absolute mt-2 right-0">
                                <a href="order_user.php" class="block px-4 py-2 hover:bg-gray-100">Orders</a>
                                <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>

                        <!-- Cart Icon with Subtotal -->
                        <div class="relative flex items-center space-x-2">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-700" id="cartSubtotalNav">RM0.00</div>
                                <div class="text-xs text-gray-500">Cart Total</div>
                            </div>
                            <button onclick="toggleCart()" class="text-gray-700 hover:text-blue-600 relative">
                                <i class="fas fa-shopping-cart text-xl"></i>
                                <span id="cartCount" class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                            </button>
                            <div id="cartPanel" class="action-panel absolute mt-2 right-0 top-full">
                                <div class="p-4 border-b">
                                    <h3 class="font-bold">Your Cart</h3>
                                </div>
                                <div id="cartItems">
                                    <div class="empty-state">
                                        <i class="fas fa-shopping-cart text-2xl mb-2 text-gray-300"></i>
                                        <p>Your cart is empty</p>
                                    </div>
                                </div>
                                <div class="p-4 border-t">
                                    <div class="flex justify-between mb-2">
                                        <span>Shipping fee:</span>
                                        <span id="cartShippingFee">RM5.00</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="cartSubtotal">RM0.00</span>
                                    </div>
                                    <a href="payment.php" class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700">Checkout</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-blue-600">
                            <i class="fas fa-user text-xl"></i>
                            <span class="hidden md:inline ml-1">Sign In</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Search -->
            <div class="md:hidden py-2">
                <div class="relative">
                    <div class="search-box flex items-center border rounded-full overflow-hidden">
                        <input type="text" placeholder="Search books..."
                            class="py-2 px-4 w-full focus:outline-none">
                        <button class="bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:block">
                <ul class="flex items-center justify-center space-x-6 py-3">
                    <li class="dropdown relative">
                        <button onclick="toggleDropdown()" class="text-gray-800 hover:text-blue-600 font-medium flex items-center transition-colors duration-200 focus:outline-none">
                            Categories <i id="dropdownIcon" class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200"></i>
                        </button>
                        <div id="categoriesDropdown" class="dropdown-menu absolute bg-white shadow-lg rounded mt-2 w-48 z-50">
                            <a href="fiction.php" class="block px-4 py-2 hover:bg-gray-100">Fiction</a>
                            <a href="academic.php" class="block px-4 py-2 hover:bg-gray-100">Academic</a>
                            <a href="children.php" class="block px-4 py-2 hover:bg-gray-100">Children</a>
                            <a href="business.php" class="block px-4 py-2 hover:bg-gray-100">Business</a>
                            <a href="foodNdrink.php" class="block px-4 py-2 hover:bg-gray-100">Food & Drink</a>
                            <a href="romance.php" class="block px-4 py-2 hover:bg-gray-100">Romance</a>
                        </div>
                    </li>
                    <li><a href="newRelease.php" class="text-gray-800 hover:text-blue-600 font-medium">New Releases</a></li>
                    <li><a href="bestseller.php" class="text-gray-800 hover:text-blue-600 font-medium">Bestsellers</a></li>
                    <li><a href="about.php" class="text-gray-800 hover:text-blue-600 font-medium">About Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="md:hidden bg-white border-t border-b py-2 px-4 flex justify-between items-center">
        <button class="text-gray-800">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div class="text-sm text-gray-600">
            <a href="#" class="font-medium">Browse Categories</a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">My Orders</h1>

            <?php if (empty($orders)): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden text-center py-16">
                    <div class="py-12">
                        <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-700 mb-4">No Orders Yet</h2>
                        <p class="text-gray-600 mb-6">You haven't placed any orders yet.</p>
                        <a href="index.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                            Start Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                        <!-- Order Header -->
                        <div class="bg-blue-800 text-white p-6">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                                <div>
                                    <h2 class="text-2xl font-bold">Order #<?php echo $order['OrderID']; ?></h2>
                                    <p class="text-blue-200 mt-1">Placed on <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
                                </div>
                                <div class="mt-4 md:mt-0">
                                    <?php
                                    $statusClass = '';
                                    switch (strtolower($order['Status'])) {
                                        case 'processing':
                                            $statusClass = 'bg-yellow-500';
                                            break;
                                        case 'completed':
                                            $statusClass = 'bg-green-600';
                                            break;
                                        case 'shipped':
                                            $statusClass = 'bg-blue-600';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-red-600';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-600';
                                    }
                                    ?>
                                    <span class="order-status <?php echo $statusClass; ?> text-white px-4 py-2 rounded-full text-sm font-medium">
                                        <?php echo $order['Status']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Information -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <!-- Shipping Address -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="font-bold text-lg mb-3 flex items-center">
                                        <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                                        Shipping Address
                                    </h3>
                                    <div class="space-y-1">
                                        <p><?php echo htmlspecialchars($order['address']); ?></p>
                                    </div>
                                </div>

                                <!-- Order Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="font-bold text-lg mb-3 flex items-center">
                                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                        Order Information
                                    </h3>
                                    <div class="space-y-1">
                                        <p class="font-medium">Total Amount: <span class="font-normal">RM <?php echo number_format($order['Total_Amount'], 2); ?></span></p>
                                        <p class="font-medium">Quantity: <span class="font-normal"><?php echo $order['Quantity']; ?> item(s)</span></p>
                                        <p class="font-medium">User ID: <span class="font-normal"><?php echo $order['User_ID']; ?></span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="mb-8">
                                <h3 class="font-bold text-lg mb-4">Order Items</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <?php foreach ($order['books'] as $book): ?>
                                        <div class="border rounded-lg bg-white shadow-sm p-4 flex flex-col items-center text-center">
                                            <img src="<?php echo htmlspecialchars($book['ImagePath']); ?>" 
                                                alt="<?php echo htmlspecialchars($book['book_name']); ?>" 
                                                class="w-32 h-48 object-contain mb-3">
                                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($book['book_name']); ?></h4>
                                            <?php if (!empty($book['Author'])): ?>
                                                <p class="text-gray-500 text-sm mb-1"><?php echo htmlspecialchars($book['Author']); ?></p>
                                            <?php endif; ?>
                                            <p class="text-xs text-gray-400">Book ID: <?php echo htmlspecialchars($book['BookID']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>


                            <!-- Order Summary -->
                            <div class="bg-gray-50 p-6 rounded-lg mb-8">
                                <h3 class="font-bold text-lg mb-4">Order Summary</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span>Subtotal (<?php echo $order['Quantity']; ?> item<?php echo $order['Quantity'] > 1 ? 's' : ''; ?>)</span>
                                        <span>RM<?php echo number_format($order['Total_Amount'], 2); ?></span>
                                    </div>
                                    <div class="flex justify-between font-bold text-lg pt-3 border-t">
                                        <span>Total</span>
                                        <span>RM<?php echo number_format($order['Total_Amount'], 2); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <a href="index.php" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Continue Shopping
                                </a>

                                <!-- View Receipt Button -->
                                <a href="receipt.php?order_id=<?php echo $order['OrderID']; ?>"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-receipt mr-2"></i>
                                    View Receipt
                                </a>

                                <?php if (strtolower($order['Status']) !== 'completed' && strtolower($order['Status']) !== 'cancelled'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="cancel_order_id" value="<?php echo $order['OrderID']; ?>">
                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700"
                                            onclick="return confirm('Are you sure you want to cancel this order?');">
                                            <i class="fas fa-times-circle mr-2"></i>
                                            Cancel Order
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

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
                    <a class="text-gray-400 hover:text-white">Privacy Policy</a>
                    <a class="text-gray-400 hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Header functionality
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('categoriesDropdown');
            const icon = document.getElementById('dropdownIcon');
            dropdown.classList.toggle('show');
            icon.classList.toggle('rotate-180');
        }

        function toggleCart() {
            const panel = document.getElementById('cartPanel');
            panel.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('button') && !event.target.closest('button')) {
                const dropdowns = document.getElementsByClassName("dropdown-menu");
                const userDropdowns = document.getElementsByClassName("user-menu");
                const actionPanels = document.getElementsByClassName("action-panel");

                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }

                for (let i = 0; i < userDropdowns.length; i++) {
                    const openDropdown = userDropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }

                for (let i = 0; i < actionPanels.length; i++) {
                    const openPanel = actionPanels[i];
                    if (openPanel.classList.contains('show')) {
                        openPanel.classList.remove('show');
                    }
                }

                // Remove rotate-180 from icons
                const icons = document.getElementsByClassName("fas fa-chevron-down");
                for (let i = 0; i < icons.length; i++) {
                    if (icons[i].classList.contains('rotate-180')) {
                        icons[i].classList.remove('rotate-180');
                    }
                }
            }
        }
    </script>
</body>

</html>