<?php
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST['userID']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $userName = trim($_POST['user_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $address = '';
    
    // Validate form data
    if (empty($userID) || empty($firstName) || empty($lastName) || empty($userName) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if User ID, username or email already exists
        $checkQuery = "SELECT * FROM user WHERE User_ID = ? OR user_Name = ? OR EmailAddress = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("sss", $userID, $userName, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "User ID, username or email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user with User_ID
            $insertQuery = "INSERT INTO user (User_ID, user_Name, EmailAddress, address, FirstName, LastName, Password) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sssssss", $userID, $userName, $email, $address, $firstName, $lastName, $hashedPassword);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now <a href='login.php' class='text-blue-600 hover:underline'>login</a>.";
                // Clear form fields
                $userID = $firstName = $lastName = $userName = $email = '';
            } else {
                $error = "Registration failed. Please try again later. Error: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Book Heaven</title>
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
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Top Announcement Bar -->
    <div class="bg-blue-800 text-white text-center py-2 px-4 text-sm">
        Free shipping on orders over RM100 | Member discounts available
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <!-- Top Header -->
            <div class="flex items-center justify-between py-4 border-b">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="MainPage01.php">
                        <img src="img/logo.png" alt="Book Heaven" width="200" height="150">
                    </a>
                    <h1 class="text-2xl font-bold text-blue-800 ml-4">Ready to join us? Welcome!</h1>
                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <a href="javascript:history.back()" class="flex items-center justify-center py-2 px-10 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fa-solid fa-backward mr-2"></i>
                        <span>Go Back</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Register Section -->
    <section class="py-16" style="background: url(img/sitebg.gif) center">
        <div class="container mx-auto px-4 max-w-lg">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-800 text-white py-4 px-6">
                    <h2 class="text-2xl font-bold">Create Account</h2>
                    <p class="text-blue-100">Join Book Heaven today</p>
                </div>
                
                <!-- Error and Success Messages -->
                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3">
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="p-6">
                    <div class="mb-4">
                        <label for="userID" class="block text-gray-700 font-medium mb-2">User ID</label>
                        <input type="text" id="userID" name="userID" value="<?php echo isset($userID) ? htmlspecialchars($userID) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="User ID" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="first_name" class="block text-gray-700 font-medium mb-2">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="John" required>
                        </div>
                        <div>
                            <label for="last_name" class="block text-gray-700 font-medium mb-2">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Doe" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="user_name" class="block text-gray-700 font-medium mb-2">Username</label>
                        <input type="text" id="user_name" name="user_name" value="<?php echo isset($userName) ? htmlspecialchars($userName) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Username" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="your@email.com" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                        <input type="password" id="password" name="password" class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="••••••••" required>
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Create Account
                    </button>
                    
                    <div class="mt-6 text-center">
                        <p class="text-gray-600">Already have an account? 
                            <a href="login.php" class="text-blue-600 hover:underline">Sign in</a>
                        </p>
                    </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm_password');
                
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmPassword.focus();
                }
            });
        });
    </script>
</body>
</html>