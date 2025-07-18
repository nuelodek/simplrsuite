<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check for company_id in the session
if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
} else {
    // Handle the case where company_id is not set
    echo "Company ID not set.";
    exit;
}

// Generate unique random ID for the employee
function generateRandomId($length = 16) {
    // Define the character set
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%<>.,/?:;\'"{}()_*&^';
    
    // Get the total number of characters in the character set
    $charactersLength = strlen($characters);
    
    // Initialize an empty string for the ID
    $randomId = '';
    
    // Loop to generate a string of the specified length
    for ($i = 0; $i < $length; $i++) {
        $randomId .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomId;
}

// Handle form submission
if (isset($_POST['submit'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $hire_date = $_POST['hire_date'];
    $job_title = $_POST['job_title'];
    $department_id = $_POST['department_id'];
    $manager_id = $_POST['manager_id'];
    $role_id = $_POST['role_id'];
    // $address = $_POST['address'];
    // $city = $_POST['city'];
    // $state = $_POST['state'];
    // $zip = $_POST['zip'];

    // Generate a random ID for the new employee
    $employee_id = generateRandomId(16);

    // SQL query to insert the employee into the database
    $query = "INSERT INTO employees (id, company_id, first_name, last_name, email, phone, hire_date, job_title, department_id, manager_id, role_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$employee_id, $company_id, $first_name, $last_name, $email, $phone, $hire_date, $job_title, $department_id, $manager_id, $role_id]);

    // Check if the employee was added successfully
    if ($stmt) {
        echo "Employee added successfully with ID: $employee_id";
    } else {
        echo "Error adding employee.";
    }
}
?>


