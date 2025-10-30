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
        .book-card:hover .book-actions {
            opacity: 1;
        }
        .search-box:focus-within {
            border-color: #3b82f6;
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
                    <a href="MainPage01.php">
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
                    <a href="#" class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-user text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-heart text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 relative">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </a>
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
                        <a href="#" class="text-gray-800 hover:text-blue-600 font-medium flex items-center transition-colors duration-200 group">
                            Categories <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200 group-hover:rotate-180"></i>
                        </a>
                        <div class="dropdown-menu absolute bg-white shadow-lg rounded mt-2 py-2 w-48 z-50">
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 transition-colors duration-150">Fiction</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 transition-colors duration-150">Non-Fiction</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 transition-colors duration-150">Children's Books</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 transition-colors duration-150">Academic</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 transition-colors duration-150">Best Sellers</a>
                        </div>
                    </li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">New Releases</a></li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Bestsellers</a></li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Promotions</a></li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">Events</a></li>
                    <li><a href="#" class="text-gray-800 hover:text-blue-600 font-medium">About Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Mobile Menu Button -->
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
                <a href="#" class="bg-gray-50 rounded-lg p-4 text-center hover:bg-blue-50 hover:shadow-md transition-all">
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">The Silent Patient</h3>
                        <p class="text-sm text-gray-600 mb-2">Alex Michaelides</p>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1589998059171-988d322dfe03?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1541963463532-d68292c34b19?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">The Midnight Library</h3>
                        <p class="text-sm text-gray-600 mb-2">Matt Haig</p>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1531346878377-a5be20888e57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Educated</h3>
                        <p class="text-sm text-gray-600 mb-2">Tara Westover</p>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1629992101753-56d196c8aabb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Where the Crawdads Sing</h3>
                        <p class="text-sm text-gray-600 mb-2">Delia Owens</p>
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
                            <span class="font-bold text-gray-900">RM49.90</span>
                            <span class="text-xs text-gray-500 line-through">RM64.90</span>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">The Thursday Murder Club</h3>
                        <p class="text-sm text-gray-600 mb-2">Richard Osman</p>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="https://images.unsplash.com/photo-1541963463532-d68292c34b19?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Project Hail Mary</h3>
                        <p class="text-sm text-gray-600 mb-2">Andy Weir</p>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="https://images.unsplash.com/photo-1589998059171-988d322dfe03?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">Klara and the Sun</h3>
                        <p class="text-sm text-gray-600 mb-2">Kazuo Ishiguro</p>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="https://images.unsplash.com/photo-1531346878377-a5be20888e57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
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
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">NEW</span>
                        <img src="https://images.unsplash.com/photo-1629992101753-56d196c8aabb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                             alt="Book Cover" class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1">The Four Winds</h3>
                        <p class="text-sm text-gray-600 mb-2">Kristin Hannah</p>
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

    <!-- Testimonials -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">What Our Readers Say</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"Book Heaven has the best selection of books I've seen online. Their delivery is fast and packaging is excellent. My books always arrive in perfect condition!"</p>
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Customer" class="h-10 w-10 rounded-full mr-3">
                        <div>
                            <h4 class="font-medium">Sarah Johnson</h4>
                            <p class="text-sm text-gray-500">Loyal Customer</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"I love the personalized recommendations from Book Heaven. They really understand my reading preferences. The membership program is totally worth it!"</p>
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Customer" class="h-10 w-10 rounded-full mr-3">
                        <div>
                            <h4 class="font-medium">Michael Chen</h4>
                            <p class="text-sm text-gray-500">Book Club Member</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"As an academic, I appreciate Book Heaven's extensive collection of scholarly works. Their customer service is exceptional - always helpful with my special requests."</p>
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Customer" class="h-10 w-10 rounded-full mr-3">
                        <div>
                            <h4 class="font-medium">Dr. Emily Wong</h4>
                            <p class="text-sm text-gray-500">University Professor</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-12 bg-white border-t border-b">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
                <p class="text-gray-600 mb-6">Subscribe to our newsletter for the latest book releases, exclusive offers, and literary events.</p>
                <div class="flex flex-col sm:flex-row justify-center">
                    <input type="email" placeholder="Your email address" 
                           class="px-4 py-3 mb-2 sm:mb-0 sm:mr-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
                    <button class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700">Subscribe</button>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a href="#" class="text-gray-400 hover:text-white">Shop</a></li>
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
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Shipping Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Returns & Refunds</a></li>
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
                <p class="text-gray-400 mb-4 md:mb-0">© 2023 Book Heaven. All rights reserved.</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a>
                    <a href="#" class="text-gray-400 hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle functionality would go here
        document.addEventListener('DOMContentLoaded', function() {
            // This is where you would add JavaScript for interactive elements
            // For example, mobile menu toggle, cart functionality, etc.
        });
    </script>
</body>
</html>