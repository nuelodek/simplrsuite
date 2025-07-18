<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if required POST variables are set
if (isset($_POST['fetch_tools']) && isset($_POST['employee_id']) && isset($_POST['company_id'])) {
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $company_id = $conn->real_escape_string($_POST['company_id']);

    // Fetch tools assigned to the selected employee
    $sql = "SELECT t.id, t.tool_name FROM tools t 
            INNER JOIN tools_assigned ta ON t.id = ta.tool_id 
            WHERE ta.employee_id = '$employee_id' AND ta.company_id = '$company_id'";

    $result = $conn->query($sql);
    $tools = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tools[] = $row;
        }
    }

    // Return JSON-encoded tools data
    header('Content-Type: application/json');
    echo json_encode($tools);
} else {
    // Handle error if required POST variables are not set
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Required parameters missing']);
}
?>
