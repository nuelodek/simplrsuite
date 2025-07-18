<?php
session_start();
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic


$companyId = $_SESSION['company_id'];

$sql = "SELECT * FROM departments WHERE company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $companyId);
$stmt->execute();
$result = $stmt->get_result();

$departments = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

if (!empty($departments)) {
    foreach ($departments as $department) {
        echo '<option value="' . $department['id'] . '">' . $department['department_name'] . '</option>';
    }
} else {
    echo '<option value="-1" disabled>No departments found</option>';
}


?>
Full texts
id	
