<?php
// get_alerts.php
header('Content-Type: application/json');

// Database connection
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic



// Check the connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to fetch alerts for system-wide or company-specific alerts
$sql = "SELECT * FROM alerts WHERE company_id IS NULL OR company_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}

// Assuming you have a way to get the company_id, replace 1 with the actual company_id
$stmt->bind_param("i", $companyId);

$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die(json_encode(['error' => 'Query failed: ' . $stmt->error]));
}

// Fetch results and return as JSON
$alerts = [];
while ($row = $result->fetch_assoc()) {
    $alerts[] = $row;
}

echo json_encode($alerts);

$stmt->close();
$conn->close();
?>
