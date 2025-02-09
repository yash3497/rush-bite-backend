<?php
$conn = new mysqli('localhost', 'root', '', 'restro');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!";
}

$conn->close();
?>
