<?php
session_start();

// Include the database connection file
include 'db.php';

// Retrieve the data sent via POST and sanitize it
$recruiter_name = mysqli_real_escape_string($conn, $_POST['recruiter_name']);
$talent_name = mysqli_real_escape_string($conn, $_POST['talent_name']);
$talent_email = mysqli_real_escape_string($conn, $_POST['talent_email']);
$recruiter_email = mysqli_real_escape_string($conn, $_POST['recruiter_email']);
$created_at = mysqli_real_escape_string($conn, $_POST['created_at']);
$recruiterUniqueId = mysqli_real_escape_string($conn, $_POST['recruiter_unique_id']);
$talentUniqueId = mysqli_real_escape_string($conn, $_POST['talent_unique_id']);

// Sanitize the created_at field for use in the URL
$created_at_sanitized = urlencode($created_at);

// Create the chat URL
$chatUrl = "chatus.php?unique_id1=" . $recruiterUniqueId . "&unique_id2=" . $talentUniqueId . "&created_at=" . $created_at_sanitized;

// Sanitize the URL before inserting into the database
$chatUrlToInsert = mysqli_real_escape_string($conn, $chatUrl);

// Insert the chat URL into the database
$sqlInsertChatUrl = "INSERT INTO talentchat (chat_url, recruiter_name, talent_name, talent_email, recruiter_email)
                    VALUES ('$chatUrlToInsert', '$recruiter_name', '$talent_name', '$talent_email', '$recruiter_email')
                    ON DUPLICATE KEY UPDATE
                    recruiter_name = VALUES(recruiter_name),
                    talent_name = VALUES(talent_name),
                    talent_email = VALUES(talent_email),
                    recruiter_email = VALUES(recruiter_email)";

// Execute the SQL query
if ($conn->query($sqlInsertChatUrl) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Chat URL inserted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
}

?>
