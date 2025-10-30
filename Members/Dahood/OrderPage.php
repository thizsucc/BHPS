<?php
// Sample order data - in a real application, this would come from a database
 $orderData = [
    'order_id' => 'ORD-2023-0615-001',
    'order_date' => 'June 15, 2023',
    'status' => 'Processing',
    'customer_name' => 'John Doe',
    'address' => [
        'street' => '123, Jalan Bukit Bintang',
        'city' => 'Kuala Lumpur',
        'postal' => '55100',
        'state' => 'Wilayah Persekutuan Kuala Lumpur'
    ],
    'phone' => '+60123456789',
    'email' => 'johndoe@example.com',
    'items' => [
        [
            'id' => 1,
            'title' => 'The Silent Patient',
            'author' => 'Alex Michaelides',
            'isbn' => '9781250301697',
            'image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80',
            'quantity' => 1,
            'price' => 45.90
        ],
        [
            'id' => 2,
            'title' => 'Atomic Habits',
            'author' => 'James Clear',
            'isbn' => '9780735211292',
            'image' => 'https://images.unsplash.com/photo-1589998059171-988d322dfe03?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80',
            'quantity' => 1,
            'price' => 52.90
        ],
        [
            'id' => 3,
            'title' => 'The Midnight Library',
            'author' => 'Matt Haig',
            'isbn' => '9780525559474',
            'image' => 'https://images.unsplash.com/photo-1541963463532-d68292c34b19?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80',
            'quantity' => 1,
            'price' => 48.50
        ]
    ],
    'shipping_fee' => 5.00,
    'tax' => 0.00
];

// Calculate total
 $subtotal = 0;
foreach ($orderData['items'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
 $total = $subtotal + $orderData['shipping_fee'] + $orderData['tax'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Book Heaven</title>
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

    <!-- Order Details Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- Order Header -->
                    <div class="bg-blue-800 text-white p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div>
                                <h1 class="text-2xl font-bold">Order Details</h1>
                                <p class="text-blue-200 mt-1">Order ID: <span class="font-mono"><?php echo htmlspecialchars($orderData['order_id']); ?></span></p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <span class="order-status bg-green-600 text-white px-4 py-2 rounded-full text-sm font-medium">
                                    <?php echo htmlspecialchars($orderData['status']); ?>
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
                                    <p class="font-medium"><?php echo htmlspecialchars($orderData['customer_name']); ?></p>
                                    <p><?php echo htmlspecialchars($orderData['address']['street']); ?></p>
                                    <p><?php echo htmlspecialchars($orderData['address']['postal'] . ' ' . $orderData['address']['city']); ?></p>
                                    <p><?php echo htmlspecialchars($orderData['address']['state']); ?></p>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-bold text-lg mb-3 flex items-center">
                                    <i class="fas fa-phone-alt text-blue-600 mr-2"></i>
                                    Contact Information
                                </h3>
                                <div class="space-y-1">
                                    <p class="font-medium">Phone: <span class="font-normal"><?php echo htmlspecialchars($orderData['phone']); ?></span></p>
                                    <p class="font-medium">Email: <span class="font-normal"><?php echo htmlspecialchars($orderData['email']); ?></span></p>
                                    <p class="font-medium">Order Date: <span class="font-normal"><?php echo htmlspecialchars($orderData['order_date']); ?></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-8">
                            <h3 class="font-bold text-lg mb-4">Order Items</h3>
                            <div class="border rounded-lg overflow-hidden">
                                <?php foreach ($orderData['items'] as $item): ?>
                                <div class="flex flex-col md:flex-row items-start <?php echo ($item !== end($orderData['items'])) ? 'border-b' : ''; ?> p-4">
                                    <div class="w-full md:w-1/6 mb-4 md:mb-0">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="Book Cover" class="w-full h-32 object-cover rounded">
                                    </div>
                                    <div class="w-full md:w-3/6 px-4">
                                        <h4 class="font-medium"><?php echo htmlspecialchars($item['title']); ?></h4>
                                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($item['author']); ?></p>
                                        <p class="text-gray-500 text-xs mt-1">ISBN: <?php echo htmlspecialchars($item['isbn']); ?></p>
                                    </div>
                                    <div class="w-full md:w-1/6 px-4">
                                        <p class="text-gray-600">Qty: <?php echo (int)$item['quantity']; ?></p>
                                    </div>
                                    <div class="w-full md:w-1/6 px-4 text-right">
                                        <p class="font-medium">RM<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-8">
                            <h3 class="font-bold text-lg mb-4">Order Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span>Subtotal (<?php echo count($orderData['items']); ?> items)</span>
                                    <span>RM<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Shipping Fee</span>
                                    <span>RM<?php echo number_format($orderData['shipping_fee'], 2); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Tax</span>
                                    <span>RM<?php echo number_format($orderData['tax'], 2); ?></span>
                                </div>
                                <div class="flex justify-between font-bold text-lg pt-3 border-t">
                                    <span>Total</span>
                                    <span>RM<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
                            <a href="my_orders.php" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to My Orders
                            </a>
                            <button id="cancel-order-btn" class="inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-times-circle mr-2"></i>
                                Cancel Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cancel Order Confirmation Modal -->
    <div id="cancel-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Confirm Cancellation</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to cancel this order? This action cannot be undone.</p>
            <div class="flex justify-end space-x-4">
                <button id="cancel-modal-close" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">No, Keep Order</button>
                <button id="confirm-cancel" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Yes, Cancel Order</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Cancel order modal functionality
            const cancelBtn = document.getElementById('cancel-order-btn');
            const cancelModal = document.getElementById('cancel-modal');
            const closeModalBtn = document.getElementById('cancel-modal-close');
            const confirmCancelBtn = document.getElementById('confirm-cancel');

            cancelBtn.addEventListener('click', function() {
                cancelModal.classList.remove('hidden');
            });

            closeModalBtn.addEventListener('click', function() {
                cancelModal.classList.add('hidden');
            });

            confirmCancelBtn.addEventListener('click', function() {
                // In a real application, you would send an AJAX request to cancel the order
                // For this example, we'll just show an alert and redirect
                alert('Order has been cancelled successfully.');
                window.location.href = 'cancel_order.php?order_id=<?php echo urlencode($orderData['order_id']); ?>';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === cancelModal) {
                    cancelModal.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>