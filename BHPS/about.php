<?php
session_start();
include_once 'db_connect.php';

$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $logged_in ? $_SESSION['name'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Book Heaven</title>
    <link rel="icon" type="image/x-icon" href="/static/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/animejs/lib/anime.iife.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <img src="img/logo.png" alt="Book Heaven Logo" class="h-12 w-auto">
                </div>
                <div class="flex items-center space-x-8">
                    <a href="index.php" class="text-gray-600 hover:text-blue-600">Home</a>
                    <a href="about.php" class="text-blue-600 font-medium">About Us</a>
                    <a href="about.php" class="text-gray-600 hover:text-blue-600">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-16" style="background: url(img/sitebg.gif) center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6" data-aos="fade-up">
                About Book Heaven
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Your ultimate destination for literary treasures and reading inspiration
            </p>
        </div>
    </section>

    <!-- Story Section -->
    <section class="bg-blue-800 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right">
                    <h2 class="text-3xl font-bold text-white mb-6">Our Story</h2>
                    <p class="text-white mb-4">
                        Founded in 2025, Book Heaven started as a small independent bookstore with a passion for connecting readers with exceptional literature. It is a simple cozy corner shop that wishes to blossom into a beloved community hub for book lovers.
                    </p>
                    <p class="text-white mb-4">
                        Our journey is still new and fueled by a deep love for storytelling and a commitment to fostering a culture of reading. Over countless hurdles, we've grown our collection to include over many titles spanning all genres and age groups.
                    </p>
                    <p class="text-white">
                        Today, Book Heaven stands as a testament to the enduring power of books to inspire, educate, and transform lives. We continue to curate our collection with the same care and dedication that marked our humble beginnings.
                    </p>
                </div>
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
                    alt="Library" class="rounded-lg shadow-xl w-full">
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Mission</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    To cultivate a lifelong love of reading and provide access to diverse literary experiences
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-blue-50 rounded-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-blue-600 mb-4">
                        <i data-feather="book-open" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Curated Collections</h3>
                    <p class="text-gray-600">
                        Carefully selected books across all genres to suit every reader's taste and interest
                    </p>
                </div>
                <div class="text-center p-6 bg-indigo-50 rounded-lg" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-indigo-600 mb-4">
                        <i data-feather="users" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Community Building</h3>
                    <p class="text-gray-600">
                        Creating spaces that bring readers together to share their passion for literature
                    </p>
                </div>
                <div class="text-center p-6 bg-purple-50 rounded-lg" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-purple-600 mb-4">
                        <i data-feather="heart" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Literary Promotion</h3>
                    <p class="text-gray-600">
                        Supporting authors and promoting literacy through events, workshops, and partnerships
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Meet Our Team</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Passionate book lovers dedicated to serving our community
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                    <img src="img/hilmi.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Fakhrul Hilmi</h3>
                        <p class="text-blue-600 mb-2">Project Leader</p>
                        <p class="text-gray-600">
                            The one who manages the project and make sure everything is on track.
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                    <img src="img/chei.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Chei Kah Yan</h3>
                        <p class="text-blue-600 mb-2">Back-end Programmer</p>
                        <p class="text-gray-600">
                            The one who handles the server, database, and application logic.
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                    <img src="img/rifqah.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Nur Rifqah</h3>
                        <p class="text-blue-600 mb-2">Front-end Programmer</p>
                        <p class="text-gray-600">
                            The one who designs and implements the user interface and user experience.
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                    <img src="img/toh.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Toh Khim Tat</h3>
                        <p class="text-blue-600 mb-2">Trello Manager</p>
                        <p class="text-gray-600">
                            The one who manages the tasks and workflow using Trello.
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="500">
                    <img src="img/huda.jpeg" alt="Team Member" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Nur Alhuda</h3>
                        <p class="text-blue-600 mb-2">Document Leader</p>
                        <p class="text-gray-600">
                            The one who handles all the documentation and reports.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right">
                    <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80"
                        alt="Books" class="rounded-lg shadow-xl w-full">
                </div>
                <div data-aos="fade-left">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Core Values</h2>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i data-feather="check-circle" class="w-5 h-5 text-green-500"></i>
                            </div>
                            <p class="ml-3 text-gray-600"><span class="font-semibold">Literary Excellence:</span> We champion quality writing and diverse voices</p>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i data-feather="check-circle" class="w-5 h-5 text-green-500"></i>
                            </div>
                            <p class="ml-3 text-gray-600"><span class="font-semibold">Community Focus:</span> Building connections through shared reading experiences</p>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i data-feather="check-circle" class="w-5 h-5 text-green-500"></i>
                            </div>
                            <p class="ml-3 text-gray-600"><span class="font-semibold">Accessibility:</span> Making literature available to all readers regardless of background</p>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i data-feather="check-circle" class="w-5 h-5 text-green-500"></i>
                            </div>
                            <p class="ml-3 text-gray-600"><span class="font-semibold">Sustainability:</span> Supporting eco-friendly practices in publishing and retail</p>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i data-feather="check-circle" class="w-5 h-5 text-green-500"></i>
                            </div>
                            <p class="ml-3 text-gray-600"><span class="font-semibold">Innovation:</span> Embracing new technologies while honoring traditional book culture</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-1 px-10 bg-gradient-to-r from-blue-600 to-indigo-700 text-white padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <br>
            <h2 class="text-3xl font-bold mb-4" data-aos="fade-up">Get in Contact with us!</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Tell us something or give us any feedback, We will love it!
            </p>
        </div>

        <!-- ✅ Updated form -->
        <section class="py-6 bg-white border-t border-b rounded-lg shadow-md">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-black text-xl font-bold mb-4">Get in Touch</h3>
                            <form class="space-y-4" action="save_contact.php" method="POST">
                                <div>
                                    <label for="name" class="block text-gray-700 mb-1">Name</label>
                                    <input type="text" id="name" name="name" required class="text-black w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="email" class="block text-gray-700 mb-1">Email</label>
                                    <input type="email" id="email" name="email" required class="text-black w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="subject" class="block text-gray-700 mb-1">Subject</label>
                                    <input type="text" id="subject" name="subject" required class="text-black w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="message" class="block text-gray-700 mb-1">Message</label>
                                    <textarea id="message" name="message" rows="4" required class="text-black w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 w-full">Send Message</button>
                            </form>
                        </div>

                        <!-- Right side contact info -->
                        <div>
                            <h3 class="text-black text-xl font-bold mb-4">Contact Information</h3>
                            <div class="space-y-4">
                                <div class="text-black flex items-start">
                                    <i class="fas fa-map-marker-alt text-blue-600 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium">Address</h4>
                                        <p class="text-gray-600">123 Book Street, Kuala Lumpur, 50450 Malaysia</p>
                                    </div>
                                </div>
                                <div class="text-black flex items-start">
                                    <i class="fas fa-phone-alt text-blue-600 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium">Phone</h4>
                                        <p class="text-gray-600">+603-1234 5678</p>
                                    </div>
                                </div>
                                <div class="text-black flex items-start">
                                    <i class="fas fa-envelope text-blue-600 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium">Email</h4>
                                        <p class="text-gray-600">hello@bookheaven.com</p>
                                    </div>
                                </div>
                                <div class="text-black flex items-start">
                                    <i class="fas fa-clock text-blue-600 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium">Opening Hours</h4>
                                        <p class="text-gray-600">Monday - Friday: 9am - 6pm<br>Saturday: 10am - 4pm</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8">
                                <h4 class="text-black font-medium mb-2">Follow Us</h4>
                                <div class="flex space-x-4">
                                    <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-facebook-f text-xl"></i></a>
                                    <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-twitter text-xl"></i></a>
                                    <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-instagram text-xl"></i></a>
                                    <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fab fa-youtube text-xl"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <br>
    </section>

    <!-- Footer (unchanged) -->
    <footer class="bg-gray-900 text-white pt-12 pb-6">
        <!-- your existing footer content -->
    </footer>

    <script>
        AOS.init();
    </script>
    <script>
        feather.replace();
    </script>
</body>

</html>