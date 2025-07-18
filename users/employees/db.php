<?php

// Database configuration
$servername = "localhost";
$username = "simplrco_simplr";
$password = "3)TiskO2(0W8Iu";
$dbname = "simplrco_dashboard";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
