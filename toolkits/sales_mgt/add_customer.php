<?php
include '../../sql_queries/db.php';

session_start();
header("Content-Type: application/json");

// function to generate a unique customer_id
function generateCustomerId(){
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $customer_Id = '';
    for($i = 0; $i < 10; $i++ ){
      $customer_Id .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $customer_Id;
}

$customerId = generateCustomerId();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyId = $_SESSION['company_id'];
    // Retrieve form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $businessName = $_POST['businessName'];
    $acquiredItem = $_POST['acquiredItem'];
    $customerEmail = $_POST['customerEmail'];
    $customerPhone = $_POST['customerPhone'];
    $customerAddress = $_POST['customerAddress'];
}

// Prepare and execute the SQL query
$stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, business_name, acquired_item, customer_email, phone_number, address, customer_id, company_id) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->bind_param("sssssssss", $firstName, $lastName, $businessName, $acquiredItem, $customerEmail, $customerPhone, $customerAddress, $customerId, $companyId);
$stmt->execute();
// Check if the query was successful
if ($stmt->affected_rows > 0) {
    echo json_encode("Customer added successfully");
} else {
    echo json_encode( "Error adding customer");
}
?>