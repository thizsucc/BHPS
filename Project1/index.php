<?php
session_start();
$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $logged_in ? $_SESSION['name'] : '';
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
        /* Modal Styles */
        #productModal {
            transition: all 0.3s ease;
        }
        #productModal.hidden {
            opacity: 0;
            visibility: hidden;
        }
        #productModal:not(.hidden) {
            opacity: 1;
            visibility: visible;
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
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Orders</a>
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
                            <a href="fiction.html" class="block px-4 py-2 hover:bg-gray-100">Fiction</a>
                            <a href="Category.php" class="block px-4 py-2 hover:bg-gray-100">Non-Fiction</a>
                            <a href="Category.php" class="block px-4 py-2 hover:bg-gray-100">Children's Books</a>
                            <a href="Category.php" class="block px-4 py-2 hover:bg-gray-100">Academic</a>
                            <a href="Category.php" class="block px-4 py-2 hover:bg-gray-100">Best Sellers</a>
                        </div>
                    </li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">New Releases</a></li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Bestsellers</a></li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Promotions</a></li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Events</a></li>
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

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Discover Your Next Favorite Book</h1>
                    <p class="text-xl mb-6">Millions of titles at your fingertips with exclusive member benefits.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-white text-blue-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100">Shop Now</a>
                        <a href="#" class="border border-white px-6 py-3 rounded-full font-medium hover:bg-white hover:text-blue-800">Join Membership</a>
                    </div>
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
                <a href="fiction.html" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                    <div class="bg-blue-100 text-blue-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-book text-2xl"></i>
                    </div>
                    <span class="font-medium">Fiction</span>
                </a>
                <a href="#" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                    <div class="bg-green-100 text-green-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <span class="font-medium">Academic</span>
                </a>
                <a href="#" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                    <div class="bg-yellow-100 text-yellow-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-child text-2xl"></i>
                    </div>
                    <span class="font-medium">Children</span>
                </a>
                <a href="#" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                    <div class="bg-purple-100 text-purple-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <span class="font-medium">Business</span>
                </a>
                <a href="#" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                    <div class="bg-red-100 text-red-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-utensils text-2xl"></i>
                    </div>
                    <span class="font-medium">Food & Drink</span>
                </a>
                <a href="#" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
                    <div class="bg-pink-100 text-pink-800 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                    <span class="font-medium">Romance</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Books -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Bestsellers</h2>
                <a href="#" class="text-blue-600 hover:underline">View All</a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <!-- Book 1 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('IKIGAI: The Japanese Secret to a Long and Happy Life', 'Hector Garcia and Francesc Miralles', 45.90, 59.90, 'bookimg/ikigai.png')">
                    <div class="relative">
                        <img src="bookimg/ikigai.png" 
                             alt="IKIGAI Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">IKIGAI: The Japanese Secret to a Long and Happy Life</h3>
                        <p class="text-sm text-gray-600 mb-2">Hector Garcia and Francesc Miralles</p>
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
                            <span class="font-bold text-gray-900">RM45.90</span>
                            <span class="text-xs text-gray-500 line-through">RM59.90</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 2 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('Atomic Habits', 'James Clear', 52.90, 65.90, 'bookimg/atomicHabits.png')">
                    <div class="relative">
                        <img src="bookimg/atomicHabits.png" 
                             alt="Atomic Habits Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Atomic Habits</h3>
                        <p class="text-sm text-gray-600 mb-2">James Clear</p>
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-gray-500 text-xs ml-1">(5.0)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">RM52.90</span>
                            <span class="text-xs text-gray-500 line-through">RM65.90</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 3 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('DUNE', 'Frank Herbert', 48.50, 62.00, 'bookimg/Dune.jpg')">
                    <div class="relative">
                        <img src="bookimg/Dune.jpg" 
                             alt="Dune Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">DUNE</h3>
                        <p class="text-sm text-gray-600 mb-2">Frank Herbert</p>
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
                            <span class="font-bold text-gray-900">RM48.50</span>
                            <span class="text-xs text-gray-500 line-through">RM62.00</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 4 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('Pattern Cutting Deconstructed', 'Monisola Omotoso', 55.20, 69.00, 'bookimg/pattern.jpg')">
                    <div class="relative">
                        <img src="bookimg/pattern.jpg" 
                             alt="Pattern Cutting Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Pattern Cutting Deconstructed</h3>
                        <p class="text-sm text-gray-600 mb-2">Monisola Omotoso</p>
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
                            <span class="font-bold text-gray-900">RM55.20</span>
                            <span class="text-xs text-gray-500 line-through">RM69.00</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 5 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('VENOM', 'MARVEL', 12.90, 20.00, 'bookimg/Venom.jpg')">
                    <div class="relative">
                        <img src="bookimg/Venom.jpg" 
                             alt="Venom Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">VENOM</h3>
                        <p class="text-sm text-gray-600 mb-2">MARVEL</p>
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
                            <span class="font-bold text-gray-900">RM12.90</span>
                            <span class="text-xs text-gray-500 line-through">RM20.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">New Arrivals</h2>
                <a href="surprisedpage.html" class="text-blue-600 hover:underline">View All</a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <!-- Book 1 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('Red Rising', 'Pierce Brown', 47.90, null, 'bookimg/red.jpg')">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="bookimg/red.jpg" 
                             alt="Red Rising Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Red Rising</h3>
                        <p class="text-sm text-gray-600 mb-2">Pierce Brown</p>
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
                            <span class="font-bold text-gray-900">RM47.90</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 2 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('Nocturne', 'Lydia Madison', 54.90, null, 'bookimg/nocturne.jpg')">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="bookimg/nocturne.jpg" 
                             alt="Nocturne Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Nocturne</h3>
                        <p class="text-sm text-gray-600 mb-2">Lydia Madison</p>
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
                            <span class="font-bold text-gray-900">RM54.90</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 3 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('Gone With The Wind', 'Margaret Mitchell', 49.50, null, 'bookimg/WithTheWind.jpg')">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="bookimg/WithTheWind.jpg" 
                             alt="Gone With The Wind Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Gone With The Wind</h3>
                        <p class="text-sm text-gray-600 mb-2">Margaret Mitchell</p>
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
                            <span class="font-bold text-gray-900">RM49.50</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 4 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('The Push', 'Ashley Audrain', 52.20, null, 'bookimg/Xenophon.jpg')">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="bookimg/Xenophon.jpg" 
                             alt="The Push Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">The Push</h3>
                        <p class="text-sm text-gray-600 mb-2">Ashley Audrain</p>
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
                            <span class="font-bold text-gray-900">RM52.20</span>
                        </div>
                    </div>
                </div>
                
                <!-- Book 5 -->
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden" onclick="openProductModal('The Little Prince', 'Antoine de Saint-Exupéry', 51.90, null, 'bookimg/theprince.jpg')">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="bookimg/theprince.jpg" 
                             alt="The Little Prince Book Cover" class="w-full h-64 object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">The Little Prince</h3>
                        <p class="text-sm text-gray-600 mb-2">Antoine de Saint-Exupéry</p>
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
                            <span class="font-bold text-gray-900">RM51.90</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Promotional Banner -->
    <section class="py-12 bg-blue-800 text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h2 class="text-3xl font-bold mb-4">Join Our Membership Program</h2>
                    <p class="text-xl mb-6">Enjoy exclusive benefits including:</p>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-300"></i> 10% discount on all purchases
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-300"></i> Free shipping with no minimum spend
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-300"></i> Early access to sales and events
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-300"></i> Birthday rewards and special offers
                        </li>
                    </ul>
                    <a href="#" class="bg-white text-blue-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 inline-block">Sign Up Now</a>
                </div>
                <div class="md:w-1/2">
                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                         alt="Membership" class="rounded-lg shadow-xl w-full">
                </div>
            </div>
        </div>
    </section>

    <!-- Product Modal -->
    <div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="relative">
                <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 z-10">
                    <i class="fas fa-times text-2xl"></i>
                </button>
                <div class="grid md:grid-cols-2 gap-8 p-6">
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
                            <span id="modalProductOldPrice" class="text-sm text-gray-500 line-through ml-2"></span>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="font-medium mb-2">Quantity</h3>
                            <div class="flex items-center">
                                <button onclick="changeQuantity(-1)" class="bg-gray-200 px-3 py-1 rounded-l hover:bg-gray-300">-</button>
                                <input id="productQuantity" type="number" value="1" min="1" class="border-t border-b border-gray-200 w-12 text-center py-1">
                                <button onclick="changeQuantity(1)" class="bg-gray-200 px-3 py-1 rounded-r hover:bg-gray-300">+</button>
                            </div>
                        </div>
                        
                        <div class="flex space-x-4">
                            <button onclick="addToCartFromModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 flex-1">
                                Add to Cart
                            </button>
                            <button onclick="buyNow()" class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 flex-1">
                                Buy Now
                            </button>
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="font-medium mb-2">Description</h3>
                            <p class="text-gray-600">Discover this amazing book that will take you on an unforgettable journey through its pages. Perfect for readers of all ages.</p>
                        </div>
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
                        <li><a href="#" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">New Releases</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Bestsellers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Promotions</a></li>
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
        // Cart data
        let cart = [];
        let currentProduct = null;
        
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
        function openProductModal(title, author, price, oldPrice, image) {
            currentProduct = {
                title: title,
                author: author,
                price: price,
                oldPrice: oldPrice,
                image: image
            };
            
            document.getElementById('modalProductTitle').textContent = title;
            document.getElementById('modalProductAuthor').textContent = author;
            document.getElementById('modalProductPrice').textContent = 'RM' + price.toFixed(2);
            
            if (oldPrice) {
                document.getElementById('modalProductOldPrice').textContent = 'RM' + oldPrice.toFixed(2);
                document.getElementById('modalProductOldPrice').style.display = 'inline';
            } else {
                document.getElementById('modalProductOldPrice').style.display = 'none';
            }
            
            document.getElementById('modalProductImage').src = image;
            document.getElementById('productQuantity').value = 1;
            
            document.getElementById('productModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
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
            const existingItem = cart.find(item => item.title === currentProduct.title);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    title: currentProduct.title,
                    price: currentProduct.price,
                    image: currentProduct.image,
                    quantity: quantity
                });
            }
            
            updateCartDisplay();
            closeModal();
            
            // Show success message
            alert('Item added to cart successfully!');
        }

        function buyNow() {
            addToCartFromModal();
            // Redirect to checkout page
            window.location.href = 'payment.php';
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
                cartSubtotalNav.textContent = 'RM0.00'; // Update nav subtotal
                return;
            }
            
            let itemsHTML = '';
            let subtotal = 0;
            let count = 0;
            const shippingFee = 5.00;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                count += item.quantity;
                
                itemsHTML += `
                    <div class="cart-item">
                        <img src="${item.image}" alt="${item.title}">
                        <div class="cart-item-details">
                            <div class="font-medium">${item.title}</div>
                            <div class="flex justify-between">
                                <span>RM${item.price.toFixed(2)} x ${item.quantity}</span>
                                <span class="font-bold">RM${itemTotal.toFixed(2)}</span>
                            </div>
                        </div>
                        <button onclick="removeFromCart('${item.title}')" class="text-red-500 ml-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });
            
            const total = subtotal + shippingFee;
            
            cartItems.innerHTML = itemsHTML;
            cartCount.textContent = count;
            cartShippingFee.textContent = `RM${shippingFee.toFixed(2)}`;
            cartSubtotal.textContent = `RM${total.toFixed(2)}`;
            cartSubtotalNav.textContent = `RM${total.toFixed(2)}`;
        }

        function removeFromCart(title) {
            cart = cart.filter(item => item.title !== title);
            updateCartDisplay();
        }

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
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Initialize cart display
        updateCartDisplay();
    </script>
</body>
</html>