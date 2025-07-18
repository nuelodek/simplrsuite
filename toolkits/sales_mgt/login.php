<?php
session_start();
include '../../sql_queries/db.php'; // DB connection file

// Login function
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT e.password, e.employee_id, e.company_id, u.company_name 
                            FROM employees_auth e 
                            JOIN users u ON e.company_id = u.company_id 
                            WHERE e.email = ?");

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Start session with required data
            $_SESSION['companyName'] = $row['company_name'];
            $_SESSION['company_id'] = $row['company_id'];
            $_SESSION['unique_id'] = $row['employee_id'];

            echo "Login successful";
            // Redirect or load dashboard as required
            header("Location: index.php");
        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "Invalid username or password";
    }

    $stmt->close();
}
$conn->close();
?>
