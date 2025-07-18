<?php
header('Content-Type: application/json');
include '../../sql_queries/db.php';

// Retrieve data from the POST request
$data = json_decode(file_get_contents("php://input"), true);
$taskId = $data['taskId'] ?? null;
$newAssigneeId = $data['newAssigneeId'] ?? null;

if (!$taskId || !$newAssigneeId) {
    echo json_encode(["status" => "error", "message" => "Invalid data."]);
    exit();
}

// Update the assignee in the database
$sql = "UPDATE tasks SET assignee = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newAssigneeId, $taskId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Assignee updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update assignee."]);
}

$stmt->close();
$conn->close();
?>
