


<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Database configuration
$hostname = '16.171.153.65';  // Replace with your DB host (e.g., localhost, IP, or domain)
$username = 'yash';        // Replace with your DB username
$password = 'Rush@yash12';        // Replace with your DB password
$database = 'rushbite';      // Replace with your DB name

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully!";

// Close the connection
$conn->close();
?>
