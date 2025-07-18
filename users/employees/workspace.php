<?php
ob_start();

session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

// Check if company_id is set in the session
if (!isset($_SESSION['employee_id'])) {
    // Redirect to login.html if company_id is not found
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; 
$employee_id = $_SESSION['employee_id'];
$email = $_SESSION['email'];
$company_id = $_SESSION['company_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$department_id = $_SESSION['department_id'];
$manager_id = $_SESSION['manager_id'];
$role_id = $_SESSION['role_id'];
$phone = $_SESSION['phone'];
$hire_date = $_SESSION['hire_date'];
$job_title = $_SESSION['job_title'];
$created_at = $_SESSION['created_at'];
$updated_at  = $_SESSION['created_at'];
$tool_id = isset($_SESSION['tool_id']) ? $_SESSION['tool_id'] : null;
$assigned_at = $_SESSION['assigned_at']; 
$assigned_by = $_SESSION['assigned_by'];
$assignment_notes = $_SESSION['assignment_notes'];

$company_name = "";
$tool_name = "";

// Database connection

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute the query
$stmt = $conn->prepare("SELECT company_name FROM users WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the result
if ($row = $result->fetch_assoc()) {
    $company_name = $row['company_name'];
}

// Prepare and execute the query
$stmt_tools = $conn->prepare("SELECT tool_name FROM tools WHERE id = ?");
$stmt_tools->bind_param("i", $tool_id);
$stmt_tools->execute();
$result_tools = $stmt_tools->get_result();

// Fetch the result
if ($row = $result_tools->fetch_assoc()) {
    $tool_name = $row['tool_name'];
}

// Close the statement and connection
$stmt->close();
$stmt_tools->close();
$conn->close();

?>
            <?php 
  if ($tool_id == "1") {
      // Code for Payroll & Accounting tool
      include "payroll_accounting.php";
  } elseif ($tool_id == "2") {
      // Code for Recruiting & Human Resources tool
      include "recruiting_hr.php";
  } elseif ($tool_id == "3") {
      // Code for Customer Relationship Management tool
      include "crm.php";
  } elseif ($tool_id == "4") {
      // Code for Cybersecurity Console tool
      include "cybersecurity.php";
  } elseif ($tool_id == "5") {
      // Code for Project Management tool
      include "project_management.php";
  } elseif ($tool_id == "6") {
      // Code for Business Strategy tool
      include "business_strategy.php";
  } elseif ($tool_id == "7") {
      // Code for Risk Management & Compliance tool
      include "risk_compliance.php";
  } elseif ($tool_id == "8") {
      // Code for Sales & Marketing tool
      include "sales_marketing.php";
  } elseif ($tool_id == "9") {
      // Code for Product Development tool
      include "product_development.php";
  } elseif ($tool_id == "10") {
      // Code for Office Management & Tasking tool
      include "office_management.php";
  } else {
      // Code for default or unknown tool
      echo "You haven't been assigned a tool, check back later";
  }

ob_end_flush();
?>
