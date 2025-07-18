<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
$companyId = $_SESSION['company_id']; // Assuming company ID is stored in session

include '../../sql_queries/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_widgets = $_POST['widgets'];

    // Remove all previously selected widgets for this company
    $deleteQuery = "DELETE FROM company_widgets WHERE company_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->execute([$companyId]);

    // Insert the newly selected widgets
    $insertQuery = "INSERT INTO company_widgets (company_id, widget_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);

    foreach ($selected_widgets as $widget_id) {
        $stmt->execute([$companyId, $widget_id]);
    }

    // Redirect to the dashboard or success page
    header('Location: newdashboard.php');
}

