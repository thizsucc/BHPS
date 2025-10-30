<?php
// index.php - Book Heaven Homepage
session_start();

// Database connection (if needed)
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "bookheaven";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If database connection fails, we'll use static data
    $conn = null;
}

// Featured books data
$featuredBooks = [
    [
        'title' => 'The Lost Bookshop',
        'author' => 'Evie Woods',
        'price' => '45.90',
        'original_price' => '59.90',
        'rating' => 4.5,
        'badge' => 'BESTSELLER',
        'image' => 'img/books/Fiction/the-lost-bookshop.jpg',
        'category' => 'fiction'
    ],
    [
        'title' => 'Atomic Habits',
        'author' => 'James Clear',
        'price' => '52.90',
        'original_price' => '65.90',
        'rating' => 5.0,
        'badge' => 'POPULAR',
        'image' => 'img/books/Non-Fiction/atomic-habits.jpg',
        'category' => 'non-fiction'
    ],
    [
        'title' => 'Introduction to Algorithms',
        'author' => 'Thomas H. Cormen',
        'price' => '120.90',
        'original_price' => '150.90',
        'rating' => 4.8,
        'badge' => 'ACADEMIC',
        'image' => 'img/books/Academic/algorithms.jpg',
        'category' => 'academic'
    ],
    [
        'title' => 'The Very Hungry Caterpillar',
        'author' => 'Eric Carle',
        'price' => '25.90',
        'original_price' => '32.90',
        'rating' => 4.9,
        'badge' => 'CLASSIC',
        'image' => 'img/books/Childrens-Book/caterpillar.jpg',
        'category' => 'childrens-book'
    ]
];

// New arrivals data
$newArrivals = [
    [
        'title' => 'The Midnight Library',
        'author' => 'Matt Haig',
        'price' => '48.50',
        'original_price' => '62.00',
        'rating' => 4.0,
        'badge' => 'NEW',
        'image' => 'img/books/Fiction/midnight-library.jpg',
        'category' => 'fiction'
    ],
    [
        'title' => 'Educated',
        'author' => 'Tara Westover',
        'price' => '55.20',
        'original_price' => '69.00',
        'rating' => 4.5,
        'badge' => 'NEW',
        'image' => 'img/books/Non-Fiction/educated.jpg',
        'category' => 'non-fiction'
    ],
    [
        'title' => 'The Princeton Review MCAT Prep',
        'author' => 'The Princeton Review',
        'price' => '95.50',
        'original_price' => '120.00',
        'rating' => 4.7,
        'badge' => 'NEW',
        'image' => 'img/books/Academic/mcat-prep.jpg',
        'category' => 'academic'
    ],
    [
        'title' => 'Harry Potter and the Sorcerer\'s Stone',
        'author' => 'J.K. Rowling',
        'price' => '35.90',
        'original_price' => '45.90',
        'rating' => 4.9,
        'badge' => 'NEW',
        'image' => 'img/books/Childrens-Book/harry-potter.jpg',
        'category' => 'childrens-book'
    ]
];

// Categories data
$categories = [
    'fiction' => [
        'name' => 'Fiction',
        'count' => 245,
        'image' => 'img/categories/fiction.jpg',
        'description' => 'Explore imaginative stories and literary fiction'
    ],
    'non-fiction' => [
        'name' => 'Non-Fiction',
        'count' => 189,
        'image' => 'img/categories/non-fiction.jpg',
        'description' => 'Real stories, real knowledge, real inspiration'
    ],
    'academic' => [
        'name' => 'Academic',
        'count' => 156,
        'image' => 'img/categories/academic.jpg',
        'description' => 'Textbooks and scholarly resources for students'
    ],
    'childrens-book' => [
        'name' => 'Children\'s Book',
        'count' => 203,
        'image' => 'img/categories/childrens.jpg',
        'description' => 'Wonderful books for young readers'
    ],
    'bestseller' => [
        'name' => 'Bestseller',
        'count' => 78,
        'image' => 'img/categories/bestseller.jpg',
        'description' => 'Current bestselling titles'
    ]
];

// Handle search
$searchQuery = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = htmlspecialchars($_GET['search']);
    // Redirect to category page with search query
    header("Location: category.php?category=fiction&search=" . urlencode($searchQuery));
    exit();
}

// Handle newsletter subscription
$newsletterMessage = '';
if (isset($_POST['subscribe'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        $newsletterMessage = "Thank you for subscribing with: " . htmlspecialchars($email);
    } else {
        $newsletterMessage = "Please enter a valid email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Heaven - Your Online Bookstore</title>
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
        .hero-slider {
            animation: slide 15s infinite;
        }
        @keyframes slide {
            0%, 20% { transform: translateX(0); }
            25%, 45% { transform: translateX(-100%); }
            50%, 70% { transform: translateX(-200%); }
            75%, 95% { transform: translateX(-300%); }
        }
        .category-card:hover .category-overlay {
            opacity: 1;
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
                    <a href="index.php" class="flex items-center">
                        <h1 class="text-2xl font-bold text-blue-600">Book Heaven</h1>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="hidden md:flex flex-1 mx-8">
                    <div class="relative w-full max-w-xl">
                        <form method="GET" action="index.php" class="search-box flex items-center border rounded-full overflow-hidden">
                            <input type="text" name="search" placeholder="Search books, authors, ISBN..." 
                                   class="py-2 px-6 w-full focus:outline-none" style="min-width: 300px;"
                                   value="<?php echo $searchQuery; ?>">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 hover:bg-blue-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
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
                        <?php
                        $cart_count = 0;
                        if (isset($_SESSION['cart'])) {
                            $cart_count = array_sum($_SESSION['cart']);
                        }
                        if ($cart_count > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?php echo $cart_count; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Mobile Search (hidden on desktop) -->
            <div class="md:hidden py-2">
                <div class="relative">
                    <form method="GET" action="index.php" class="search-box flex items-center border rounded-full overflow-hidden">
                        <input type="text" name="search" placeholder="Search books..." 
                               class="py-2 px-4 w-full focus:outline-none"
                               value="<?php echo $searchQuery; ?>">
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
                        <a href="#" class="text-gray-800 hover:text-blue-600 font-medium flex items-center transition-colors duration-200 group">
                            Categories <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200 group-hover:rotate-180"></i>
                        </a>
                        <div class="dropdown-menu absolute bg-white shadow-lg rounded mt-2 py-2 w-48 z-50">
                            <?php foreach ($categories as $key => $category): ?>
                            <a href="category.php?category=<?php echo $key; ?>" class="block px-4 py-2 hover:bg-gray-100 transition-colors duration-150">
                                <?php echo $category['name']; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li><a href="#new-arrivals" class="text-gray-800 hover:text-blue-600 font-medium">New Releases</a></li>
                    <li><a href="category.php?category=bestseller" class="text-gray-800 hover:text-blue-600 font-medium">Bestsellers</a></li>
                    <li><a href="#promotions" class="text-gray-800 hover:text-blue-600 font-medium">Promotions</a></li>
                    <li><a href="#events" class="text-gray-800 hover:text-blue-600 font-medium">Events</a></li>
                    <li><a href="#about" class="text-gray-800 hover:text-blue-600 font-medium">About Us</a></li>
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
    <section class="relative bg-gradient-to-r from-blue-600 to-purple-700 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Discover Your Next Favorite Book</h1>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Explore thousands of titles across all genres. From bestsellers to hidden gems, we have something for every reader.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="category.php?category=bestseller" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                    Shop Bestsellers
                </a>
                <a href="#categories" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-blue-600 transition-colors">
                    Browse Categories
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section id="categories" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Browse by Category</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories as $key => $category): ?>
                <a href="category.php?category=<?php echo $key; ?>" class="category-card block relative rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all">
                    <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-book text-white text-6xl"></i>
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center category-overlay opacity-0 transition-opacity">
                        <div class="text-white text-center p-4">
                            <h3 class="text-xl font-bold mb-2"><?php echo $category['name']; ?></h3>
                            <p><?php echo $category['count']; ?> books available</p>
                        </div>
                    </div>
                    <div class="p-4 bg-white">
                        <h3 class="text-lg font-semibold"><?php echo $category['name']; ?></h3>
                        <p class="text-gray-600 text-sm"><?php echo $category['description']; ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Books -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Featured Books</h2>
                <a href="category.php?category=bestseller" class="text-blue-600 hover:text-blue-800 font-medium">View All <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($featuredBooks as $book): ?>
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <?php if (!empty($book['badge'])): ?>
                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">
                            <?php echo $book['badge']; ?>
                        </span>
                        <?php endif; ?>
                        
                        <img src="<?php echo $book['image']; ?>" 
                             alt="<?php echo $book['title']; ?>" 
                             class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700 add-to-cart" 
                                    data-title="<?php echo $book['title']; ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1"><?php echo $book['title']; ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?php echo $book['author']; ?></p>
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400 text-sm">
                                <?php
                                $fullStars = floor($book['rating']);
                                $hasHalfStar = ($book['rating'] - $fullStars) >= 0.5;
                                
                                for ($i = 0; $i < $fullStars; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                
                                if ($hasHalfStar) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                    $fullStars++;
                                }
                                
                                for ($i = $fullStars; $i < 5; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <span class="text-gray-500 text-xs ml-1">(<?php echo $book['rating']; ?>)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">RM<?php echo $book['price']; ?></span>
                            <?php if (!empty($book['original_price'])): ?>
                                <span class="text-xs text-gray-500 line-through">RM<?php echo $book['original_price']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- New Arrivals -->
    <section id="new-arrivals" class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">New Arrivals</h2>
                <a href="#new-arrivals" class="text-blue-600 hover:text-blue-800 font-medium">View All <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($newArrivals as $book): ?>
                <div class="book-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <div class="relative">
                        <?php if (!empty($book['badge'])): ?>
                        <span class="absolute top-2 left-2 bg-green-600 text-white text-xs px-2 py-1 rounded">
                            <?php echo $book['badge']; ?>
                        </span>
                        <?php endif; ?>
                        
                        <img src="<?php echo $book['image']; ?>" 
                             alt="<?php echo $book['title']; ?>" 
                             class="w-full h-64 object-cover">
                        <div class="book-actions absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 transition-opacity">
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="bg-white text-blue-800 rounded-full p-2 mx-1 hover:bg-blue-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-blue-600 text-white rounded-full p-2 mx-1 hover:bg-blue-700 add-to-cart" 
                                    data-title="<?php echo $book['title']; ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-1"><?php echo $book['title']; ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?php echo $book['author']; ?></p>
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400 text-sm">
                                <?php
                                $fullStars = floor($book['rating']);
                                $hasHalfStar = ($book['rating'] - $fullStars) >= 0.5;
                                
                                for ($i = 0; $i < $fullStars; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                
                                if ($hasHalfStar) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                    $fullStars++;
                                }
                                
                                for ($i = $fullStars; $i < 5; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <span class="text-gray-500 text-xs ml-1">(<?php echo $book['rating']; ?>)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">RM<?php echo $book['price']; ?></span>
                            <?php if (!empty($book['original_price'])): ?>
                                <span class="text-xs text-gray-500 line-through">RM<?php echo $book['original_price']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Promotions -->
    <section id="promotions" class="py-12 bg-gradient-to-r from-blue-500 to-purple-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Summer Reading Sale</h2>
            <p class="text-xl mb-6">Up to 50% off on selected titles. Limited time offer!</p>
            <a href="category.php?category=bestseller" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors inline-block">
                Shop Now
            </a>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
                <p class="text-gray-600 mb-6">Subscribe to our newsletter for the latest book releases, exclusive offers, and literary events.</p>
                <?php if (!empty($newsletterMessage)): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    <?php echo $newsletterMessage; ?>
                </div>
                <?php endif; ?>
                <form method="POST" action="" class="flex flex-col sm:flex-row justify-center">
                    <input type="email" name="email" placeholder="Your email address" required
                           class="px-4 py-3 mb-2 sm:mb-0 sm:mr-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
                    <button type="submit" name="subscribe" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700">Subscribe</button>
                </form>
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
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="category.php?category=fiction" class="text-gray-400 hover:text-white">Fiction</a></li>
                        <li><a href="category.php?category=non-fiction" class="text-gray-400 hover:text-white">Non-Fiction</a></li>
                        <li><a href="category.php?category=academic" class="text-gray-400 hover:text-white">Academic</a></li>
                        <li><a href="category.php?category=childrens-book" class="text-gray-400 hover:text-white">Children's Books</a></li>
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
        // Add interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Add to cart functionality
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const bookTitle = this.getAttribute('data-title');
                    
                    // Create a simple cart notification
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    notification.textContent = `Added "${bookTitle}" to cart!`;
                    document.body.appendChild(notification);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                });
            });

            // Mobile menu toggle
            const mobileMenuButton = document.querySelector('.md\\:hidden button');
            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', function() {
                    // This would toggle a mobile menu - implementation depends on your design
                    alert('Mobile menu would open here');
                });
            }
        });
    </script>
</body>
</html>