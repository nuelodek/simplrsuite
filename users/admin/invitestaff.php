<?php
session_start();
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic
require '../../PHPMailer-master/src/PHPMailer.php';
require '../../PHPMailer-master/src/Exception.php';
require '../../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$simplrcompany = "Simplr Business Suite";
$mailpassword = "3)TiskO2(0W8Iu";
$companyusername = 'auth@simplr.com.ng';

// Function to generate a unique ID
function generateUniqueId() {
    // Define the prefix
    $prefix = "emp_";
    
    // Generate a random 5-character alphanumeric string
    $randomString = bin2hex(random_bytes(5));
    
    // Combine the prefix and random string
    $uniqueId = $prefix . $randomString;
    
    return $uniqueId;
    
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the posted values
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $hire_date = $_POST['hire_date'];
    $job_title = $_POST['job_title'];
    $department_id = $_POST['department_id'];
    $manager_id = $_POST['manager_id'];
    $company_id = $_POST['company_id'];
    $role_name = $_POST['role_name'];
    $description = $_POST['description'];

    // Check if the employee email already exists
    $check_sql = "SELECT * FROM employees WHERE company_id = ? AND email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $company_id, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "Employee with this email already exists for your company.";
    } else {
        // Insert the role name and description into the roles table
        $insert_role_sql = "INSERT INTO roles (role_name, description, company_id) VALUES (?, ?, ?)";
        $insert_role_stmt = $conn->prepare($insert_role_sql);
        $insert_role_stmt->bind_param("ssi", $role_name, $description, $company_id);

        if ($insert_role_stmt->execute()) {
            // Get the last inserted role's ID
            $role_id = $conn->insert_id;

            // Generate a unique ID for the new employee
            $unique_id = generateUniqueID();

            // Insert the new employee
            $insert_employee_sql = "INSERT INTO employees (company_id, first_name, last_name, email, phone, hire_date, job_title, department_id, manager_id, role_id, unique_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_employee_stmt = $conn->prepare($insert_employee_sql);
            $insert_employee_stmt->bind_param("issssssiiis", $company_id, $first_name, $last_name, $email, $phone, $hire_date, $job_title, $department_id, $manager_id, $role_id, $unique_id);

            if ($insert_employee_stmt->execute()) {
                // Generate a random password for the employee
                $password = bin2hex(random_bytes(4)); // 8-character random password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into employees_auth table
                $insert_auth_sql = "INSERT INTO employees_auth (company_id, email, password) VALUES (?, ?, ?)";
                $insert_auth_stmt = $conn->prepare($insert_auth_sql);
                $insert_auth_stmt->bind_param("iss", $company_id, $email, $hashed_password);

                if ($insert_auth_stmt->execute()) {
                    echo "New employee created successfully.";

                    // Send notification email
                    function sendNotificationEmail($first_name, $last_name, $company_name, $email, $password) {
                        global $companyusername, $mailpassword, $simplrcompany;

                        $mail = new PHPMailer(true);

                        try {
                            // Server settings
                            $mail->isSMTP();
                            $mail->Host       = 'simplr.com.ng'; // Replace with your SMTP server
                            $mail->SMTPAuth   = true;
                            $mail->Username   = $companyusername; // Replace with your email
                            $mail->Password   = $mailpassword; // Replace with your email password
                            $mail->SMTPSecure = 'ssl';
                            $mail->Port       = 465;

                            // Recipients
                            $mail->setFrom($companyusername, $simplrcompany);
                            $mail->addAddress($email, $first_name . ' ' . $last_name);

                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = 'Employee Account Invitation';
                            $mail->Body    = "
                            <html>
                            <head>
                                <style>
                                    body { font-family: Arial, sans-serif; }
                                    .container { width: 600px; margin: 0 auto; }
                                    .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
                                    .content { padding: 20px; }
                                    .footer { background-color: #f4f4f4; padding: 10px; text-align: center; }
                                </style>
                            </head>
                            <body>
                                <div class='container'>
                                    <div class='header'>
                                        <h1>Welcome to Simplr!</h1>
                                    </div>
                                    <div class='content'>
                                        <p>Dear $first_name $last_name,</p>
                                        <p>$company_name has invited you to become an employee. Here are your login credentials:</p>
                                        <ul>
                                            <li>Email: $email</li>
                                            <li>Password: $password</li>
                                            <li>Login URL: <a href='https://simplr.com.ng/employees/login'>simplr.com.ng/employees/login</a></li>
                                        </ul>
                                        <p>Please change your password upon first login.</p>
                                        <p>If you have any questions, please don't hesitate to contact us.</p>
                                    </div>
                                    <div class='footer'>
                                        <p>© " . date("Y") . " $simplrcompany. All rights reserved.</p>
                                    </div>
                                </div>
                            </body>
                            </html>
                            ";

                            $mail->send();
                            return "Notification email sent successfully.";
                        } catch (Exception $e) {
                            return "Failed to send notification email. Error: {$mail->ErrorInfo}";
                        }
                    }

                    // Call the function to send the email
                    $emailResult = sendNotificationEmail($first_name, $last_name, $_SESSION['company_name'], $email, $password);
                    echo $emailResult;
                } else {
                    echo "Error: " . $insert_auth_stmt->error;
                }

                // Close the employee auth insert statement
                $insert_auth_stmt->close();
            } else {
                echo "Error: " . $insert_employee_stmt->error;
            }

            // Close the employee insert statement
            $insert_employee_stmt->close();
        } else {
            echo "Error: " . $insert_role_stmt->error;
        }

        // Close the role insert statement
        $insert_role_stmt->close();
    }

    // Close the check statement
    $check_stmt->close();
}

// Close the database connection
$conn->close();
?>
