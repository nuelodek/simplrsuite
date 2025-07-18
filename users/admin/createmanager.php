<?php
// session_start();
include '../session.php';
include '../../sql_queries/db.php'; // Make sure this file correctly sets up the $conn variable

// require './PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/SMTP.php';

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



$simplrcompany = "Simplr Business Suite";
$mailpassword = "3)TiskO2(0W8Iu";
$companyusername = 'auth@simplr.com.ng';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the posted values
    $company_id = $_POST['company_id'];
    $manager_name = $_POST['manager_name'];
    $manager_email = $_POST['manager_email'];
    $department_id = $_POST['department_id'];
    $role_name = $_POST['role_name'];
    $description = $_POST['description'];

    // Get the company name from the session
    $company_name = $_SESSION['company_name'];

    // Check if the manager email already exists for the given company_id
    $check_sql = "SELECT * FROM managers WHERE company_id = ? AND manager_email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $company_id, $manager_email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "Manager with this email already exists for your company.";
    } else {
        // Insert the role name and description into the roles table
        $insert_role_sql = "INSERT INTO roles (role_name, description, company_id) VALUES (?, ?, ?)";
        $insert_role_stmt = $conn->prepare($insert_role_sql);
        $insert_role_stmt->bind_param("ssi", $role_name, $description, $company_id);

        if ($insert_role_stmt->execute()) {
            // Get the last inserted role's ID
            $role_id = $conn->insert_id;

            // Insert the new manager with pending status
            $status = "pending";
            $insert_manager_sql = "INSERT INTO managers (company_id, manager_name, manager_email, department_id, role_id, status) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_manager_stmt = $conn->prepare($insert_manager_sql);
            $insert_manager_stmt->bind_param("ississ", $company_id, $manager_name, $manager_email, $department_id, $role_id, $status);

            if ($insert_manager_stmt->execute()) {
                // Generate a random password for the manager
                $password = bin2hex(random_bytes(4)); // 8-character random password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into manager_auth table
                $insert_auth_sql = "INSERT INTO manager_auth (company_id, manager_name, manager_email, password) VALUES (?, ?, ?, ?)";
                $insert_auth_stmt = $conn->prepare($insert_auth_sql);
                $insert_auth_stmt->bind_param("isss", $company_id, $manager_name, $manager_email, $hashed_password);
                
                if ($insert_auth_stmt->execute()) {
                    echo "New manager created successfully with pending status.";

                    // Send notification email
                    $customername = $manager_name;
                    $customeremail = $manager_email;
                    
                    function sendNotificationEmail($customername, $company_name, $customeremail, $password) {
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
                            $mail->addAddress($customeremail, $customername);

                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = 'Manager Account Invitation';
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
                                        <p>Dear $customername,</p>
                                        <p>$company_name has invited you to become a manager. Here are your login credentials:</p>
                                        <ul>
                                            <li>Email: $customeremail</li>
                                            <li>Password: $password</li>
                                            <li>Login URL: <a href='https://simplr.com.ng/managers/login'>simplr.com.ng/managers/login</a></li>
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
                    $emailResult = sendNotificationEmail($customername, $company_name, $customeremail, $password);
                    echo $emailResult;
                } else {
                    echo "Error: " . $insert_auth_stmt->error;
                }
            } else {
                echo "Error: " . $insert_manager_stmt->error;
            }

            // Close the manager insert statement
            $insert_manager_stmt->close();
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
