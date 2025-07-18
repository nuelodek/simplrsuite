<?php
include '../../sql_queries/db.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = $_POST['lead_id'];
    $new_status = $_POST['new_status'];

    // Update the lead's status in the database
    $stmt = $conn->prepare("UPDATE leads SET status = ? WHERE lead_id = ? AND company_id = ?");
    $stmt->bind_param("sii", $new_status, $lead_id, $_SESSION['company_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Status update failed']);
    }

    $stmt->close();
    $conn->close();
}
?>
