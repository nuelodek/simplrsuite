<?php
// Include database configuration
require_once 'db.php';

// Function to sanitize inputs
function sanitize($conn, $input) {
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($input)));
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve form data and sanitize
    $issueType = sanitize($conn, $_POST['issue-type']);
    $query = sanitize($conn, $_POST['query']);
    
    // File upload handling
    $attachment = $_FILES['attachment'];
    $fileName = sanitize($conn, $attachment['name']);
    $tempFilePath = $attachment['tmp_name'];
    
    // Move uploaded file to desired directory
    $uploadsDirectory = 'uploads/'; // Directory where uploads will be stored
    $uploadPath = $uploadsDirectory . $fileName;
    if (move_uploaded_file($tempFilePath, $uploadPath)) {
        
        // Set initial resolve status as 'pending'
        $resolveStatus = 'pending';
        
        // SQL query to insert data into database
        $sql = "INSERT INTO support_requests (issue_type, query_text, attachment_path, resolve_status) 
                VALUES ('$issueType', '$query', '$uploadPath', '$resolveStatus')";
        
        if ($conn->query($sql) === TRUE) {
            // Output JavaScript for redirection with a delay
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="refresh" content="3;url=dashboard.php">
                <title>Redirecting...</title>
            </head>
            <body>
                <h2>Your support request has been successfully submitted!</h2>
                <p>Redirecting to dashboard in 3 seconds...</p>
            </body>
            </html>';
            exit; // Make sure no further output is sent
        } else {
            // Display error message if query execution fails
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        
    } else {
        // File upload failed
        echo "Error uploading file.";
    }
    
} else {
    // Redirect or handle errors if form is not submitted properly
    echo "Form submission error!";
}

// Close database connection
$conn->close();
?>
