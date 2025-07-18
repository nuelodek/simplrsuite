<?php
include '../../sql_queries/db.php';
include 'PHPMailer/src/PHPMailer.php'; // Include PHPMailer
include 'PHPMailer/src/SMTP.php';
include 'PHPMailer/src/Exception.php';

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$simplrcompany = "Simplr Business Suite";
$mailpassword = "3)TiskO2(0W8Iu";
$companyusername = 'business@simplr.com.ng';

// Check if required POST variables are set
if (isset($_POST['revoke']) && isset($_POST['employee_id']) && isset($_POST['tool_ids']) && isset($_POST['company_id'])) {
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $tool_ids = $_POST['tool_ids']; // This is an array of tool IDs
    $company_id = $conn->real_escape_string($_POST['company_id']);

    // Get employee details for email
    $sql_employee = "SELECT first_name, last_name, email FROM employees WHERE id = '$employee_id'";
    $result_employee = $conn->query($sql_employee);
    if ($result_employee->num_rows > 0) {
        $employee = $result_employee->fetch_assoc();
        $first_name = $employee['first_name'];
        $last_name = $employee['last_name'];
        $email = $employee['email'];
    } else {
        echo '<div style="color: red;">Employee not found.</div>';
        exit;
    }

    $revoked_tools = [];

    // Delete from tools_assigned table for each selected tool
    foreach ($tool_ids as $tool_id) {
        $tool_id = $conn->real_escape_string($tool_id);
        $sql_revoke_tool = "DELETE FROM tools_assigned WHERE employee_id = '$employee_id' AND tool_id = '$tool_id' AND company_id = '$company_id'";
        if ($conn->query($sql_revoke_tool) === TRUE) {
            $tool_name = get_tool_name($tool_id, $conn);
            if ($tool_name) {
                $revoked_tools[] = $tool_name;
            }
        } else {
            echo '<div style="color: red;">Error revoking tool: ' . $conn->error . '</div>';
        }
    }

    if (!empty($revoked_tools)) {
echo '<script>alert("Tools revoked successfully"); window.location.href = "dashboard.php";</script>';

        // Send notification email
        $emailResult = sendRevokeNotificationEmail($first_name, $last_name, $simplrcompany, $email, $revoked_tools);
        echo $emailResult;
    }
} else {
    echo '<div style="color: red;">Required parameters are missing.</div>';
}

// Function to fetch tool name based on tool id
function get_tool_name($tool_id, $conn) {
    $sql_tool_name = "SELECT tool_name FROM tools WHERE id = '$tool_id'";
    $result_tool_name = $conn->query($sql_tool_name);
    if ($result_tool_name->num_rows > 0) {
        $row_tool_name = $result_tool_name->fetch_assoc();
        return $row_tool_name['tool_name'];
    }
    return null;
}

// Function to send notification email
function sendRevokeNotificationEmail($first_name, $last_name, $company_name, $email, $tools) {
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
        $tools_list = implode(', ', $tools);
        $mail->isHTML(true);
        $mail->Subject = 'Tools Revoked Notification';
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
                    <h1>Tools Revoked</h1>
                </div>
                <div class='content'>
                    <p>Dear $first_name $last_name,</p>
                    <p>The following tools have been revoked by $company_name:</p>
                    <ul> 
                        <li>$tools_list</li>
                    </ul>
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
?>
