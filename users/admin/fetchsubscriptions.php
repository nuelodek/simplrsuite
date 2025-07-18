<?php

include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic
require '../session.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// $company_id = $_SESSION['company_id'];
$sql = "SELECT payroll_expireddate, recruiting_expireddate, crm_expireddate, 
               cybersecurity_expireddate, project_expireddate, strategy_expireddate, 
               risk_expireddate, sales_expireddate, product_expireddate, 
               office_expireddate 
        FROM toolkit_subscriptions 
        WHERE company_id = '$company_id'";
$result = $conn->query($sql);

$response = array();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_date = date("Y-m-d");

    $tools = ['payroll', 'recruiting', 'crm', 'cybersecurity', 'project', 'strategy', 'risk', 'sales', 'product', 'office'];

    foreach ($tools as $tool) {
        $expired_date = $row["{$tool}_expireddate"];
        if ($expired_date > $current_date) {
            $response[$tool] = false; // Tool is expired
        } else {
            $response[$tool] = true; // Tool is active
        }
    }
}

echo json_encode($response);

$conn->close();
?>