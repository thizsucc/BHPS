<?php
session_start();
include_once 'db_connect.php';

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data safely
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>
                alert('Please fill in all fields before submitting.');
                window.history.back();
              </script>";
        exit;
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO contact (name, email, subject, message) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            echo "<script>
                    alert('Thank you for contacting us! Your message has been sent successfully.');
                    window.location.href = 'about.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error saving your message. Please try again.');
                    window.history.back();
                  </script>";
        }
        $stmt->close();
    } else {
        echo "<script>
                alert('Database error: unable to prepare statement.');
                window.history.back();
              </script>";
    }
} else {
    // Redirect back if accessed directly
    header("Location: about.php");
    exit;
}
