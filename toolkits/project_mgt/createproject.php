<?php
include '../../sql_queries/db.php';

session_start();

/**
 * Generates a unique project ID
 *
 * The ID is in the format of "proj_XXXXX", where XXXXX is a random number
 * between 0 and 99999, padded with zeros to 5 digits
 *
 * @return string Unique project ID
 */
function generateProjectUniqueId() {
  return 'proj_' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
}

$uniqueId = generateProjectUniqueId();
$companyId = $_SESSION['company_id'];

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $project_name = $_POST['project_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $project_manager_id = $_POST['project_manager'];

    // Adjusted SQL query and bind statement
    $sql = "INSERT INTO projects (projectName, startDate, endDate, status, project_manager_id, company_id, unique_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $project_name, $start_date, $end_date, $status, $project_manager_id, $companyId, $uniqueId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Project added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add project."]);
    }

    $stmt->close();
    $conn->close();
}
?>
