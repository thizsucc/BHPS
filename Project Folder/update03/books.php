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

// Define categories and formats (for the edit modal)
$categories = ["Fiction", "Non-Fiction", "Children's Books", "Academic"];
$formats = ["Physical", "E-book"];

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $where = " WHERE Name LIKE '%$search%' OR Author LIKE '%$search%' OR BookID LIKE '%$search%' OR Category LIKE '%$search%'";
}

// Fetch books with search filter
$books_query = $conn->query("SELECT * FROM book $where ORDER BY BookID DESC");

// Handle form submissions for updating and deleting books (similar to staff.php)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_book'])) {
        $old_book_id = $_POST['old_book_id'];
        $new_book_id = trim($_POST['editBookID']);
        $name = $_POST['editBookTitle'];
        $author = $_POST['editBookAuthor'];
        $category = $_POST['editBookCategory'];
        $format = $_POST['editBookFormat'];
        $price = $_POST['editBookPrice'];
        $quantity = $_POST['editBookStock'];
        $description = $_POST['editBookDescription'];
        
        // Check if new Book ID already exists (if it's different from old one)
        if ($new_book_id != $old_book_id) {
            $check_query = $conn->prepare("SELECT * FROM book WHERE BookID = ?");
            $check_query->bind_param("s", $new_book_id);
            $check_query->execute();
            $result = $check_query->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Book ID '$new_book_id' already exists. Please use a different Book ID.";
                header("Location: books.php");
                exit();
            }
        }
        
        // Handle image upload for update
        $imagePath = $_POST['currentImagePath'];
        if (isset($_FILES['editBookCover']) && $_FILES['editBookCover']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = time() . '_' . basename($_FILES['editBookCover']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['editBookCover']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
                // Delete old image if it's not the default
                if ($_POST['currentImagePath'] != 'default-book.jpg' && file_exists($_POST['currentImagePath'])) {
                    unlink($_POST['currentImagePath']);
                }
            }
        }
        
        $stmt = $conn->prepare("UPDATE book SET BookID = ?, Name = ?, Author = ?, Category = ?, Format = ?, Price = ?, Quantity = ?, Description = ?, ImagePath = ? WHERE BookID = ?");
        $stmt->bind_param("sssssdisss", $new_book_id, $name, $author, $category, $format, $price, $quantity, $description, $imagePath, $old_book_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Book updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating book: " . $conn->error;
        }
        header("Location: books.php");
        exit();
    }
    
    if (isset($_POST['delete_book'])) {
        $book_id = $_POST['book_id'];
        
        // Get image path before deleting
        $result = $conn->query("SELECT ImagePath FROM book WHERE BookID = '$book_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $imagePath = $row['ImagePath'];
            // Delete image file if it's not the default
            if ($imagePath != 'default-book.jpg' && file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM book WHERE BookID = ?");
        $stmt->bind_param("s", $book_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Book deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting book: " . $conn->error;
        }
        header("Location: books.php");
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
    <title>Manage Books - Staff Portal</title>
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
            max-width: 600px;
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
                    <div class="dropdown relative">
                        <button class="flex items-center text-gray-700 hover:text-blue-600">
                            <i class="fas fa-user-circle text-xl mr-1"></i>
                            <span><?php echo htmlspecialchars($staff_name); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 bg-white shadow-lg rounded mt-2 py-2 w-48 z-50">
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
                    <li><a href="books.php" class="text-blue-600 font-medium border-b-2 border-blue-600 pb-1">Books</a></li>
                    <li><a href="customers.php" class="text-gray-800 hover:text-blue-600 font-medium">Customers</a></li>
                    <li><a href="reports.php" class="text-gray-800 hover:text-blue-600 font-medium">Reports</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
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
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manage Books</h1>
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

        <!-- Books Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-medium text-gray-800">Book List</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs">
                        <tr>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Cover</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Book ID</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Format</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        // Load current bestsellers
                        $bestseller_file = __DIR__ . '/bestseller_books.json';
                        $bestseller_ids = file_exists($bestseller_file) ? json_decode(file_get_contents($bestseller_file), true) : [];
                        if (!is_array($bestseller_ids)) $bestseller_ids = [];
                        while ($book = $books_query->fetch_assoc()):
                            $is_bestseller = in_array($book['BookID'], $bestseller_ids);
                        ?>
                        <tr>
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
                                <?php echo htmlspecialchars($book['Category']); ?>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900">
                                <?php echo htmlspecialchars($book['Format']); ?>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900">
                                RM<?php echo number_format($book['Price'], 2); ?>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap">
                                <span class="text-xs <?php echo getStockClass($book['Quantity']); ?> px-1 py-0.5 rounded">
                                    <?php echo getStockText($book['Quantity']); ?>
                                </span>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-xs font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-2" onclick="editBook('<?php echo $book['BookID']; ?>')">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button class="text-red-600 hover:text-red-900 mr-2" onclick="deleteBook('<?php echo $book['BookID']; ?>')">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                                <button class="ml-1 px-1 py-0.5 rounded <?php echo $is_bestseller ? 'bg-yellow-400 text-white' : 'bg-gray-200 text-gray-700'; ?> bestseller-btn" data-bookid="<?php echo $book['BookID']; ?>" style="font-size:11px;">
                                    <i class="fas fa-star"></i> <?php echo $is_bestseller ? 'Bestseller' : 'Mark as Bestseller'; ?>
                                </button>
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

    <script>
    // Bestseller button logic
    document.addEventListener('DOMContentLoaded', function() {
        function updateBestseller(bookId, add) {
            // Get all current bestseller IDs from DOM
            let ids = Array.from(document.querySelectorAll('.bestseller-btn.bg-yellow-400')).map(btn => btn.getAttribute('data-bookid'));
            if (add && !ids.includes(bookId)) ids.push(bookId);
            if (!add) ids = ids.filter(id => id !== bookId);
            fetch('bestseller_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bestsellers: ids })
            }).then(() => location.reload());
        }
        document.querySelectorAll('.bestseller-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const bookId = this.getAttribute('data-bookid');
                const isBestseller = this.classList.contains('bg-yellow-400');
                updateBestseller(bookId, !isBestseller);
            });
        });
    });
    </script>
    </main>

    <!-- Edit Book Modal -->
    <div id="editBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header flex justify-between items-center mb-4 pb-2 border-b">
                <h2 class="text-xl font-bold text-gray-800">Edit Book</h2>
                <span class="close text-2xl cursor-pointer" onclick="closeModal('editBookModal')">&times;</span>
            </div>
            <form method="POST" id="editBookForm" enctype="multipart/form-data">
                <input type="hidden" name="old_book_id" id="oldBookId">
                <input type="hidden" name="currentImagePath" id="currentImagePath">
                <input type="hidden" name="update_book">
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookID">Book ID <span class="text-red-500">*</span></label>
                    <input type="text" id="editBookID" name="editBookID" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">You can change the Book ID if needed.</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookTitle">Book Title <span class="text-red-500">*</span></label>
                    <input type="text" id="editBookTitle" name="editBookTitle" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookAuthor">Author <span class="text-red-500">*</span></label>
                    <input type="text" id="editBookAuthor" name="editBookAuthor" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookCategory">Category <span class="text-red-500">*</span></label>
                        <select id="editBookCategory" name="editBookCategory" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookFormat">Format <span class="text-red-500">*</span></label>
                        <select id="editBookFormat" name="editBookFormat" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a format</option>
                            <?php foreach($formats as $format): ?>
                                <option value="<?php echo $format; ?>"><?php echo $format; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookPrice">Price (RM) <span class="text-red-500">*</span></label>
                        <input type="number" id="editBookPrice" name="editBookPrice" step="0.01" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" for="editBookStock">Stock Quantity <span class="text-red-500">*</span></label>
                        <input type="number" id="editBookStock" name="editBookStock" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookCover">Book Cover</label>
                    <div id="currentBookCover" class="mb-2">
                        <img id="currentBookCoverImg" src="" alt="Current Book Cover" class="book-cover-preview">
                    </div>
                    <input type="file" id="editBookCover" name="editBookCover" accept="image/*" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="editBookCoverPreview" class="mt-2 hidden">
                        <img src="" alt="New Book Cover Preview" class="book-cover-preview">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="editBookDescription">Description</label>
                    <textarea id="editBookDescription" name="editBookDescription" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400" onclick="closeModal('editBookModal')">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Book Form -->
    <form method="POST" id="deleteBookForm" style="display: none;">
        <input type="hidden" name="book_id" id="deleteBookId">
        <input type="hidden" name="delete_book">
    </form>

    <!-- Footer (same as staff.php) -->
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
                        <li><a href="reports.php" class="text-gray-400 hover:text-white">Reports</a></li>
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
        // Edit book function
        function editBook(bookId) {
            // Fetch book details via AJAX
            fetch('get_book_details.php?book_id=' + bookId)
                .then(response => response.json())
                .then(book => {
                    document.getElementById('oldBookId').value = book.BookID;
                    document.getElementById('editBookID').value = book.BookID;
                    document.getElementById('editBookTitle').value = book.Name;
                    document.getElementById('editBookAuthor').value = book.Author;
                    document.getElementById('editBookCategory').value = book.Category;
                    document.getElementById('editBookFormat').value = book.Format;
                    document.getElementById('editBookPrice').value = book.Price;
                    document.getElementById('editBookStock').value = book.Quantity;
                    document.getElementById('editBookDescription').value = book.Description || '';
                    document.getElementById('currentImagePath').value = book.ImagePath;
                    
                    // Set current book cover image
                    const currentCover = document.getElementById('currentBookCoverImg');
                    currentCover.src = book.ImagePath || 'default-book.jpg';
                    
                    document.getElementById('editBookModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching book details:', error);
                    alert('Error loading book details');
                });
        }

        // Delete book
        function deleteBook(bookId) {
            if (confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                document.getElementById('deleteBookId').value = bookId;
                document.getElementById('deleteBookForm').submit();
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

        // Image preview for edit book form
        document.getElementById('editBookCover').addEventListener('change', function(e) {
            const preview = document.getElementById('editBookCoverPreview');
            const img = preview.querySelector('img');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchValue = this.value;
                window.location.href = 'books.php?search=' + encodeURIComponent(searchValue);
            }
        });
    </script>
</body>
</html>

<?php
// Helper functions
function getStockClass($quantity) {
    if ($quantity == 0) return 'bg-red-100 text-red-800';
    if ($quantity < 10) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
}

function getStockText($quantity) {
    if ($quantity == 0) return 'Out of Stock';
    if ($quantity < 10) return 'Low Stock: ' . $quantity;
    return 'In Stock: ' . $quantity;
}
?>