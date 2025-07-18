<?php
include "../../sql_queries/db.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$contact_id = $data['contactId'];
$link_id = $data['linkId'];
$type = $data['type'];

// Determine the type of link
if ($type === "lead") {
    $stmt = $conn->prepare("UPDATE contacts SET lead_id = ? WHERE unique_id = ?");
} elseif ($type === "customer") {
    $stmt = $conn->prepare("UPDATE contacts SET customer_id = ? WHERE unique_id = ?");
}

if ($stmt) {
    $stmt->bind_param("ss", $link_id, $contact_id);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Contact successfully linked!"]);
    } else {
        echo json_encode(["message" => "Error linking contact: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["message" => "Error preparing statement: " . $conn->error]);
}

$conn->close();
?>
