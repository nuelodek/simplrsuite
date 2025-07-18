<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic
$taskId = isset($_GET['id']) ? $_GET['id'] : null;

// Check if task ID is provided
if ($taskId) {
    // Prepare the delete statement
    $sql_delete = "DELETE FROM tasks WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $taskId);

    if ($stmt_delete->execute()) {
        echo "Task deleted successfully!";
        header("Location: view_tasks.php"); // Redirect back to the task list
        exit();
    } else {
        echo "Error deleting task: " . $conn->error;
    }
} else {
    die("Invalid task ID.");
}

$conn->close();
?>
