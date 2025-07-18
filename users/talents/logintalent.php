<?php
// Report all PHP errors
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
require_once 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate user
    $sql = "SELECT * FROM talentprofile WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Check password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['industry'] = $row['industry'];
            $_SESSION['talent_id'] = $row['talent_id'];
            $_SESSION['status'] = $row['status'];
            // $_SESSION['token'] = $row['token'];

            // Check status and redirect accordingly
            if ($row['status'] == 'Unverified') {
                // Redirect to verifyaccount.php with email parameter
                header("Location: verifytalents.php?email=" . urlencode($row['email']));
                exit();
            } elseif ($row['status'] == 'Verified') {
                // Redirect to dashboard.php
                header("Location: talentpool.php");
                exit();
            } else {
                // Handle other statuses if needed
                echo "Unknown status. Please contact support.";
            }
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with this email!";
    }

    $stmt->close();
}

$conn->close();
?>
