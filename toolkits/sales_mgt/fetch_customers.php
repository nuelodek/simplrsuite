<?php
include "../../sql_queries/db.php";
session_start();

$company_id = $_SESSION['company_id'];
// Total Customers Over Time
$totalCustomersOverTimeQuery = "SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count 
                                FROM customers 
                                WHERE company_id = '$company_id' 
                                GROUP BY YEAR(created_at), MONTH(created_at) 
                                ORDER BY year, month";
$totalCustomersOverTimeResult = $conn->query($totalCustomersOverTimeQuery);
if ($totalCustomersOverTimeResult === false) {
    die('Query failed: ' . htmlspecialchars($conn->error));
}

$totalCustomersData = [];
while ($row = $totalCustomersOverTimeResult->fetch_assoc()) {
    $totalCustomersData[] = $row;
}

header('Content-Type: application/json');
echo json_encode($totalCustomersData);
?>