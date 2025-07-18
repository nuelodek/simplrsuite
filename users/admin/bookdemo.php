<?php
// Database connection details
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Create connection

// Check connection
if ($conn->connect_error) {
             die("Connection failed: " . $conn->connect_error);
}

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS bookdemo (
             id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
             name VARCHAR(100) NOT NULL,
             email VARCHAR(100) NOT NULL,
             company VARCHAR(100),
             phone VARCHAR(20),
             demo_date DATE,
             demo_time TIME,
             message TEXT,
             status VARCHAR(20) DEFAULT 'Pending',
             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
             updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             preferred_contact_method ENUM('email', 'phone') DEFAULT 'email',
             industry VARCHAR(100),
             number_of_employees INT(6),
             heard_from VARCHAR(100)
)";

if ($conn->query($sql) === FALSE) {
             echo "Error creating table: " . $conn->error;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
             $name = $_POST['name'];
             $email = $_POST['email'];
             $company = $_POST['company'];
             $phone = $_POST['phone'];
             $date = $_POST['date'];
             $time = $_POST['time'];
             $message = $_POST['message'];
             $status = 'Pending';
             $preferred_contact_method = $_POST['preferred_contact_method'];
             $industry = $_POST['industry'];
             $number_of_employees = $_POST['number_of_employees'];
             $heard_from = $_POST['heard_from'];

             $sql = "INSERT INTO bookdemo (name, email, company, phone, demo_date, demo_time, message, status, preferred_contact_method, industry, number_of_employees, heard_from)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
             $stmt = $conn->prepare($sql);
             $stmt->bind_param("ssssssssssss", $name, $email, $company, $phone, $date, $time, $message, $status, $preferred_contact_method, $industry, $number_of_employees, $heard_from);
    
             if ($stmt->execute()) {
                 echo "Demo request submitted successfully";
             } else {
                 echo "Error: " . $stmt->error;
             }

             $stmt->close();
}

$conn->close();
?>

            