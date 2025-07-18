<?php
session_start();

include "db.php";

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to sanitize email
function sanitize_email($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

// Function to generate random token
function generateRandomToken() {
    return bin2hex(random_bytes(32));
}

// Function to send verification email
function sendVerificationEmail($email, $verification_link) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'simplr.com.ng';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'superadmin@simplr.com.ng';
        $mail->Password   = '3)TiskO2(0W8Iu';
        $mail->SMTPSecure = 'ssl';   
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('superadmin@simplr.com.ng', 'Simplr Talent');
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Account';
        $mail->Body    = "
            <div style='text-align: center; padding: 20px; font-family: Arial, sans-serif;'>
                <img src='http://simplr.com.ng/logo.png' alt='Simplr Business' style='width: 100px;'>
                <h2>Verify Your Account</h2>
                <p>Please click the button below to verify your account:</p>
                <a href='$verification_link' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a>
                <p>Or click the following link: <a href='$verification_link'>$verification_link</a></p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send email. Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Check if form is submitted to generate token
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_token'])) {
    if (isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Invalid email format.";
            exit;
        }

        // Generate a random token
        $token = generateRandomToken();

        // Store the token in the database for the user
        $stmt = $conn->prepare("UPDATE talentprofile SET token=? WHERE email=?");
        $stmt->bind_param("ss", $token, $email);
        if ($stmt->execute()) {
            // Send email with verification link containing token
            $verification_link = "http://simplr.com.ng/verifytalents.php?email=" . urlencode($email) . "&token=" . urlencode($token);
            if (sendVerificationEmail($email, $verification_link)) {
                $message = "Token generated and sent to your email. Check your inbox (and spam folder).";
            } else {
                $message = "Failed to send verification email.";
            }
        } else {
            $message = "Error updating token: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Email parameter not found.";
    }
} elseif (isset($_GET['email']) && isset($_GET['token'])) {
    // Process verification if token is provided
    $email = sanitize_email($_GET['email']);
    $token = $_GET['token'];

    // Verify token and update status if valid
    $stmt = $conn->prepare("SELECT * FROM talentprofile WHERE email=? AND token=? AND status='Unverified'");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Update status to verified
        $stmt = $conn->prepare("UPDATE talentprofile SET status='Verified', token=NULL WHERE email=?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $message = "Account verified successfully. You can now <a href='talentpool.php'>login to your talentpool</a>.";
        } else {
            $message = "Error updating status: " . $stmt->error;
        }
    } else {
        $message = "Invalid verification link or account already verified.";
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="form.css">
    <title>Account Verification</title>
    <style>
        .readonly-input {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>   
<div class="container">
<?php if (!isset($_GET['email']) || !isset($_GET['token'])): ?>
    <!-- Display form to generate token -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h1>Account Verification</h1>
        <?php if (isset($message)) echo "<p>$message</p>"; ?>
        <label for="email">Enter your email*</label>
        <input type="email" id="email" name="email" required>
        <input type="submit" name="generate_token" value="Generate Token">
    </form>
<?php else: ?>
    <!-- Display form to verify token -->
    <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h1>Account Verification</h1>
        <?php if (isset($message)) echo "<p>$message</p>"; ?>
        <label for="email">Your Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly class="readonly-input">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="submit" value="Verify Account">
    </form>
<?php endif; ?>
</div>
</body>
</html>
