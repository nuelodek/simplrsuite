<?php
session_start();

require 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
$mailpassword = "3)TiskO2(0W8Iu";
$companyusername = 'payments@simplr.com.ng';


// Get the form data
$customername = $_POST['customername'];
$companyid = $_POST['companyid'];
$customeremail = $_POST['customeremail'];
$amount = $_POST['amount'];
$currency = $_POST['currency'];

// Sanitize the input data
$customername = $conn->real_escape_string($customername);
$companyid = $conn->real_escape_string($companyid);
$customeremail = $conn->real_escape_string($customeremail);
$amount = $conn->real_escape_string($amount);
$currency = $conn->real_escape_string($currency);

// Insert data into the appropriate table
if ($currency === 'USD') {
    $usdpayment_sql = "INSERT INTO usdpayment (customername, companyid, customeremail, amount, currency)
                       VALUES ('$customername', '$companyid', '$customeremail', '$amount', '$currency')";

    if ($conn->query($usdpayment_sql) === TRUE) {
        echo "Record inserted successfully into usdpayment.";
    } else {
        echo "Error: " . $usdpayment_sql . "<br>" . $conn->error;
    }
} elseif ($currency === 'NGN') {
    $nairapayments_sql = "INSERT INTO nairapayments (customername, companyid, customeremail, amount, currency)
                          VALUES ('$customername', '$companyid', '$customeremail', '$amount', '$currency')";

    if ($conn->query($nairapayments_sql) === TRUE) {
        echo "Record inserted successfully into nairapayments.";
    } else {
        echo "Error: " . $nairapayments_sql . "<br>" . $conn->error;
    }
}

// Insert data into totalpayments
$totalpayments_sql = "INSERT INTO totalpayments (customername, companyid, customeremail, amount, currency)
                      VALUES ('$customername', '$companyid', '$customeremail', '$amount', '$currency')";

if ($conn->query($totalpayments_sql) === TRUE) {
    echo "Record inserted successfully into totalpayments.";
   
    // Send notification email
    function sendNotificationEmail($customername, $companyid, $customeremail, $amount, $currency) {
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
            $mail->Subject = 'Subscription Purchase Confirmation';
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
                        <h1>Thank You for Your Purchase!</h1>
                    </div>
                    <div class='content'>
                        <p>Dear $customername,</p>
                        <p>We are pleased to confirm your subscription purchase. Here are the details:</p>
                        <ul>
                            <li>Company ID: $companyid</li>
                            <li>Amount: $amount $currency</li>
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

    // Call the function to send the email
    $emailResult = sendNotificationEmail($customername, $companyid, $customeremail, $amount, $currency);
    echo $emailResult;
} else {
    echo "Error: " . $totalpayments_sql . "<br>" . $conn->error;
}

$conn->close();
?>