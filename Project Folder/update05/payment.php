<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}
include 'db_connect.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Function to generate unique OrderID for the entire order
function generateOrderID($conn)
{
    // Generate a random OrderID between 100000 and 999999
    $max_attempts = 10;
    $attempts = 0;

    while ($attempts < $max_attempts) {
        $new_id = mt_rand(100000, 999999);

        // Check if this ID already exists
        $check_stmt = $conn->prepare("SELECT OrderID FROM `order` WHERE OrderID = ?");
        $check_stmt->bind_param("i", $new_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $check_stmt->close();
            return $new_id;
        }

        $check_stmt->close();
        $attempts++;
    }

    // If all attempts fail, use timestamp-based ID as fallback
    return intval(date('YmdHis')) % 1000000;
}

// Handle payment POST
$payment_success = false;
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $phpCart = $_SESSION['cart'];

    error_log('DEBUG: Session data = ' . print_r($_SESSION, true));

    // Get user ID from session
    $user_id = $_SESSION['userID'] ?? $_SESSION['UserID'] ?? null;

    // Validate user ID
    if (!$user_id) {
        $error_message = 'User not logged in properly. Please login again.';
        error_log('ERROR: No user ID found in session. Session data: ' . print_r($_SESSION, true));
    } else {
        error_log('DEBUG: Using userID = ' . $user_id . ' (Type: ' . gettype($user_id) . ')');

        // Get address from POST
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
        $order_date = date('Y-m-d H:i:s');

        // Get payment details
        $card_number = '';
        $bank_name = '';

        if ($payment_method === 'card') {
            $card_number = isset($_POST['cardNumber']) ? trim($_POST['cardNumber']) : '';
        } elseif ($payment_method === 'bank') {
            $bank_name = isset($_POST['bank_name']) ? trim($_POST['bank_name']) : '';
        }

        // Validate required fields
        if (empty($address)) {
            $error_message = 'Shipping address is required.';
        } elseif (empty($payment_method)) {
            $error_message = 'Payment method is required.';
        } elseif ($payment_method === 'card' && empty($card_number)) {
            $error_message = 'Card number is required for card payment.';
        } elseif ($payment_method === 'bank' && empty($bank_name)) {
            $error_message = 'Bank selection is required for online banking.';
        } elseif (!empty($phpCart)) {
            $all_success = true;

            // Generate a SINGLE OrderID for the entire order (all items in cart)
            $order_id = generateOrderID($conn);
            $order_ids = [$order_id]; // Store the single order ID

            foreach ($phpCart as $index => $item) {
                // Use the SAME OrderID for all items in this order

                // Get book ID from various possible keys
                $book_id = null;
                $possible_keys = ['book_id', 'BookID', 'id', 'bookID', 'BookId', 'bookId', 'ID', 'bookId'];

                foreach ($possible_keys as $key) {
                    if (isset($item[$key]) && !empty($item[$key])) {
                        $book_id = $item[$key];
                        break;
                    }
                }

                // get books from database using title
                if (!$book_id) {
                    $title = $item['title'] ?? $item['Name'] ?? '';
                    if ($title) {
                        $clean_title = trim($title);
                        $stmt = $conn->prepare("SELECT BookID FROM book WHERE Name = ? LIMIT 1");
                        $stmt->bind_param("s", $clean_title);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $book_id = $row['BookID'];
                        }
                        $stmt->close();
                    }
                }

                // If still no BookID, create a fallback ID for testing
                if (!$book_id) {
                    $title = $item['title'] ?? 'Unknown Book';
                    $book_id = 'BOOK_' . substr(md5($title), 0, 8);
                }

                $qty = $item['quantity'] ?? $item['qty'] ?? 1;
                $price = $item['price'] ?? $item['Price'] ?? 0;
                $total_amount = (float)$price * (int)$qty;

                error_log("Inserting order item - OrderID: $order_id, UserID: $user_id, Address: $address, BookID: $book_id, Quantity: $qty, Total: $total_amount, Payment Method: $payment_method");

                error_log("DEBUG: User_ID type: " . gettype($user_id) . ", value: " . $user_id);
                error_log("DEBUG: BookID type: " . gettype($book_id) . ", value: " . $book_id);

                // Modified SQL to include payment_method, card_number, and bank_name
                $stmt = $conn->prepare("INSERT INTO `order` (OrderID, BookID, order_date, address, Quantity, User_ID, Status, Total_Amount, payment_method, card_number, bank_name) VALUES (?, ?, ?, ?, ?, ?, 'Processing', ?, ?, ?, ?)");

                error_log("Parameters - OrderID: $order_id (int), BookID: $book_id (string), Date: $order_date, Address: $address, Qty: $qty (int), User_ID: $user_id (string), Total: $total_amount (float), Payment Method: $payment_method, Card: $card_number, Bank: $bank_name");

                $stmt->bind_param("isssisdsss", $order_id, $book_id, $order_date, $address, $qty, $user_id, $total_amount, $payment_method, $card_number, $bank_name);

                if ($stmt->execute()) {
                    error_log("Successfully inserted order item with OrderID: $order_id for book: $book_id");
                } else {
                    $all_success = false;
                    $error_message = 'Database error: ' . $conn->error;
                    error_log("Database error: " . $conn->error);
                    error_log("SQL State: " . $stmt->sqlstate);
                    error_log("Error No: " . $stmt->errno);

                    // Additional debug info
                    error_log("DEBUG: User_ID being inserted: " . $user_id);
                    break;
                }
                $stmt->close();
            }

            if ($all_success) {
                $payment_success = true;
                $_SESSION['cart'] = [];
                $_SESSION['last_order_ids'] = $order_ids; // This will now contain only one ID

                // Store bank URL mapping for redirection (separate from database storage)
                $bank_urls = [
                    'Maybank2u' => 'https://www.maybank2u.com.my/home/m2u/common/login.do',
                    'CIMB Clicks' => 'https://www.cimbclicks.com.my/',
                    'Bank Islam' => 'https://www.bankislam.com.my/',
                    'Agrobank' => 'https://www.agrobank.com.my/'
                ];

                if ($payment_method === 'bank' && !empty($bank_name) && isset($bank_urls[$bank_name])) {
                    $_SESSION['selected_bank_url'] = $bank_urls[$bank_name];
                }
            }
        } else {
            $error_message = 'Your cart is empty.';
        }
    }
}

// Build PHP cart summary - use empty array if payment was successful
$phpCart = $payment_success ? [] : $_SESSION['cart'];
$subtotal = 0.0;
foreach ($phpCart as $item) {
    $price = $item['price'] ?? $item['Price'] ?? 0;
    $qty = $item['quantity'] ?? $item['qty'] ?? 1;
    $lineTotal = (float)$price * (int)$qty;
    $subtotal += $lineTotal;
}

// FIXED: Shipping fee should be 0.00 when cart is empty
$shipping = (count($phpCart) > 0 && $subtotal > 0 && $subtotal < 200) ? 5.00 : 0.00;
$total = $subtotal + $shipping;

$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $logged_in ? $_SESSION['name'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Book Heaven</title>
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

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
        }

        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .payment-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 24px;
        }

        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 12px;
        }
    </style>
</head>

<body class="bg-gray-50">
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
                                <a href="order_user.php" class="block px-4 py-2 hover:bg-gray-100">My Orders</a>
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
                                <span id="cartCount" class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo count($phpCart); ?></span>
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
                                        <span id="cartShippingFee">RM0.00</span>
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
                            <a href="children.php" class="block px-4 py-2 hover:bg-gray-100">Children</a>
                            <a href="academic.php" class="block px-4 py-2 hover:bg-gray-100">Academic</a>
                            <a href="business.php" class="block px-4 py-2 hover:bg-gray-100">Business</a>
                            <a href="foodNdrink.php" class="block px-4 py-2 hover:bg-gray-100">Food & Drink</a>
                            <a href="romance.php" class="block px-4 py-2 hover:bg-gray-100">Romance</a>
                        </div>
                    </li>
                    <li><a href="newRelease.php" class="text-gray-800 hover:text-blue-600 font-medium">New Releases</a></li>
                    <li><a href="bestseller.php" class="text-gray-800 hover:text-blue-600 font-medium">Bestsellers</a></li>
                    <li><a href="promotion.php" class="text-gray-800 hover:text-blue-600 font-medium">Promotion</a></li>
                    <li><a href="about.html" class="text-gray-800 hover:text-blue-600 font-medium">About Us</a></li>
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
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Order Summary -->
                <div class="payment-card">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Your Order</h2>
                    <div id="orderSummary">
                        <?php if (empty($phpCart)): ?>
                            <div class="text-gray-500 text-center py-8">Your cart is empty.</div>
                        <?php else: ?>
                            <?php foreach ($phpCart as $item): ?>
                                <?php
                                $title = $item['title'] ?? $item['Name'] ?? 'Item';
                                $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
                                $price = (float)($item['price'] ?? $item['Price'] ?? 0);
                                $lineTotal = $price * $qty;
                                ?>
                                <div class="flex justify-between items-center py-4 border-b border-gray-200">
                                    <div class="flex items-center space-x-4">
                                        <img src="<?php echo htmlspecialchars($item['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($title); ?>" class="w-16 h-20 object-cover rounded">
                                        <div>
                                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($title); ?></h3>
                                            <p class="text-sm text-gray-600">Qty: <?php echo $qty; ?></p>
                                        </div>
                                    </div>
                                    <span class="font-medium text-gray-900">RM <?php echo number_format($lineTotal, 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="mt-6 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">RM <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping:</span>
                            <span class="font-medium">RM <?php echo number_format($shipping, 2); ?></span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2">
                            <span class="text-lg font-bold text-gray-900">Total:</span>
                            <span class="text-lg font-bold text-gray-900">RM <?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="payment-card">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Payment & Shipping Details</h2>

                    <form id="checkoutForm" method="POST" novalidate class="space-y-6">
                        <input type="hidden" name="pay_now" value="1">

                        <!-- Shipping Address -->
                        <div class="mb-6">
                            <div class="space-y-4">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address *</label>
                                    <input type="text" id="address" name="address" placeholder="Enter your full shipping address" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" id="email" name="email" required placeholder="Enter your email address"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method *</label>
                            <div class="space-y-3">
                                <!-- Credit/Debit Card Option -->
                                <div class="border border-gray-300 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <input type="radio" id="payment-card" name="payment_method" value="card" required class="mr-3">
                                        <label for="payment-card" class="flex items-center cursor-pointer">
                                            <span class="font-medium">Credit/Debit Card</span>
                                            <div class="flex space-x-2 ml-3">
                                                <img src="https://img.icons8.com/color/48/visa.png" alt="Visa" class="h-6">
                                                <img src="https://img.icons8.com/color/48/mastercard.png" alt="MasterCard" class="h-6">
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Card Details (Hidden by default, shown when selected) -->
                                    <div id="card-details" class="mt-4 pl-6 hidden">
                                        <div class="space-y-4">
                                            <div>
                                                <label for="cardNumber" class="block text-sm font-medium text-gray-700 mb-1">Card Number *</label>
                                                <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiry (MM/YY) *</label>
                                                    <input type="text" id="expiry" name="expiry" placeholder="MM/YY"
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div>
                                                    <label for="cvv" class="block text-sm font-medium text-gray-700 mb-1">CVV *</label>
                                                    <input type="password" id="cvv" name="cvv" maxlength="4" placeholder="123"
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Online Banking Option -->
                                <div class="border border-gray-300 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <input type="radio" id="payment-bank" name="payment_method" value="bank" class="mr-3">
                                        <label for="payment-bank" class="font-medium cursor-pointer">Online Banking</label>
                                    </div>

                                    <!-- Bank Selection (Hidden by default, shown when selected) -->
                                    <div id="bank-details" class="mt-4 pl-6 hidden">
                                        <div>
                                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Select Your Bank *</label>
                                            <select id="bank_name" name="bank_name" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Choose a bank</option>
                                                <option value="Maybank2u">Maybank2u</option>
                                                <option value="CIMB Clicks">CIMB Clicks</option>
                                                <option value="Bank Islam">Bank Islam</option>
                                                <option value="Agrobank">Agrobank</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition duration-200">
                            Pay RM <?php echo number_format($total, 2); ?>
                        </button>
                    </form>

                    <?php if ($payment_success): ?>
                        <div class="success-message mt-6">
                            <h3 class="text-xl font-bold mb-2">✅ Payment Successful!</h3>
                            <p class="mb-4">Your order has been placed successfully and is now being processed.</p>
                            <?php if (isset($_SESSION['last_order_ids'])): ?>
                                <p class="font-medium mb-4">Order ID: <?php echo $_SESSION['last_order_ids'][0]; ?></p>
                                <?php unset($_SESSION['last_order_ids']); ?>
                            <?php endif; ?>
                            <div class="flex space-x-4 justify-center">
                                <a href="order_user.php" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                    View My Orders
                                </a>
                                <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    Continue Shopping
                                </a>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['selected_bank_url'])): ?>
                            <script>
                                // Redirect to the selected bank in a new tab after successful order
                                setTimeout(function() {
                                    window.open('<?php echo $_SESSION['selected_bank_url']; ?>', '_blank');
                                }, 2000);
                                <?php unset($_SESSION['selected_bank_url']); ?>
                            </script>
                        <?php endif; ?>
                    <?php elseif ($error_message): ?>
                        <div class="error-message mt-6">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
                        <li><a href="promotion.php" class="text-gray-400 hover:text-white">Promotion</a></li>
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

    <!-- JavaScript -->
    <script>
        // Header functionality
        function toggleDropdown() {
            const dropdown = document.getElementById('categoriesDropdown');
            const icon = document.getElementById('dropdownIcon');
            dropdown.classList.toggle('show');
            icon.classList.toggle('rotate-180');
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        function toggleCart() {
            const panel = document.getElementById('cartPanel');
            panel.classList.toggle('show');
        }

        // Payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Hide all payment details
                document.getElementById('card-details').classList.add('hidden');
                document.getElementById('bank-details').classList.add('hidden');

                // Show selected payment details
                if (this.value === 'card') {
                    document.getElementById('card-details').classList.remove('hidden');
                } else if (this.value === 'bank') {
                    document.getElementById('bank-details').classList.remove('hidden');
                }
            });
        });

        // Form validation and formatting
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const address = document.getElementById('address').value;
            const email = document.getElementById('email').value;

            if (!address.trim()) {
                e.preventDefault();
                alert('Please enter your shipping address');
                return;
            }

            if (!email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }

            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return;
            }

            if (paymentMethod.value === 'card') {
                const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
                const expiry = document.getElementById('expiry').value;
                const cvv = document.getElementById('cvv').value;

                if (cardNumber.length < 16) {
                    e.preventDefault();
                    alert('Please enter a valid card number');
                    return;
                }

                if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                    e.preventDefault();
                    alert('Please enter a valid expiry date (MM/YY)');
                    return;
                }

                if (cvv.length < 3) {
                    e.preventDefault();
                    alert('Please enter a valid CVV');
                    return;
                }
            } else if (paymentMethod.value === 'bank') {
                const bankName = document.getElementById('bank_name').value;

                if (!bankName) {
                    e.preventDefault();
                    alert('Please select your bank');
                    return;
                }
            }
        });

        // Card number formatting
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 16);
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value;
        });

        // Expiry date formatting
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 4);
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            e.target.value = value;
        });

        // CVV input restriction
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });

        // Close dropdowns when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('button') && !event.target.closest('button')) {
                const dropdowns = document.getElementsByClassName("dropdown-menu");
                const userDropdowns = document.getElementsByClassName("user-menu");
                const actionPanels = document.getElementsByClassName("action-panel");
                const icons = document.getElementsByClassName("fas fa-chevron-down");

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

                for (let i = 0; i < icons.length; i++) {
                    const icon = icons[i];
                    if (icon.classList.contains('rotate-180')) {
                        icon.classList.remove('rotate-180');
                    }
                }
            }
        }

        // Update shipping fee display based on subtotal
        function updateShippingDisplay() {
            const subtotalElement = document.querySelector('.flex.justify-between.border-t.pt-2 span:last-child');
            const shippingElement = document.querySelector('.flex.justify-between:not(.border-t) span:last-child');
            const payButton = document.querySelector('button[type="submit"]');

            if (subtotalElement && shippingElement) {
                const subtotalText = subtotalElement.textContent.replace('RM ', '').trim();
                const subtotal = parseFloat(subtotalText);

                let shipping = 0.00;
                if (subtotal > 0 && subtotal < 200) {
                    shipping = 5.00;
                }

                // Update shipping display
                shippingElement.textContent = 'RM ' + shipping.toFixed(2);

                // Update total
                const total = subtotal + shipping;
                const totalElement = document.querySelector('.flex.justify-between.border-t.pt-2 + .flex.justify-between.border-t.pt-2 span:last-child');
                if (totalElement) {
                    totalElement.textContent = 'RM ' + total.toFixed(2);
                }

                // Update pay button
                if (payButton) {
                    payButton.textContent = 'Pay RM ' + total.toFixed(2);
                }
            }
        }

        // Initialize shipping display on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateShippingDisplay();
        });
    </script>
</body>

</html>