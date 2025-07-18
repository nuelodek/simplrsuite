<?php
session_start();
// require_once('db.php');
require_once '../../sql_queries/db.php';

// Function to generate a unique company ID
function generateUniqueCompanyID($conn) {
    $unique = false;
    $companyID = null;
    while (!$unique) {
        $tempID = mt_rand(100000, 999999); // Generate a random ID (adjust range as needed)
        $stmt = $conn->prepare("SELECT company_id FROM users WHERE company_id = ?");
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
$companyName = $_POST['company_name'];
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
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "An account with this email already exists!";
} else {
    // Generate a unique company ID
    $companyID = generateUniqueCompanyID($conn);

    // Insert new user into the database
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (company_name, industry, email, password, company_id, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $companyName, $industry, $email, $hashed_password, $companyID, $status);

          if ($stmt->execute()) {
    $_SESSION['email'] = $email;
    $_SESSION['status'] = $status;
    echo '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: #f0f0f0;">
              <img src="success.png" alt="Success" style="width: 64px; height: 64px;">
              <p style="font-size: 18px; color: #4CAF50; margin-top: 10px;">Your account has been created. Welcome!</p>
          </div>';
    echo '<script>
              setTimeout(function() {
                  window.location.href = "verifyaccount.php";
              }, 3000);                                                                  
          </script>';
} else {
    echo '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: #ffebee;">
              <p style="font-size: 18px; color: #f44336; margin: 0;">Error: ' . $stmt->error . '</p>
          </div>';
}
}

$stmt->close();
$conn->close();
?>
