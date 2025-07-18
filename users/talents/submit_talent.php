<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
include 'db.php';

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';
    require_once 'PHPMailer/src/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$simplrcompany = "Simplr Business Suite";
$mailpassword = "3)TiskO2(0W8Iu";
$companyusername = 'business@simplr.com.ng';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate a unique filename
function generate_unique_filename($original_filename) {
    $timestamp = time();
    $random_number = rand(1000, 9999);
    $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $filename = pathinfo($original_filename, PATHINFO_FILENAME);
    return $filename . '_' . $timestamp . '_' . $random_number . '.' . $extension;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $visasponsorship = sanitize_input($_POST["visasponsorship"]);
    $talent_id = sanitize_input($_POST["talent_id"]);
    $name = sanitize_input($_POST["name"]);
    $email = sanitize_input($_POST["email"]);
    $telephone = sanitize_input($_POST["telephone"]);
    $gender = sanitize_input($_POST["gender"]);
    $location = sanitize_input($_POST["location"]);
    $preferred_role = sanitize_input($_POST["preferred_role"]);
    $experience = sanitize_input($_POST["experience"]);
    $skills = isset($_POST["skills"]) ? json_encode($_POST["skills"]) : json_encode([]);
    $languages = isset($_POST["languages"]) ? json_encode($_POST["languages"]) : json_encode([]);
    $education = sanitize_input($_POST["education"]);
    $certifications = sanitize_input($_POST["certifications"]);
    $availability = sanitize_input($_POST["availability"]);
    $currency = sanitize_input($_POST["currency"]);
    $salary_expectation = sanitize_input($_POST["salary_expectation"]);
    $portfolio_url = sanitize_input($_POST["portfolio_url"]);
    $linkedin_profile = sanitize_input($_POST["linkedin_profile"]);
    $contact_references = sanitize_input($_POST["contact_references"]);
    $other_information = sanitize_input($_POST["other_information"]);
    $preferred_working_arrangement = sanitize_input($_POST["preferred_working_arrangement"]);

    // Handle file uploads
    $target_dir = "uploads/";

    $profile_picture = $_FILES["profile_picture"]["name"];
    $resume_file = $_FILES["resume_file"]["name"];

    $profile_picture_path = $target_dir . generate_unique_filename($profile_picture);
    $resume_file_path = $target_dir . generate_unique_filename($resume_file);

    move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture_path);
    move_uploaded_file($_FILES["resume_file"]["tmp_name"], $resume_file_path);

    // Prepare SQL statement
    $sql = "INSERT INTO talents (name, email, telephone, gender, location, preferred_role, experience, skills, education, certifications, languages, availability, currency, salary_expectation, portfolio_url, linkedin_profile, contact_references, other_information, preferred_working_arrangement, profile_picture, resume_file, talent_id, visasponsorship) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssssssssssss", 
        $name, $email, $telephone, $gender, $location, $preferred_role, $experience, $skills, 
        $education, $certifications, $languages, $availability, $currency, $salary_expectation, 
        $portfolio_url, $linkedin_profile, $contact_references, $other_information, 
        $preferred_working_arrangement, $profile_picture_path, $resume_file_path, $talent_id, $visasponsorship
    );

    // Execute statement and check for success
    if ($stmt->execute()) {
        // Function to send notification email
        function sendNotificationEmail($name, $email) {
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
                $mail->addAddress($email, $name);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Talent Profile Created';
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
                            <h1>Talent Profile Created</h1>
                        </div>
                        <div class='content'>
                            <p>Dear $name,</p>
                            <p>Your talent profile has been successfully created in our system.</p>
                            <p>Thank you for joining our talent pool. We will review your profile and contact you if there are any suitable opportunities.</p>
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
                return "New talent profile created successfully.";
            } catch (Exception $e) {
                return "Failed to send notification email. Error: {$mail->ErrorInfo}";
            }
        }

        $result = sendNotificationEmail($name, $email);
        echo $result;
        header("Location: talentpool.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
