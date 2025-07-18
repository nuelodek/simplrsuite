<?php
header('Content-Type: application/json');
include '../../sql_queries/db.php';

// Retrieve data from the POST request
$data = json_decode(file_get_contents("php://input"), true);
$taskId = $data['taskId'] ?? null;
$newStatus = $data['newStatus'] ?? null;

if (!$taskId || !$newStatus) {
    echo json_encode(["status" => "error", "message" => "Invalid data."]);
    exit();
}

// Update the status in the database
$sql = "UPDATE tasks SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newStatus, $taskId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Status updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update status."]);
}

$stmt->close();
$conn->close();
?>
