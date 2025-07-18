<?php

session_start();

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include your database connection file
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the posted values
    $company_id = $_POST['company_id'];
    $department_name = $_POST['department_name'];
    $location = isset($_POST['location']) ? $_POST['location'] : null;

    // Check if the department already exists for the given company_id
    $check_sql = "SELECT * FROM departments WHERE company_id = ? AND department_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $company_id, $department_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "Department already exists for your company.";
    } else {
        // Prepare an SQL statement for insertion
        $insert_sql = "INSERT INTO departments (company_id, department_name, location) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iss", $company_id, $department_name, $location);

        // Execute the statement
        if ($insert_stmt->execute()) {
            echo "New department created successfully.";
        } else {
            echo "Error: " . $insert_stmt->error;
        }

        // Close the insert statement
        $insert_stmt->close();
    }

    // Close the check statement
    $check_stmt->close();
}

// Close the database connection
$conn->close();
?>
