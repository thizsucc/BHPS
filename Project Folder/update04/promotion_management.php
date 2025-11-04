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

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $where = " WHERE (Name LIKE '%$search%' OR Author LIKE '%$search%' OR BookID LIKE '%$search%' OR Category LIKE '%$search%')";
}

// Fetch all books for promotion management
$books_query = $conn->query("SELECT * FROM book $where ORDER BY is_promotion DESC, BookID DESC");

// Handle promotion actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['set_promotion'])) {
        $book_id = $_POST['book_id'];
        $promotion_price = $_POST['promotion_price'];

        // Validate promotion price is less than original price
        $book_query = $conn->query("SELECT Price FROM book WHERE BookID = '$book_id'");
        if ($book_query->num_rows > 0) {
            $book = $book_query->fetch_assoc();
            if ($promotion_price >= $book['Price']) {
                $_SESSION['error'] = "Promotion price must be less than original price.";
                header("Location: promotion_management.php");
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
        header("Location: promotion_management.php");
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
        header("Location: promotion_management.php");
        exit();
    }

    if (isset($_POST['update_promotion'])) {
        $book_id = $_POST['book_id'];
        $promotion_price = $_POST['promotion_price'];

        // Validate promotion price is less than original price
        $book_query = $conn->query("SELECT Price FROM book WHERE BookID = '$book_id'");
        if ($book_query->num_rows > 0) {
            $book = $book_query->fetch_assoc();
            if ($promotion_price >= $book['Price']) {
                $_SESSION['error'] = "Promotion price must be less than original price.";
                header("Location: promotion_management.php");
                exit();
            }

            $stmt = $conn->prepare("UPDATE book SET promotion_price = ? WHERE BookID = ?");
            $stmt->bind_param("ds", $promotion_price, $book_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Promotion price updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating promotion: " . $conn->error;
            }
        }
        header("Location: promotion_management.php");
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
    <title>Manage Promotions - Staff Portal</title>
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
            max-width: 500px;
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

        .promotion-badge {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: white;
            font-weight: bold;
        }

        .price-original {
            text-decoration: line-through;
            color: #6b7280;
            font-size: 0.9em;
        }

        .price-promotion {
            color: #ef4444;
            font-weight: bold;
            font-size: 1.1em;
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
                    <li><a href="staff.php" class="text-gray-800 hover:text-blue-600 font-medium">Dashboard</a></li>
                    <li><a href="orders.php" class="text-gray-800 hover:text-blue-600 font-medium">Orders</a></li>
                    <li><a href="books.php" class="text-gray-800 hover:text-blue-600 font-medium">Books</a></li>
                    <li><a href="promotion_management.php" class="text-blue-600 font-medium border-b-2 border-blue-600 pb-1">Promotions</a></li>
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
            <h1 class="text-2xl font-bold text-gray-800">Promotion Management</h1>
            <div class="flex space-x-2">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search books..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <a href="staff.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Set New Promotion Section -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Set New Promotion</h2>
            </div>
            <div class="p-6">
                <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 mb-2" for="promotionBook">Select Book</label>
                        <select id="promotionBook" name="book_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Choose a book</option>
                            <?php
                            // Reset the query to get all non-promotion books
                            $non_promo_books = $conn->query("SELECT BookID, Name, Author, Price FROM book WHERE is_promotion = 0 ORDER BY Name");
                            while ($book = $non_promo_books->fetch_assoc()):
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
                            <i class="fas fa-tag mr-2"></i>Set Promotion
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Promotions -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Current Promotions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs">
                        <tr>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Cover</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Book ID</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Title & Author</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Original Price</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Promotion Price</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        // Reset query pointer to loop through books again
                        $books_query->data_seek(0);
                        $has_promotions = false;
                        while ($book = $books_query->fetch_assoc()):
                            if ($book['is_promotion'] && $book['promotion_price']):
                                $has_promotions = true;
                                $discount = round((($book['Price'] - $book['promotion_price']) / $book['Price']) * 100);
                        ?>
                                <tr class="bg-blue-50">
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <img src="<?php echo $book['ImagePath'] ?: 'default-book.jpg'; ?>"
                                            alt="Book Cover" class="h-8 w-8 object-cover rounded">
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($book['BookID']); ?></div>
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($book['Name']); ?></div>
                                        <div class="text-xs text-gray-500">by <?php echo htmlspecialchars($book['Author']); ?></div>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-500 line-through">
                                        RM<?php echo number_format($book['Price'], 2); ?>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap text-xs font-bold text-red-600">
                                        RM<?php echo number_format($book['promotion_price'], 2); ?>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap text-xs">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded"><?php echo $discount; ?>% OFF</span>
                                    </td>
                                    <td class="px-2 py-2 whitespace-nowrap text-xs font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-2" onclick="editPromotion('<?php echo $book['BookID']; ?>', <?php echo $book['Price']; ?>, <?php echo $book['promotion_price']; ?>)">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                                            <button type="submit" name="remove_promotion" class="text-red-600 hover:text-red-900" onclick="return confirm('Remove this book from promotion?')">
                                                <i class="fas fa-times mr-1"></i>Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                            endif;
                        endwhile;

                        if (!$has_promotions):
                            ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-tag text-4xl mb-4 text-gray-300"></i>
                                    <p>No active promotions found.</p>
                                    <p class="text-sm mt-2">Use the form above to create new promotions.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- All Books Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">All Books</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs">
                        <tr>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Cover</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Book ID</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        // Reset query pointer again
                        $books_query->data_seek(0);
                        while ($book = $books_query->fetch_assoc()):
                            $is_promotion = $book['is_promotion'] && $book['promotion_price'];
                        ?>
                            <tr class="<?php echo $is_promotion ? 'bg-blue-50' : ''; ?>">
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <img src="<?php echo $book['ImagePath'] ?: 'default-book.jpg'; ?>"
                                        alt="Book Cover" class="h-8 w-8 object-cover rounded">
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($book['BookID']); ?></div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($book['Name']); ?></div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900">
                                    <?php echo htmlspecialchars($book['Author']); ?>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900">
                                    <?php if ($is_promotion): ?>
                                        <div class="price-promotion">RM<?php echo number_format($book['promotion_price'], 2); ?></div>
                                        <div class="price-original">RM<?php echo number_format($book['Price'], 2); ?></div>
                                    <?php else: ?>
                                        RM<?php echo number_format($book['Price'], 2); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <?php if ($is_promotion): ?>
                                        <span class="text-xs bg-red-200 text-red-700 px-2 py-1 rounded">PROMOTION</span>
                                    <?php else: ?>
                                        <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded">REGULAR</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-xs font-medium">
                                    <?php if (!$is_promotion): ?>
                                        <button class="text-green-600 hover:text-green-900 mr-2" onclick="quickSetPromotion('<?php echo $book['BookID']; ?>', <?php echo $book['Price']; ?>)">
                                            <i class="fas fa-tag mr-1"></i>Set Promotion
                                        </button>
                                    <?php else: ?>
                                        <button class="text-blue-600 hover:text-blue-900 mr-2" onclick="editPromotion('<?php echo $book['BookID']; ?>', <?php echo $book['Price']; ?>, <?php echo $book['promotion_price']; ?>)">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                                            <button type="submit" name="remove_promotion" class="text-red-600 hover:text-red-900" onclick="return confirm('Remove this book from promotion?')">
                                                <i class="fas fa-times mr-1"></i>Remove
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($books_query->num_rows == 0): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-book-open text-4xl mb-4"></i>
                    <p>No books found. <?php if (!empty($search)): ?>Try a different search term.<?php endif; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Edit Promotion Modal -->
    <div id="editPromotionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header flex justify-between items-center mb-4 pb-2 border-b">
                <h2 class="text-xl font-bold text-gray-800">Edit Promotion Price</h2>
                <span class="close text-2xl cursor-pointer" onclick="closeModal('editPromotionModal')">&times;</span>
            </div>
            <form method="POST" id="editPromotionForm">
                <input type="hidden" name="book_id" id="editPromotionBookId">
                <input type="hidden" name="update_promotion">

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editPromotionBookTitle">Book</label>
                    <input type="text" id="editPromotionBookTitle" class="w-full px-3 py-2 border rounded-lg bg-gray-100" readonly>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2" for="editPromotionOriginalPrice">Original Price (RM)</label>
                        <input type="number" id="editPromotionOriginalPrice" class="w-full px-3 py-2 border rounded-lg bg-gray-100" readonly>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" for="editPromotionPrice">Promotion Price (RM) <span class="text-red-500">*</span></label>
                        <input type="number" id="editPromotionPrice" name="promotion_price" step="0.01" min="0.01" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div id="discountInfo" class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
                        <!-- Discount information will be shown here -->
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400" onclick="closeModal('editPromotionModal')">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Promotion</button>
                </div>
            </form>
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
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="staff.php" class="text-gray-400 hover:text-white">Dashboard</a></li>
                        <li><a href="orders.php" class="text-gray-400 hover:text-white">Orders</a></li>
                        <li><a href="books.php" class="text-gray-400 hover:text-white">Books</a></li>
                        <li><a href="promotion_management.php" class="text-gray-400 hover:text-white">Promotions</a></li>
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

        // Quick set promotion
        function quickSetPromotion(bookId, originalPrice) {
            document.getElementById('promotionBook').value = bookId;
            document.getElementById('promotionPrice').value = (originalPrice * 0.9).toFixed(2);
            document.getElementById('promotionPrice').focus();

            // Scroll to the promotion form
            document.getElementById('promotionBook').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Edit promotion
        function editPromotion(bookId, originalPrice, currentPromotionPrice) {
            // Find the book title
            const row = document.querySelector(`tr:has(button[onclick*="${bookId}"])`);
            const bookTitle = row ? row.querySelector('td:nth-child(3) .text-xs.font-medium').textContent : 'Book';
            const author = row ? row.querySelector('td:nth-child(3) .text-gray-500').textContent.replace('by ', '') : '';

            document.getElementById('editPromotionBookId').value = bookId;
            document.getElementById('editPromotionBookTitle').value = `${bookTitle} by ${author}`;
            document.getElementById('editPromotionOriginalPrice').value = originalPrice;
            document.getElementById('editPromotionPrice').value = currentPromotionPrice;
            document.getElementById('editPromotionPrice').max = originalPrice - 0.01;

            // Calculate and show discount
            updateDiscountInfo(originalPrice, currentPromotionPrice);

            document.getElementById('editPromotionModal').style.display = 'flex';
        }

        // Update discount information
        function updateDiscountInfo(originalPrice, promotionPrice) {
            const discount = Math.round(((originalPrice - promotionPrice) / originalPrice) * 100);
            const savings = (originalPrice - promotionPrice).toFixed(2);

            document.getElementById('discountInfo').innerHTML = `
                <strong>Discount: ${discount}% OFF</strong><br>
                Customers save: RM${savings} per book
            `;
        }

        // Real-time discount calculation
        document.getElementById('editPromotionPrice').addEventListener('input', function() {
            const originalPrice = parseFloat(document.getElementById('editPromotionOriginalPrice').value);
            const promotionPrice = parseFloat(this.value);

            if (promotionPrice && promotionPrice < originalPrice) {
                updateDiscountInfo(originalPrice, promotionPrice);
            }
        });

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

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchValue = this.value;
                window.location.href = 'promotion_management.php?search=' + encodeURIComponent(searchValue);
            }
        });
    </script>
</body>

</html>