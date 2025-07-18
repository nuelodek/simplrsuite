<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the input from POST request
$taskId = $_POST['taskId'];
$title = $_POST['updateTitle'];
$description = $_POST['updateDescription'];
$dueDate = $_POST['updateDueDate'];
$priority = $_POST['updatePriority'];
$assignee = $_POST['updateAssignee'];

// Update task query
$sql_update = "UPDATE tasks 
                SET title = ?, description = ?, due_date = ?, priority = ?, assignee = ?
                WHERE id = ?";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("sssssi", $title, $description, $dueDate, $priority, $assignee, $taskId);

if ($stmt->execute()) {
    // Task updated successfully
    echo json_encode([
        'success' => true,
        'message' => 'Task updated successfully'
    ]);
} else {
    // Error updating task
    echo json_encode([
        'success' => false,
        'message' => 'Error updating task: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
