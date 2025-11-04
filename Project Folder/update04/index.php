<?php
session_start();
include_once 'db_connect.php';

$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $logged_in ? $_SESSION['name'] : '';

// Fetch latest 10 book IDs for NEW badge
$latest_book_ids = [];
$latestRes = $conn->query("SELECT BookID FROM book ORDER BY BookID DESC LIMIT 10");
if ($latestRes) {
    while ($r = $latestRes->fetch_assoc()) {
        $latest_book_ids[] = $r['BookID'];
    }
}

// Fetch bestseller IDs
$bestseller_file = __DIR__ . '/bestseller_books.json';
$bestseller_ids = file_exists($bestseller_file) ? json_decode(file_get_contents($bestseller_file), true) : [];
if (!is_array($bestseller_ids)) $bestseller_ids = [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Heaven - Your Ultimate Book Destination</title>
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

        .book-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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

        /* Modal Styles */
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
                        <div id="userDropdown" class="user-menu absolute mt-2 right-0" style="display:none;">
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
                                <a href="payment.php" onclick="syncCartAndCheckout(event)" class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700">Checkout</a>
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
                <li><a href="promotion.php" class="text-gray-800 hover:text-blue-600 font-medium">Promotions</a></li>
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
        <a href="fiction.php" class="font-medium">Browse Categories</a>
    </div>
</div>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Discover Your Next Favorite Book</h1>
                <p class="text-xl mb-6">Millions of titles at your fingertips with exclusive member benefits.</p>
            </div>
            <div class="md:w-1/2">
                <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80"
                    alt="Books" class="rounded-lg shadow-xl w-full">
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Browse Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="fiction.php" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                <div class="bg-blue-100 text-blue-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-book text-2xl"></i>
                </div>
                <span class="font-medium">Fiction</span>
            </a>
            <a href="academic.php" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                <div class="bg-green-100 text-green-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-graduation-cap text-2xl"></i>
                </div>
                <span class="font-medium">Academic</span>
            </a>
            <a href="children.php" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                <div class="bg-yellow-100 text-yellow-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-child text-2xl"></i>
                </div>
                <span class="font-medium">Children</span>
            </a>
            <a href="business.php" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                <div class="bg-purple-100 text-purple-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <span class="font-medium">Business</span>
            </a>
            <a href="foodNdrink.php" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                <div class="bg-red-100 text-red-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-utensils text-2xl"></i>
                </div>
                <span class="font-medium">Food & Drink</span>
            </a>
            <a href="romance.php" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                <div class="bg-pink-100 text-pink-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-heart text-2xl"></i>
                </div>
                <span class="font-medium">Romance</span>
            </a>
        </div>
    </div>
</section>

<!-- Bestsellers Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold">Bestsellers</h2>
            <a href="bestseller.php" class="text-blue-600 hover:underline">View All</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <?php
            if (count($bestseller_ids) > 0) {
                $in = str_repeat('?,', count($bestseller_ids) - 1) . '?';
                $stmt = $conn->prepare("SELECT * FROM book WHERE BookID IN ($in)");
                $stmt->bind_param(str_repeat('s', count($bestseller_ids)), ...$bestseller_ids);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($book = $result->fetch_assoc()):
                    // Check if book is in promotion
                    $is_promotion = $book['is_promotion'] && $book['promotion_price'];
                    $display_price = $is_promotion ? $book['promotion_price'] : $book['Price'];
                    $original_price = $is_promotion ? $book['Price'] : null;
                    $discount = $is_promotion ? round((($book['Price'] - $book['promotion_price']) / $book['Price']) * 100) : 0;
            ?>
                    <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden mx-auto w-56" onclick="openProductModal('<?php echo addslashes($book['BookID']); ?>')">
                        <div class="relative">
                            <?php if ($is_promotion): ?>
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">PROMOTION</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($book['ImagePath']); ?>" alt="<?php echo htmlspecialchars($book['Name']); ?> Book Cover" class="w-full h-64 object-cover">
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
                                <span class="text-gray-500 text-xs ml-1">Bestseller</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-gray-900">RM<?php echo number_format($display_price, 2); ?></span>
                                    <?php if ($is_promotion && $original_price): ?>
                                        <span class="text-gray-500 text-sm line-through ml-2">RM<?php echo number_format($original_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($is_promotion && $discount > 0): ?>
                                <div class="mt-2">
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Save <?php echo $discount; ?>%</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile;
            } else { ?>
                <div class="col-span-full text-center text-gray-500 py-8">No bestsellers selected yet.</div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- New Releases -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold">New Releases</h2>
            <a href="newRelease.php" class="text-blue-600 hover:underline">View All</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <?php
            $new_books = $conn->query("SELECT * FROM book ORDER BY BookID DESC LIMIT 5");
            if ($new_books && $new_books->num_rows > 0) {
                while ($book = $new_books->fetch_assoc()):
                    // Check if book is in promotion
                    $is_promotion = $book['is_promotion'] && $book['promotion_price'];
                    $display_price = $is_promotion ? $book['promotion_price'] : $book['Price'];
                    $original_price = $is_promotion ? $book['Price'] : null;
                    $discount = $is_promotion ? round((($book['Price'] - $book['promotion_price']) / $book['Price']) * 100) : 0;
            ?>
                    <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden mx-auto w-56" onclick="openProductModal('<?php echo addslashes($book['BookID']); ?>')">
                        <div class="relative">
                            <?php if ($is_promotion): ?>
                                <span class="absolute top-2 left-12 bg-red-600 text-white text-xs px-2 py-1 rounded">PROMOTION</span>
                            <?php endif; ?>
                            <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                            <img src="<?php echo htmlspecialchars($book['ImagePath']); ?>" alt="<?php echo htmlspecialchars($book['Name']); ?> Book Cover" class="w-full h-64 object-cover">
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
                                    <i class="far fa-star"></i>
                                </div>
                                <span class="text-gray-500 text-xs ml-1">(4.0)</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-gray-900">RM<?php echo number_format($display_price, 2); ?></span>
                                    <?php if ($is_promotion && $original_price): ?>
                                        <span class="text-gray-500 text-sm line-through ml-2">RM<?php echo number_format($original_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($is_promotion && $discount > 0): ?>
                                <div class="mt-2">
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Save <?php echo $discount; ?>%</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile;
            } else { ?>
                <div class="col-span-full text-center text-gray-500 py-8">No new arrivals yet.</div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- Promotion Section -->
<section class="py-12 bg-red-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold">Special Promotions</h2>
            <a href="promotion.php" class="text-red-600 hover:underline font-medium">View All</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <?php
            // Fetch promotion books (max 5)
            $promotion_books = [];
            $promo_sql = "SELECT * FROM book WHERE is_promotion = 1 AND promotion_price IS NOT NULL ORDER BY BookID DESC LIMIT 5";
            $promo_result = $conn->query($promo_sql);

            if ($promo_result && $promo_result->num_rows > 0) {
                while ($book = $promo_result->fetch_assoc()):
                    $discount = round((($book['Price'] - $book['promotion_price']) / $book['Price']) * 100);
            ?>
                    <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden mx-auto w-56" onclick="openProductModal('<?php echo addslashes($book['BookID']); ?>')">
                        <div class="relative">
                            <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">PROMOTION</span>
                            <?php if (in_array($book['BookID'], $latest_book_ids)): ?>
                                <span class="absolute top-2 left-24 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($book['ImagePath']); ?>" alt="<?php echo htmlspecialchars($book['Name']); ?> Book Cover" class="w-full h-64 object-cover">
                            <?php if ($book['Quantity'] < 10 && $book['Quantity'] > 0): ?>
                                <span class="absolute top-2 right-2 bg-yellow-600 text-white text-xs px-2 py-1 rounded">Low Stock</span>
                            <?php elseif ($book['Quantity'] == 0): ?>
                                <span class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded">Out of Stock</span>
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
                                <div>
                                    <span class="text-red-600 font-bold text-lg">RM<?php echo number_format($book['promotion_price'], 2); ?></span>
                                    <span class="text-gray-500 text-sm line-through ml-2">RM<?php echo number_format($book['Price'], 2); ?></span>
                                </div>
                            </div>
                            <?php if ($discount > 0): ?>
                                <div class="mt-2">
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Save <?php echo $discount; ?>%</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile;
            } else { ?>
                <div class="col-span-full text-center text-gray-500 py-8">
                    <i class="fas fa-tag text-4xl mb-4 text-gray-300"></i>
                    <p>No active promotions at the moment.</p>
                    <p class="text-sm mt-2">Check back later for special offers!</p>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- About Us Banner -->
<section class="py-12 bg-blue-800 text-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
                <h2 class="text-3xl font-bold mb-6">About Book Heaven</h2>
                <p class="text-base md:text-lg mb-6 leading-relaxed">Ever wonder who helps you find your next favorite read? At Book Heaven, we're not just a bookstore; we're your personal matchmakers in the world of literature, passionate about connecting you with stories that feel like they were written just for you.</p>
                <p class="text-base md:text-lg mb-8 leading-relaxed">Discover the readers behind the pages on our "About Us" journey and join our community of book lovers today.</p>
                <a href="about.html" class="bg-white text-blue-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 inline-block transition duration-300">Learn More About Us</a>
            </div>
            <div class="md:w-1/2 mt-6 md:mt-0">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
                    alt="Bookstore interior" class="rounded-lg shadow-xl w-full">
            </div>
        </div>
    </div>
</section>

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
                        <div id="modalProductPromotionPrice" class="text-2xl font-bold text-gray-900"></div>
                        <div id="modalProductOriginalPrice" class="text-sm text-gray-500 line-through"></div>
                        <div id="modalProductDiscount" class="text-sm text-green-600 mt-1"></div>
                    </div>

                    <div id="modalProductDescription" class="mb-6 text-gray-600">
                        A captivating book that will take you on an unforgettable journey through imagination and adventure.
                    </div>

                    <form id="addToCartForm">
                        <input type="hidden" id="modalProductId">

                        <div class="mb-6">
                            <h3 class="font-medium mb-2">Quantity</h3>
                            <div class="flex items-center">
                                <button type="button" onclick="changeQuantity(-1)" class="bg-gray-200 px-3 py-1 rounded-l hover:bg-gray-300">-</button>
                                <input id="productQuantity" type="number" value="1" min="1" class="border-t border-b border-gray-200 w-12 text-center py-1">
                                <button type="button" onclick="changeQuantity(1)" class="bg-gray-200 px-3 py-1 rounded-r hover:bg-gray-300">+</button>
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <button type="button" onclick="addToCartFromModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 flex-1">
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
    // Cart data - load from session
    let cart = <?php echo isset($_SESSION['cart']) ? json_encode($_SESSION['cart']) : '[]'; ?>;
    let currentProduct = null;

    function toggleDropdown() {
        const dropdown = document.getElementById('categoriesDropdown');
        const icon = document.getElementById('dropdownIcon');
        dropdown.classList.toggle('show');
        icon.classList.toggle('rotate-180');
    }

    function toggleUserMenu() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }

    function toggleCart() {
        const panel = document.getElementById('cartPanel');
        panel.classList.toggle('show');
    }

    // Product Modal Functions
    function openProductModal(bookId) {
        // Fetch book details from database
        fetch(`get_book_details.php?book_id=${bookId}`)
            .then(response => response.json())
            .then(book => {
                if (book.error) {
                    alert('Book not found');
                    return;
                }

                currentProduct = {
                    book_id: book.BookID,
                    title: book.Name,
                    author: book.Author,
                    price: book.is_promotion && book.promotion_price ? parseFloat(book.promotion_price) : parseFloat(book.Price),
                    original_price: parseFloat(book.Price),
                    is_promotion: book.is_promotion,
                    promotion_price: book.promotion_price ? parseFloat(book.promotion_price) : null,
                    image: book.ImagePath,
                    description: book.Description || 'A captivating book that will take you on an unforgettable journey through imagination and adventure.'
                };

                document.getElementById('modalProductTitle').textContent = book.Name;
                document.getElementById('modalProductAuthor').textContent = 'By ' + book.Author;

                // Handle pricing display for promotion
                const promotionPriceElement = document.getElementById('modalProductPromotionPrice');
                const originalPriceElement = document.getElementById('modalProductOriginalPrice');
                const discountElement = document.getElementById('modalProductDiscount');

                if (book.is_promotion && book.promotion_price) {
                    promotionPriceElement.textContent = 'RM' + parseFloat(book.promotion_price).toFixed(2);
                    promotionPriceElement.className = 'text-2xl font-bold text-red-600';
                    originalPriceElement.textContent = 'RM' + parseFloat(book.Price).toFixed(2);
                    originalPriceElement.style.display = 'block';

                    // Calculate and show discount
                    const discount = Math.round(((book.Price - book.promotion_price) / book.Price) * 100);
                    if (discount > 0) {
                        discountElement.textContent = 'Save ' + discount + '%';
                        discountElement.style.display = 'block';
                    } else {
                        discountElement.style.display = 'none';
                    }
                } else {
                    promotionPriceElement.textContent = 'RM' + parseFloat(book.Price).toFixed(2);
                    promotionPriceElement.className = 'text-2xl font-bold text-gray-900';
                    originalPriceElement.style.display = 'none';
                    discountElement.style.display = 'none';
                }

                document.getElementById('modalProductImage').src = book.ImagePath;
                document.getElementById('productQuantity').value = 1;

                // Set the description
                const descriptionElement = document.getElementById('modalProductDescription');
                if (book.Description) {
                    descriptionElement.textContent = book.Description;
                } else {
                    descriptionElement.textContent = 'A captivating book that will take you on an unforgettable journey through imagination and adventure.';
                }

                document.getElementById('productModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                console.error('Error fetching book details:', error);
                alert('Error loading book details');
            });
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

    function addToCartFromModal() {
        <?php if (!$logged_in): ?>
            alert('Please sign in to add items to your cart.');
            window.location.href = 'login.php';
            return;
        <?php endif; ?>

        if (!currentProduct) return;

        const quantity = parseInt(document.getElementById('productQuantity').value);
        const format = 'Physical'; // Default format since radio buttons are removed

        // Use promotion price if available, otherwise use original price
        const price = currentProduct.is_promotion && currentProduct.promotion_price ?
            currentProduct.promotion_price : currentProduct.price;

        // Check if item already in cart
        const existingItemIndex = cart.findIndex(item =>
            item.book_id === currentProduct.book_id && item.format === format
        );

        if (existingItemIndex !== -1) {
            cart[existingItemIndex].quantity += quantity;
        } else {
            cart.push({
                book_id: currentProduct.book_id,
                title: currentProduct.title,
                author: currentProduct.author,
                price: price,
                image: currentProduct.image,
                quantity: quantity,
                format: format
            });
        }

        updateCartDisplay();
        syncCartToServer();
        closeModal();

        // Show success message
        alert('Item added to cart successfully!');
    }

    function syncCartToServer(callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'sync_cart.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        if (typeof callback === 'function') callback();
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        xhr.send('cart=' + encodeURIComponent(JSON.stringify(cart)));
    }

    function buyNow() {
        <?php if (!$logged_in): ?>
            alert('Please sign in to buy books.');
            window.location.href = 'login.php';
            return;
        <?php endif; ?>
        if (!currentProduct) return;
        const quantity = parseInt(document.getElementById('productQuantity').value);
        const format = 'Physical'; // Default format since radio buttons are removed

        // Use promotion price if available, otherwise use original price
        const price = currentProduct.is_promotion && currentProduct.promotion_price ?
            currentProduct.promotion_price : currentProduct.price;

        cart = [{
            book_id: currentProduct.book_id,
            title: currentProduct.title,
            author: currentProduct.author,
            price: price,
            image: currentProduct.image,
            quantity: quantity,
            format: format
        }];

        updateCartDisplay();
        closeModal();
        syncCartToServer(function() {
            window.location.href = 'payment.php';
        });
    }

    function updateCartDisplay() {
        const cartItems = document.getElementById('cartItems');
        const cartCount = document.getElementById('cartCount');
        const cartShippingFee = document.getElementById('cartShippingFee');
        const cartSubtotal = document.getElementById('cartSubtotal');
        const cartSubtotalNav = document.getElementById('cartSubtotalNav');

        if (cart.length === 0) {
            cartItems.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-shopping-cart text-2xl mb-2 text-gray-300"></i>
                    <p>Your cart is empty</p>
                </div>
            `;
            cartCount.textContent = '0';
            cartShippingFee.textContent = 'RM0.00';
            cartSubtotal.textContent = 'RM0.00';
            cartSubtotalNav.textContent = 'RM0.00';
            return;
        }

        let itemsHTML = '';
        let subtotal = 0;
        let count = 0;

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            count += item.quantity;

            itemsHTML += `
                <div class="cart-item">
                    <img src="${item.image}" alt="${item.title}">
                    <div class="cart-item-details">
                        <div class="font-medium text-sm">${item.title}</div>
                        <div class="text-xs text-gray-600">${item.author}</div>
                        <div class="flex justify-between text-xs">
                            <span>RM${item.price.toFixed(2)} x ${item.quantity}</span>
                            <span class="font-bold">RM${itemTotal.toFixed(2)}</span>
                        </div>
                    </div>
                    <button onclick="removeFromCart('${item.book_id}', '${item.format}')" class="text-red-500 ml-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        });

        // Calculate shipping fee - FREE if subtotal > 200
        const shippingFee = subtotal > 200 ? 0 : 5.00;
        const total = subtotal + shippingFee;

        cartItems.innerHTML = itemsHTML;
        cartCount.textContent = count;
        cartShippingFee.textContent = shippingFee === 0 ? 'FREE' : `RM${shippingFee.toFixed(2)}`;
        cartSubtotal.textContent = `RM${total.toFixed(2)}`;
        cartSubtotalNav.textContent = `RM${total.toFixed(2)}`;
    }

    function removeFromCart(bookId, format) {
        cart = cart.filter(item => !(item.book_id === bookId && item.format === format));
        updateCartDisplay();
        syncCartToServer();
    }

    // Close the dropdown if clicked outside
    window.onclick = function(event) {
        if (!event.target.matches('button') && !event.target.closest('button')) {
            // Close all dropdown-menus
            const dropdowns = document.getElementsByClassName("dropdown-menu");
            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].classList.remove('show');
            }
            // Close user menu (use style.display)
            const userDropdown = document.getElementById('userDropdown');
            if (userDropdown && userDropdown.style.display === 'block') {
                userDropdown.style.display = 'none';
            }
            // Close action panels
            const actionPanels = document.getElementsByClassName("action-panel");
            for (let i = 0; i < actionPanels.length; i++) {
                actionPanels[i].classList.remove('show');
            }
            // Remove rotate-180 from icons
            const icons = document.getElementsByClassName("fas fa-chevron-down");
            for (let i = 0; i < icons.length; i++) {
                icons[i].classList.remove('rotate-180');
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

    // Sync cart to PHP session and go to payment page
    function syncCartAndCheckout(e) {
        e.preventDefault();
        <?php if (!$logged_in): ?>
            alert('Please sign in to checkout.');
            window.location.href = 'login.php';
            return;
        <?php endif; ?>
        syncCartToServer(function() {
            window.location.href = 'payment.php';
        });
    }

    // Initialize cart display on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCartDisplay();
    });
</script>
</body>

</html>