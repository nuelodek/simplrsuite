<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create projects table if not exists
$sql = "CREATE TABLE IF NOT EXISTS projects (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  projectName VARCHAR(255) NOT NULL,
  projectDescription TEXT,
  startDate DATE,
  endDate DATE,
  department VARCHAR(50),
  status VARCHAR(50),
  projectManager INT(11),
  company_id INT(11),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
  echo "Error creating table: " . $conn->error;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $projectName = $_POST['projectName'];
  $projectDescription = $_POST['projectDescription'];
  $startDate = $_POST['startDate'];
  $endDate = $_POST['endDate'];
  $projectDepartment = $_POST['projectDepartment'];
  $projectStatus = $_POST['projectStatus'];
  $projectManager = $_POST['projectManager'];
  $companyId = $_POST['company_id'];

  // Prepare and bind
  $stmt = $conn->prepare("INSERT INTO projects (projectName, projectDescription, startDate, endDate, department, status, projectManager, company_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssssii", $projectName, $projectDescription, $startDate, $endDate, $projectDepartment, $projectStatus, $projectManager, $companyId);

  // Execute the statement
  if ($stmt->execute()) {
      echo "New project created successfully";
  } else {
      echo "Error: " . $stmt->error;
  }

  $stmt->close();

  // Handle file upload
  if(isset($_FILES['projectfile'])) {
      $uploadDir = 'uploads/';
      $projectId = $conn->insert_id;

      foreach($_FILES['projectfile']['tmp_name'] as $key => $tmp_name) {
          $file_name = $_FILES['projectfile']['name'][$key];
          $file_tmp = $_FILES['projectfile']['tmp_name'][$key];
          $file_path = $uploadDir . $file_name;

          if(move_uploaded_file($file_tmp, $file_path)) {
              // Insert file information into database
              $stmt = $conn->prepare("INSERT INTO project_files (project_id, file_name, file_path) VALUES (?, ?, ?)");
              $stmt->bind_param("iss", $projectId, $file_name, $file_path);
              $stmt->execute();
              $stmt->close();
          }
      }
  }
}

$conn->close();
?>