<?php
include 'db_connect.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST["userID"]);
    $password = trim($_POST["password"]);

    // admin table
    if (strpos($user_id, 'admin') === 0) {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE AdminID = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['Password']) || $password === $user['Password']) {
                $_SESSION["userID"] = $user["AdminID"];
                $_SESSION["name"] = $user["AdminName"];
                $_SESSION["jawatan"] = $user["Jawatan"];
                $_SESSION["user_type"] = "admin";
                $_SESSION["loggedin"] = true;

                if ($password === $user['Password']) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE admin SET Password = ? WHERE AdminID = ?");
                    $updateStmt->bind_param("ss", $hashedPassword, $user_id);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                header("Location: admin.php");
                exit();
            } else {
                echo "<script>alert('Wrong password!'); window.location='login.php';</script>";
            }
        } else {
            echo "<script>alert('Account Admin does not exist!'); window.location='login.php';</script>";
        }
    } 
    // staff table
    elseif (strpos($user_id, 'staff') === 0) {
        $stmt = $conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['Password']) || $password === $user['Password']) {
                $_SESSION["userID"] = $user["StaffID"];
                $_SESSION["name"] = $user["Staff_Name"];  // Fixed: Changed Staff_name to Staff_Name
                $_SESSION["jawatan"] = $user["Jawatan"];
                $_SESSION["user_type"] = "staff";
                $_SESSION["loggedin"] = true;

                if ($password === $user['Password']) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE staff SET Password = ? WHERE StaffID = ?");
                    $updateStmt->bind_param("ss", $hashedPassword, $user_id);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                header("Location: staff.php");
                exit();
            } else {
                echo "<script>alert('Wrong password!'); window.location='login.php';</script>";
            }
        } else {
            echo "<script>alert('Account staff does not exist!'); window.location='login.php';</script>";
        }
    }
    // user table
    else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE User_ID = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['Password']) || $password === $user['Password']) {
                $_SESSION["userID"] = $user["User_ID"]; 
                $_SESSION["name"] = $user["user_Name"];
                $_SESSION["user_type"] = "user";
                $_SESSION["loggedin"] = true;
                
                if ($password === $user['Password']) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE user SET Password = ? WHERE User_ID = ?");
                    $updateStmt->bind_param("ss", $hashedPassword, $user_id);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                header("Location: index.php");
                exit();
            } else {
                echo "<script>alert('Wrong Password!'); window.location='login.php';</script>";
            }
        } else {
            echo "<script>alert('Account not exist!'); window.location='login.php';</script>";
        }
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Book Heaven</title>
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
        Free shipping on orders over RM200
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
                    <h1 class="text-2xl font-bold text-blue-800 ml-4">Ready to read again? Nice :)</h1>
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

    <!-- Login Section -->
    <section class="py-16" style="background: url(img/sitebg.gif) ;">
        <div class="container mx-auto px-4 max-w-lg">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-800 text-white py-4 px-6">
                    <h2 class="text-2xl font-bold">Welcome Back</h2>
                    <p class="text-blue-100">Sign in to your Book Heaven account</p>
                </div>
                
                <form method="POST" action="" class="p-6">
                    <div class="mb-4">
                        <label for="userID" class="block text-gray-700 font-medium mb-2">User ID</label>
                        <input type="text" id="userID" name="userID" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="user ID" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                        <input type="password" id="password" name="password" class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="••••••••" required>
                        <div class="flex justify-end mt-2">
                            <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Sign in
                    </button>
                    
                    <div class="mt-6 text-center">
                        <p class="text-gray-600">Don't have an account? 
                            <a href="register.php" class="text-blue-600 hover:underline">Create one</a>
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
            
        });
    </script>
</body>
</html>