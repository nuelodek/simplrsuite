<?php
// Include DB connection
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Proceed with the query if the token is valid
$sql = "SELECT * FROM employees";
$result = $conn->query($sql);

$employees = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($employees);

?>
