<?php
// add_account.php

// Include database connection
require '../../sql_queries/db.php';
session_start();

header("Content-Type: application/json");

// generate a unique account_id
function generateUniqueAccountID() {

    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $account_id = '';
    for ($i = 0; $i < 10; $i++) {
        $account_id .='acc_'.  $chars[rand(0, strlen($chars) - 1)];
    }
    return $account_id;
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_id = $_SESSION['company_id'];
    $unique_id = generateUniqueAccountID();
    // Retrieve form data and sanitize
    $accountName = mysqli_real_escape_string($conn, $_POST['accountName']);
    $accountOwner = mysqli_real_escape_string($conn, $_POST['accountOwner']);
    $accountEmail = mysqli_real_escape_string($conn, $_POST['accountEmail']);
    $accountPhone = mysqli_real_escape_string($conn, $_POST['accountPhone']);

    // Prepare the SQL statement to insert a new account
    $sql = "INSERT INTO accounts (account_owner, customer_id, account_email, account_phone, company_id, unique_id) 
            VALUES (?, ?, ?, ?, ?, ?)";

    // Bind the parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $accountOwner, $accountEmail, $accountPhone, $company_id, $unique_id); 

    // Execute the statement
    $stmt->execute();

    // Check if the statement was successful
    if ($stmt->affected_rows > 0) {
        echo json_encode("Account added successfully");
    } else {
        echo json_encode("Error adding account");
    }

    // Close the statement  
    $stmt->close();
}
    
?>
