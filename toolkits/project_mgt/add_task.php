<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
include '../../sql_queries/db.php';

// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['company_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access. Please log in."]);
    exit();
}

// Capture the POST data
$projectId = $_POST['project_id'];
$title = $_POST['title'];
$assignee = $_POST['assignee'];
$due_date = $_POST['due_date'];
$priority = $_POST['priority'];

// Insert the new task into the database
$sql = "INSERT INTO tasks (project_id, title, assignee, due_date, priority) 
        VALUES ('$projectId', '$title', '$assignee', '$due_date', '$priority')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Task added successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error adding task: " . $conn->error]);
}

// Close the database connection
$conn->close();
?>
