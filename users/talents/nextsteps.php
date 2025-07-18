<?php
session_start();
require_once('db.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to generate a unique company ID
function generateUniqueTalentID($conn) {
    $unique = false;
    $companyID = null;
    while (!$unique) {
        $tempID = mt_rand(100000, 999999); // Generate a random ID (adjust range as needed)
        $stmt = $conn->prepare("SELECT talent_id FROM talentprofile WHERE talent_id = ?");
        $stmt->bind_param("s", $tempID);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $unique = true;
            $companyID = $tempID;
        }
    }
    return $companyID;
}

// Get form data
$fullName = $_POST['full_name'];
$industry = $_POST['industry'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];
$status = $_POST['status'];

// Validate that passwords match
if ($password !== $confirmPassword) {
    echo "Passwords do not match!";
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT * FROM talentprofile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "An account with this email already exists!";
} else {
    // Generate a unique company ID
    $talentID = generateUniqueTalentID($conn);

    // Insert new user into the database
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO talentprofile (full_name, industry, email, password, talent_id, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullName, $industry, $email, $hashed_password, $talentID, $status);

    if ($stmt->execute()) {
        $_SESSION['email'] = $email;
        $_SESSION['status'] = $status;
            echo "<script>
                alert('New account created successfully! You will be redirected to talent login in 5 seconds.');
                var count = 5;
                var countdown = setInterval(function() {
                    count--;
                    if (count <= 0) {
                        clearInterval(countdown);
                        window.location.href = 'talentlogin.php';
                    } else {
                        document.getElementById('countdown').innerHTML = count;
                    }
                }, 1000);
            </script>
            <p>Redirecting in <span id='countdown'>5</span> seconds...</p>";
    } else {
        echo "Error: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
