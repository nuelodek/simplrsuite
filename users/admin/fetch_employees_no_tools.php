<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

$sql = "SELECT e.* FROM employees e
        LEFT JOIN tools_assigned ta ON e.employee_id = ta.employee_id
        WHERE ta.employee_id IS NULL";
$result = $conn->query($sql);

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
    echo '<option value="-1">No employees found</option>';
}

// $conn->close();

?>