<?php
session_start();
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic


$companyId = $_SESSION['company_id'];

$sql = "SELECT * FROM managers WHERE company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $companyId);
$stmt->execute();
$result = $stmt->get_result();

$managers = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $managers[] = $row;
    }
}

if (!empty($managers)) {
    foreach ($managers as $manager) {
        echo '<option value="' . $manager['id'] . '">' . $manager['manager_name'] . '</option>';
    }
} else {
    echo '<option value="-1" disabled>No employees found</option>';
}


?>