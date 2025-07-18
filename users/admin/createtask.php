<?php

include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// SQL to create table if not exists
$createTableSQL = "CREATE TABLE IF NOT EXISTS tasks (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  project_id VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  due_date DATE NOT NULL,
  priority ENUM('low', 'medium', 'high') NOT NULL,
  assignee VARCHAR(255) NOT NULL,
  company_id INT(11) NOT NULL,
  file_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($createTableSQL) === FALSE) {
  die("Error creating table: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
// Retrieve form data
$project_id = $_POST["project_id"];
$taskTitle = $_POST["taskTitle"];
$taskDescription = $_POST["taskDescription"];
$dueDate = $_POST["dueDate"];
$priority = $_POST["priority"];
$assignee = $_POST["assignee"];
$company_id = $_POST["company_id"];

// Handle file upload
$uploadFile = "";
if (isset($_FILES["taskfile"]) && $_FILES["taskfile"]["error"] == UPLOAD_ERR_OK) {
    $uploadDir = "uploads/";
    $uploadFile = $uploadDir . basename($_FILES["taskfile"]["name"]);
    if (!move_uploaded_file($_FILES["taskfile"]["tmp_name"], $uploadFile)) {
        echo "Error uploading file: " . $_FILES["taskfile"]["error"];
    }
}

  
$sql = "INSERT INTO tasks (project_id, title, description, due_date, priority, assignee, company_id, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssss", $project_id, $taskTitle, $taskDescription, $dueDate, $priority, $assignee, $company_id, $uploadFile);
if (!$stmt->execute()) {
    echo "Error executing SQL: " . $stmt->error;
} else {
    // Send a response back to the client
    echo "Task created successfully";
}
} else {
echo "Invalid request method";
}
