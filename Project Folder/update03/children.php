<?php
session_start();
include 'db_connect.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch user information if logged in
$user_name = "Guest";
$logged_in = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $logged_in = true;
    $user_name = $_SESSION['name'];
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $where = " WHERE (Name LIKE '%$search%' OR Author LIKE '%$search%' OR BookID LIKE '%$search%' OR Category LIKE '%$search%') AND Category = 'Children'";
} else {
    $where = " WHERE Category = 'Children'";
}

// Fetch books from Children category with search filter
$children_books = [];
$result = $conn->query("SELECT * FROM book $where ORDER BY BookID DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $children_books[] = $row;
    }
}

// Latest 10 book IDs for NEW badge
$latest_book_ids = [];
$latestRes = $conn->query("SELECT BookID FROM book ORDER BY BookID DESC LIMIT 10");
if ($latestRes) {
    while ($r = $latestRes->fetch_assoc()) {
        $latest_book_ids[] = $r['BookID'];
    }
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!$logged_in) {
        $_SESSION['error'] = "Please sign in to add items to your cart.";
        header("Location: login.php");
        exit();
    }
    
    $book_id = $_POST['book_id'];
    $quantity = (int)$_POST['quantity'];
    $format = $_POST['format'];
    
    // Find the book in database
    $book_result = $conn->query("SELECT * FROM book WHERE BookID = '$book_id'");
    if ($book_result->num_rows > 0) {
        $book = $book_result->fetch_assoc();
        
        $cart_item = [
            'book_id' => $book_id,
            'title' => $book['Name'],
            'author' => $book['Author'],
            'price' => (float)$book['Price'],
            'quantity' => $quantity,
            'format' => $format,
            'image' => $book['ImagePath'] ?: 'default-book.jpg'
        ];
        
        // Check if item already in cart
        $item_exists = false;
        foreach ($_SESSION['cart'] as $index => &$item) {
            if ($item['book_id'] == $book_id && $item['format'] == $format) {
                $item['quantity'] += $quantity;
                $item_exists = true;
                break;
            }
        }
        
        if (!$item_exists) {
            $_SESSION['cart'][] = $cart_item;
        }
        
        $_SESSION['success'] = "Item added to cart successfully!";
        
        // Return success for AJAX
        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))]);
            exit;
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle remove from cart via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $index = (int)$_POST['index'];
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

// Calculate cart totals
$cart_count = 0;
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
    $cart_total += $item['price'] * $item['quantity'];
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
    <title>Children Books - Book Heaven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: white;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
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
        .book-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .search-box:focus-within {
            border-color: #3b82f6;
        }
        .user-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
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
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
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
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Top Announcement Bar -->
    <div class="bg-blue-800 text-white text-center py-2 px-4 text-sm">
        Free shipping on orders over RM100 | Member discounts available
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
                        <form method="GET" class="search-box flex items-center border rounded-full overflow-hidden">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search children books, authors..." 
                                   class="py-2 px-6 w-full focus:outline-none" style="min-width: 300px;">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 hover:bg-blue-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <?php if ($logged_in): ?>
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="text-gray-700 hover:text-blue-600 flex items-center">
                                <i class="fas fa-user-circle text-xl mr-1"></i>
                                <span class="hidden md:inline"><?php echo htmlspecialchars($user_name); ?></span>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div id="userDropdown" class="user-menu absolute mt-2 right-0">
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Orders</a>
                                <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                        
                        <!-- Cart Icon with Subtotal -->
                        <div class="relative flex items-center space-x-2">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-700" id="cartSubtotalNav">RM<?php echo number_format($cart_total, 2); ?></div>
                                <div class="text-xs text-gray-500">Cart Total</div>
                            </div>
                            <button onclick="toggleCart()" class="text-gray-700 hover:text-blue-600 relative">
                                <i class="fas fa-shopping-cart text-xl"></i>
                                <span id="cartCount" class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                            </button>
                            <div id="cartPanel" class="action-panel absolute mt-2 right-0 top-full">
                                <div class="p-4 border-b">
                                    <h3 class="font-bold">Your Cart</h3>
                                </div>
                                <div id="cartItems">
                                    <?php if (empty($_SESSION['cart'])): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-shopping-cart text-2xl mb-2 text-gray-300"></i>
                                            <p>Your cart is empty</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                                        <div class="cart-item" id="cart-item-<?php echo $index; ?>">
                                            <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                            <div class="cart-item-details">
                                                <div class="font-medium text-sm"><?php echo htmlspecialchars($item['title']); ?></div>
                                                <div class="flex justify-between text-xs">
                                                    <span>RM<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                                    <span class="font-bold">RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                                </div>
                                            </div>
                                            <button onclick="removeCartItem(<?php echo $index; ?>)" class="text-red-500 ml-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4 border-t">
                                    <div class="flex justify-between mb-2">
                                        <span>Shipping fee:</span>
                                        <span id="cartShippingFee">RM<?php echo $cart_count > 0 ? '5.00' : '0.00'; ?></span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="cartSubtotal">RM<?php echo number_format($cart_total + ($cart_count > 0 ? 5 : 0), 2); ?></span>
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

            <!-- Mobile Search (hidden on desktop) -->
            <div class="md:hidden py-2">
                <div class="relative">
                    <form method="GET" class="search-box flex items-center border rounded-full overflow-hidden">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search children books..." 
                               class="py-2 px-4 w-full focus:outline-none">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
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
                            <a href="children.php" class="block px-4 py-2 hover:bg-gray-100">Children's Books</a>
                            <a href="business.php" class="block px-4 py-2 hover:bg-gray-100">Business</a>
                            <a href="foodNdrink.php" class="block px-4 py-2 hover:bg-gray-100">Food & Drink</a>
                            <a href="romance.php" class="block px-4 py-2 hover:bg-gray-100">Romance</a>
                        </div>
                    </li>
                    <li><a href="newRelease.php" class="text-gray-800 hover:text-blue-600 font-medium">New Releases</a></li>
                    <li><a href="bestseller.php" class="text-gray-800 hover:text-blue-600 font-medium">Bestsellers</a></li>
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

    <!-- Breadcrumb -->
    <div class="bg-white border-b py-4">
        <div class="container mx-auto px-4">
            <nav class="text-sm">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="index.php" class="text-blue-600 hover:text-blue-800">Home</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <a href="#" class="text-blue-600 hover:text-blue-800">Categories</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-gray-500">Children</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success_message): ?>
        <div class="container mx-auto px-4 mt-6">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="container mx-auto px-4 mt-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8" id="books-section">
        <div class="flex flex-col md:flex-row gap-8">
            <aside class="md:w-1/4">
                <!-- Category List -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Categories</h2>
                    <ul class="space-y-3">
                        <li><a href="fiction.php" class="text-gray-700 hover:text-blue-600">Fiction</a></li>
                        <li><a href="academic.php" class="text-gray-700 hover:text-blue-600">Academic</a></li>
                        <li><a href="children.php" class="text-blue-600 hover:text-blue-800 font-medium">Children</a></li>
                        <li><a href="business.php" class="text-gray-700 hover:text-blue-600">Business</a></li>
                        <li><a href="foodNdrink.php" class="text-gray-700 hover:text-blue-600">Food & Drink</a></li>
                        <li><a href="romance.php" class="text-gray-700 hover:text-blue-600">Romance</a></li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">About Children Books</h2>
                    <p class="text-gray-600 mb-4">
                        Our children's category features stories from beloved authors and fresh new voices, including timeless classics and exciting new favorites. Discover colorful adventures, 
                        delightful characters, and tales that spark imagination and stay in young hearts long after story time ends.
                    </p>
                </div>
            </aside>

            <!-- Main Products Section -->
            <div class="md:w-3/4">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                        <h1 class="text-2xl font-bold">Children Books</h1>
                        <div class="flex items-center mt-4 md:mt-0">
                            <span class="text-gray-600">Showing <?php echo count($children_books); ?> results</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($search)): ?>
                        <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                            <p class="text-blue-800">
                                Search results for: "<strong><?php echo htmlspecialchars($search); ?></strong>"
                                <a href="children.php" class="text-blue-600 hover:underline ml-4">Clear search</a>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Product Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($children_books as $book): ?>
                        <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" 
                             onclick="openProductModal(
                                 '<?php echo addslashes($book['BookID']); ?>',
                                 '<?php echo addslashes($book['Name']); ?>',
                                 '<?php echo addslashes($book['Author']); ?>',
                                 <?php echo $book['Price']; ?>,
                                 '<?php echo addslashes($book['Description']); ?>',
                                 '<?php echo $book['ImagePath'] ?: 'default-book.jpg'; ?>',
                                 '<?php echo $book['Format']; ?>'
                             )">
                            <div class="relative">
                                <?php if (in_array($book['BookID'], $latest_book_ids)): ?>
                                    <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                                <?php endif; ?>
                                <img src="<?php echo $book['ImagePath'] ?: 'default-book.jpg'; ?>"
                                    alt="Book Cover" class="w-full h-64 object-cover">
                                <?php if ($book['Quantity'] < 10 && $book['Quantity'] > 0): ?>
                                    <span class="absolute top-2 left-2 bg-yellow-600 text-white text-xs px-2 py-1 rounded">Low Stock</span>
                                <?php elseif ($book['Quantity'] == 0): ?>
                                    <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($book['Name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($book['Author']); ?></p>
                                <div class="flex items-center mb-2">
                                    <div class="flex text-yellow-400 text-sm">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <span class="text-gray-500 text-xs ml-1">(4.5)</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-900">RM<?php echo number_format($book['Price'], 2); ?></span>
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"><?php echo htmlspecialchars($book['Format']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($children_books)): ?>
                            <div class="col-span-3 text-center py-8">
                                <i class="fas fa-book-open text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500">No children books found<?php echo !empty($search) ? ' matching your search' : ''; ?>.</p>
                                <?php if (!empty($search)): ?>
                                    <a href="children.php" class="text-blue-600 hover:underline mt-2 inline-block">Browse all children books</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
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
                        <li><a href="fiction.php" class="text-gray-400 hover:text-white">Fiction</a></li>
                        <li><a href="children.php" class="text-gray-400 hover:text-white">Children</a></li>
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

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="relative">
                <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 z-10">
                    <i class="fas fa-times text-2xl"></i>
                </button>
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <div class="bg-gray-100 rounded-lg p-4 flex items-center justify-center h-96">
                            <img id="modalProductImage" src="" alt="Product" class="h-full object-contain">
                        </div>
                    </div>
                    
                    <!-- Product Details -->
                    <div>
                        <h2 id="modalProductTitle" class="text-2xl font-bold mb-2"></h2>
                        <p id="modalProductAuthor" class="text-gray-600 mb-4"></p>
                        
                        <div class="flex items-center mb-4">
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="text-gray-500 text-sm ml-2">(4.5) 12 reviews</span>
                        </div>
                        
                        <div class="mb-6">
                            <span id="modalProductPrice" class="text-2xl font-bold text-gray-900"></span>
                        </div>

                        <div id="modalProductDescription" class="mb-6 text-gray-600"></div>
                        
                        <form id="addToCartForm" method="POST">
                            <input type="hidden" name="book_id" id="modalProductId">
                            <input type="hidden" name="add_to_cart" value="1">
                            
                            <div class="mb-6">
                                <h3 class="font-medium mb-2">Format</h3>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="format" value="Physical" class="mr-2" checked>
                                        <span>Physical</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="format" value="E-book" class="mr-2">
                                        <span>Digital</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <h3 class="font-medium mb-2">Quantity</h3>
                                <div class="flex items-center">
                                    <button type="button" onclick="changeQuantity(-1)" class="bg-gray-200 px-3 py-1 rounded-l hover:bg-gray-300">-</button>
                                    <input id="productQuantity" name="quantity" type="number" value="1" min="1" class="border-t border-b border-gray-200 w-12 text-center py-1">
                                    <button type="button" onclick="changeQuantity(1)" class="bg-gray-200 px-3 py-1 rounded-r hover:bg-gray-300">+</button>
                                </div>
                            </div>
                            
                            <div class="flex space-x-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 flex-1">
                                    Add to Cart
                                </button>
                                <button type="button" onclick="buyNow()" class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 flex-1">
                                    Buy Now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
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

        // Product Modal Functions
        function openProductModal(bookId, title, author, price, description, image, format) {
            document.getElementById('modalProductTitle').textContent = title;
            document.getElementById('modalProductAuthor').textContent = 'By ' + author;
            document.getElementById('modalProductPrice').textContent = 'RM' + parseFloat(price).toFixed(2);
            document.getElementById('modalProductDescription').textContent = description;
            document.getElementById('modalProductImage').src = image;
            document.getElementById('modalProductId').value = bookId;
            document.getElementById('productQuantity').value = 1;
            
            // Set the format radio button based on the book's format
            const formatRadios = document.getElementsByName('format');
            for (let radio of formatRadios) {
                if (radio.value === format) {
                    radio.checked = true;
                }
            }
            
            document.getElementById('productModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function changeQuantity(amount) {
            const quantityInput = document.getElementById('productQuantity');
            let newValue = parseInt(quantityInput.value) + amount;
            if (newValue < 1) newValue = 1;
            quantityInput.value = newValue;
        }

        function buyNow() {
            // Submit the form and redirect to payment
            document.getElementById('addToCartForm').submit();
        }

        function removeCartItem(index) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                // Send AJAX request to remove item
                fetch('children.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'remove_item=1&index=' + index
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to update cart
                        window.location.reload();
                    }
                });
            }
        }

        // Handle form submission with AJAX
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            <?php if (!$logged_in): ?>
                alert('Please sign in to add items to your cart.');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
            
            // Submit the form via AJAX
            const formData = new FormData(this);
            formData.append('ajax', 'true');
            
            fetch('children.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.getElementById('cartCount').textContent = data.cart_count;
                    
                    // Show success message
                    alert('Item added to cart successfully!');
                    
                    // Close modal
                    closeModal();
                    
                    // Reload the page to update cart panel
                    window.location.reload();
                } else {
                    alert('Failed to add item to cart. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Close the dropdown if clicked outside
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
            
            // Close modal when clicking outside
            if (event.target === document.getElementById('productModal')) {
                closeModal();
            }
        };

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>