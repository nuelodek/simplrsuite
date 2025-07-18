<?php
include "../../sql_queries/db.php";
session_start();

$company_id = $_SESSION['company_id'];
$sql = "SELECT unique_id AS id, lead_name AS name FROM leads WHERE company_id = '$company_id'";
$result = $conn->query($sql);

$leads = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leads[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($leads);
?>
