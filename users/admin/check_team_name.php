
<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['team_name']) && isset($_GET['company_id'])) {
    $team_name = $_GET['team_name'];
    $company_id = $_GET['company_id'];

    // Prepare SQL statement to check if the team name exists for the given company
    $sql = "SELECT COUNT(*) as count FROM teams WHERE team_name = ? AND company_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $team_name, $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }

    $stmt->close();
} else {
    echo "invalid_request";
}

$conn->close();
?>
