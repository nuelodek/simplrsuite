<?php
session_start();
require_once '../../sql_queries/db.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = ''; // Initialize error message variable

// Function to send email using PHPMailer
function sendEmail($email, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'simplr.com.ng';  
        $mail->SMTPAuth   = true;                 
        $mail->Username   = 'auth@simplr.com.ng'; 
        $mail->Password   = '@gegetoedu123456';   
        $mail->SMTPSecure = 'ssl';                 
        $mail->Port       = 465;                   
        
        $mail->setFrom('support@gegeto.com.ng', 'Gegeto');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <title>Password Reset Request</title>
                <style>
                    @import url("https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap");
                    body {
                        font-family: "Open Sans", sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 20px auto;
                        background-color: #ffffff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    .header {
                        text-align: center;
                        padding: 10px;
                        background: linear-gradient(rgba(255, 154, 158, 0.5), rgba(128, 0, 128, 0.5)),
                        url("https://images.pexels.com/photos/1181592/pexels-photo-1181592.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2") center no-repeat;
                        background-size: cover;
                        color: #ffffff;
                        border-radius: 8px 8px 0 0;
                    }
                    .content {
                        margin: 20px 0;
                        text-align: center;
                    }
                    .footer {
                        text-align: center;
                        padding: 10px;
                        background-color: #f4f4f4;
                        border-radius: 0 0 8px 8px;
                    }
                    .verification-code {
                        display: inline-block;
                        background-color: #4CAF50;
                        color: #ffffff;
                        padding: 10px 20px;
                        border-radius: 5px;
                        text-decoration: none;
                        font-size: 18px;
                        margin: 20px 0;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Password Reset Request</h1>
                    </div>
                    <div class="content">
                        <p>Click the link below to reset your password:</p>
                        <a class="verification-code" href="https://gegeto.com/resetpassword.php?token=' . urlencode($token) . '">Reset Password</a>
                    </div>
                    <div class="footer">
                        <p>If you did not request a password reset, please ignore this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ';
        $mail->AltBody = 'Click the link to reset your password: https://gegeto.com.ng/resetpassword.php?token=' . urlencode($token);

        $mail->send();
        return 'Email has been sent';
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email'])) {
        $error_message = "Email not provided.";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        // Generate a new token
        $token = bin2hex(random_bytes(16));

        // Check if user exists in users table
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Check if user exists in employees_auth table
            $stmt->close();
            $stmt = $conn->prepare("SELECT email FROM employees_auth WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
        }

        if ($stmt->num_rows == 0) {
            // Check if user exists in manager_auth table
            $stmt->close();
            $stmt = $conn->prepare("SELECT email FROM manager_auth WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
        }

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email);
            $stmt->fetch();
        } else {
            $error_message = "No account found with that email.";
            $stmt->close();
            exit();
        }

        // Save token to database
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO password_resets (identifier, token) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $stmt->close();

        // Send reset link
        $result = sendEmail($email, $token);
        $error_message = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="buttonstyles.css">
    <link rel="stylesheet" href="btnstyles.css">
    <link rel="stylesheet" href="signup.css">
     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <style>
      
   body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: white;
            font-family: sans-serif;
            display: flex;
            box-sizing: border-box;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
         .container {
            background-color: white;
            width: 500px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }


        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input {
            padding: 10px 10px; 
            border-radius: 5px;
            /*border: none;*/
            margin-bottom: 10px;
        }

        .word {
            font-size: 30px;
            margin-bottom: 0px;
        }

        input[type="submit"]:hover {
            background-color: black;
            color: white;
        }

        p {
            line-height: 1.0;
        }
    </style>
    <title>Forget Password</title>
    <script>
        function validateForgotPasswordForm() {
            var email = document.getElementById('forgot-email').value;

            if (email === "") {
                alert("Email is required.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
   <div class="container">

    <p id="formDescription">Reset your password</p>


<form method="POST" action="resetpassword.php">

                    <label>Type your email*</label>
                    <input type="email" name="emaik" placeholder="Input your email" required>
                        <input type="submit" value="Reset Password"> 

                    </div>
                    </form>
        
 </div>
</body>
</html>
