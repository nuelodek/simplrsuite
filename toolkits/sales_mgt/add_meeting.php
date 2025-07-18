<?php
include '../../sql_queries/db.php';

session_start();

header('Content-Type: application/json');


// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyId = $_SESSION['company_id'];
    // Retrieve form data and sanitize inputs
    $meetingTitle = htmlspecialchars($_POST['meetingTitle']);
    $meetingDate = $_POST['meetingDate'];
    $meetingLocation = $_POST['meetingLocation'];
    $meetingTime = $_POST['meetingTime'];
    $meetingDetails = isset($_POST['meetingDetails']) ? htmlspecialchars($_POST['meetingDetails']) : null;

    // Optional fields based on location type
    $onlinePlatform = $meetingLocation === 'online' && isset($_POST['onlinePlatform']) ? $_POST['onlinePlatform'] : null;
    $meetingAddress = $meetingLocation === 'in-person' && isset($_POST['meetingAddress']) ? htmlspecialchars($_POST['meetingAddress']) : null;

    // SQL statement to insert data into the meetings table
    $sql = "INSERT INTO meetings (company_id, meeting_title, meeting_date, location, online_platform, meeting_address, meeting_time, details)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);

    // Bind parameters to avoid SQL injection
    $stmt ->bind_param("isssssss", $companyId, $meetingTitle, $meetingDate, $meetingLocation, $onlinePlatform, $meetingAddress, $meetingTime, $meetingDetails);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode( "Meeting added successfully!");
    } else {
        echo json_encode("Error: Could not add meeting.");
    }
} else {
    echo json_encode("Invalid request method.");
}
?>