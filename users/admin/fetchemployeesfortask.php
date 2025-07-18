<?php

session_start();
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic


$companyId = $_SESSION['company_id'];

$sql = "SELECT * FROM employees WHERE company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $companyId);
$stmt->execute();
$result = $stmt->get_result();

$employees = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

if (!empty($employees)) {
    foreach ($employees as $employee) {
        echo '<option value="' . $employee['id'] . '">' . $employee['first_name'] . ' ' . $employee['last_name'] . '</option>';
    }
} else {
    echo '<option value="-1" disabled>No employees found</option>';
}

// $stmt->close();
// $conn->close();

?>