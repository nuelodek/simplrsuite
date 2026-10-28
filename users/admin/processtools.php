<?php
session_start();
include '../../sql_queries/db.php';

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$simplrcompany = "Simplr Business Suite";
$mailpassword = "";
$companyusername = 'superadmin@simplr.com.ng';

$email = $_POST['email'];
$company_id = $_POST['company_id'];

$created_at = date('Y-m-d H:i:s');

$tools = [
    'payroll' => ['months' => isset($_POST['payroll-months']) ? $_POST['payroll-months'] : null, 'startdate' => isset($_POST['payroll-startdate']) ? $_POST['payroll-startdate'] : null, 'expireddate' => isset($_POST['payroll-expireddate']) ? $_POST['payroll-expireddate'] : null],
    'recruiting' => ['months' => isset($_POST['recruiting-months']) ? $_POST['recruiting-months'] : null, 'startdate' => isset($_POST['recruiting-startdate']) ? $_POST['recruiting-startdate'] : null, 'expireddate' => isset($_POST['recruiting-expireddate']) ? $_POST['recruiting-expireddate'] : null],
    'crm' => ['months' => isset($_POST['crm-months']) ? $_POST['crm-months'] : null, 'startdate' => isset($_POST['crm-startdate']) ? $_POST['crm-startdate'] : null, 'expireddate' => isset($_POST['crm-expireddate']) ? $_POST['crm-expireddate'] : null],
    'cybersecurity' => ['months' => isset($_POST['cybersecurity-months']) ? $_POST['cybersecurity-months'] : null, 'startdate' => isset($_POST['cybersecurity-startdate']) ? $_POST['cybersecurity-startdate'] : null, 'expireddate' => isset($_POST['cybersecurity-expireddate']) ? $_POST['cybersecurity-expireddate'] : null],
    'project' => ['months' => isset($_POST['project-months']) ? $_POST['project-months'] : null, 'startdate' => isset($_POST['project-startdate']) ? $_POST['project-startdate'] : null, 'expireddate' => isset($_POST['project-expireddate']) ? $_POST['project-expireddate'] : null],
    'strategy' => ['months' => isset($_POST['strategy-months']) ? $_POST['strategy-months'] : null, 'startdate' => isset($_POST['strategy-startdate']) ? $_POST['strategy-startdate'] : null, 'expireddate' => isset($_POST['strategy-expireddate']) ? $_POST['strategy-expireddate'] : null],
    'risk' => ['months' => isset($_POST['risk-months']) ? $_POST['risk-months'] : null, 'startdate' => isset($_POST['risk-startdate']) ? $_POST['risk-startdate'] : null, 'expireddate' => isset($_POST['risk-expireddate']) ? $_POST['risk-expireddate'] : null],
    'sales' => ['months' => isset($_POST['sales-months']) ? $_POST['sales-months'] : null, 'startdate' => isset($_POST['sales-startdate']) ? $_POST['sales-startdate'] : null, 'expireddate' => isset($_POST['sales-expireddate']) ? $_POST['sales-expireddate'] : null],
    'product' => ['months' => isset($_POST['product-months']) ? $_POST['product-months'] : null, 'startdate' => isset($_POST['product-startdate']) ? $_POST['product-startdate'] : null, 'expireddate' => isset($_POST['product-expireddate']) ? $_POST['product-expireddate'] : null],
    'office' => ['months' => isset($_POST['office-months']) ? $_POST['office-months'] : null, 'startdate' => isset($_POST['office-startdate']) ? $_POST['office-startdate'] : null, 'expireddate' => isset($_POST['office-expireddate']) ? $_POST['office-expireddate'] : null]
];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$subscribed_tools = [];

foreach ($tools as $tool => $data) {
    if ($data['months'] !== null) {
        // Calculate new expiration date
        $sql = "SELECT {$tool}_expireddate FROM toolkit_subscriptions WHERE company_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $company_id);
        $stmt->execute();
        $stmt->bind_result($current_expiration_date);
        $stmt->fetch();
        $stmt->close();

        $current_date = new DateTime();
        $expiration_date = new DateTime($current_expiration_date);

        if ($expiration_date < $current_date) {
            $expiration_date = $current_date;
        }

        $expiration_date->modify("+{$data['months']} months");

        $data['expireddate'] = $expiration_date->format('Y-m-d');

        $sql = "INSERT INTO toolkit_subscriptions (
            email,
            {$tool}_months, {$tool}_startdate, {$tool}_expireddate,
            company_id,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            email = VALUES(email),
            {$tool}_months = VALUES({$tool}_months),
            {$tool}_startdate = VALUES({$tool}_startdate),
            {$tool}_expireddate = VALUES({$tool}_expireddate)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssss",
            $email,
            $data['months'], $data['startdate'], $data['expireddate'],
            $company_id,
            $created_at
        );

        if ($stmt->execute()) {
            echo "{$tool} subscription updated successfully<br>";
            $subscribed_tools[ucfirst($tool)] = ['expireddate' => $data['expireddate']];
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $stmt->close();
    }
}

// Send notification email
function sendNotificationEmail($email, $company_id, $subscribed_tools) {
    global $companyusername, $mailpassword, $simplrcompany;

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'simplr.com.ng';
        $mail->SMTPAuth   = true;
        $mail->Username   = $companyusername;
        $mail->Password   = $mailpassword;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom($companyusername, $simplrcompany);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Subscription Confirmation';
        $mail->Body    = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { width: 600px; margin: 0 auto; }
                .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f4f4f4; padding: 10px; text-align: center; }
                .a {color:#f4f4f4; text-decoration: none; font-weight: bold}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Thank You for Your Subscription!</h1>
                </div>
                <div class='content'>
                    <p>Dear Valued Customer,</p>
                    <p>We are pleased to confirm your subscription to the following tool(s):</p>
                    <ul>
                        " . implode('', array_map(function($tool) use ($subscribed_tools) {
                            $expiry_date = $subscribed_tools[$tool]['expireddate'];
                            return "<li>$tool (Expires: $expiry_date)</li>";
                        }, array_keys($subscribed_tools))) . "
                    </ul>
                    <p>Company ID: $company_id</p>
                    <p>Maximize the potential of your tools by assigning them to your company staff(s).
                    Visit your dashboard to get started: <a href='https://simplr.com.ng/dashboard.php'>Click here</a></p>
                    <p>We're here to help! If you have any questions or need assistance, please don't hesitate to reach out:</p>
                    <ul>
                        <li>Email: <a href='mailto:support@simplr.com.ng'>support@simplr.com.ng</a></li>
                        <li>Visit our <a href='https://simplr.com.ng/support'>Support Center</a></li>
                    </ul>
                    <p>Our team is committed to ensuring you have the best experience with our tools.</p>
               
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
if (!empty($subscribed_tools)) {
    $emailResult = sendNotificationEmail($email, $company_id, $subscribed_tools);
    echo $emailResult;
} else {
    echo "No tools were subscribed.";
}

$conn->close();
?>
