<?php
// database_connection.php - this file should contain your DB connection
include '../../sql_queries/db.php';

header('Content-Type: application/json');

// Initialize an array to store metrics
$metrics = [];

// Query to get the total number of customers
$totalCustomersQuery = "SELECT COUNT(*) AS total FROM customers";
$totalCustomersResult = $conn->query($totalCustomersQuery);
$totalCustomersRow = $totalCustomersResult->fetch_assoc();
$metrics['totalCustomers'] = $totalCustomersRow['total'];

// Query to get the number of new customers in the last 30 days
$newCustomersQuery = "SELECT COUNT(*) AS new_customers FROM customers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$newCustomersResult = $conn->query($newCustomersQuery);
$newCustomersRow = $newCustomersResult->fetch_assoc();
$metrics['newCustomers'] = $newCustomersRow['new_customers'];

// Output the metrics as JSON
echo json_encode($metrics);
