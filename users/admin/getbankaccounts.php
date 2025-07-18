<?php
// Database connection
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if ($conn->connect_error) {
      die('Connection failed: ' . $conn->connect_error);
  }

  // Prepare the SQL statement to fetch all data from the bankaccounts table
  $sql = "SELECT * FROM bankaccounts";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
      while($row = $result->fetch_assoc()) {
          echo "<div style='border: 1px solid #ccc; border-radius: 8px; padding: 15px; width: 200px;'>";
          echo "<h3>Account Details</h3>";
          echo "<p><strong>ID:</strong> " . $row['id'] . "</p>";
          echo "<p><strong>Account Number:</strong> " . $row['account_number'] . "</p>";
          echo "<p><strong>Balance:</strong> $" . number_format($row['balance'], 2) . "</p>";
          echo "</div>";
      }
      echo "</div>";
  } else {
      echo "No records found";
  }

  // Close the database connection
  $conn->close();
} else {
  http_response_code(405);
  echo "Method Not Allowed";
}
?>