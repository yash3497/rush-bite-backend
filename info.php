<?php
// Display PHP Version
echo "<h2>PHP Version: " . phpversion() . "</h2>";

// Database Configuration
$hostname = "localhost";  // Usually 'localhost' for local servers
$username = "yash";       // Default username for XAMPP
$password = "Rush@yash12";           
$database = "rushbite";    // Replace with your database name

// Create Database Connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die("<p style='color:red;'>Database Connection Failed: " . $conn->connect_error . "</p>");
} else {
    echo "<p style='color:green;'>Successfully Connected to Database: <strong>$database</strong></p>";
}

// Close Connection
$conn->close();
?>
