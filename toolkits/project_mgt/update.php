<?php
include '../../sql_queries/db.php';
// Function to generate a unique project ID
function generateProjectUniqueId() {
    return 'proj_' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
}

// Set the project ID to update
$projectId = 3; // Update with the specific project ID you want to modify

// Establish a connection to the database
// $conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate a unique ID
$uniqueId = generateProjectUniqueId();

// Prepare and execute the update query
$sql = "UPDATE projects SET unique_id = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("si", $uniqueId, $projectId);
    if ($stmt->execute()) {
        echo "Project unique ID updated successfully.";
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
