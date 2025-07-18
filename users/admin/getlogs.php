<?php

// get_alerts.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();

// Database connection
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Check the connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to fetch logs entries
$sql = "SELECT id, type, user_id, action, details, company_id, created_at FROM logs WHERE company_id IS NULL OR company_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}

// Get the company_id from the session or set a default value
$companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;

$stmt->bind_param("i", $companyId);

$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die(json_encode(['error' => 'Query failed: ' . $stmt->error]));
}

// Fetch results and return as JSON
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

echo json_encode($logs);

$stmt->close();
$conn->close();
?>
