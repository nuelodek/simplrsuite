<?php
// Start session to retrieve user email
session_start();

// Check if user is logged in (you should have session management logic in place)
if (!isset($_SESSION['manager_email'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit();
}

// Include database connection
include '../db.php';

// Retrieve session email
$email = $_SESSION['manager_email'];

// Query database to fetch user information
$sql = "SELECT * FROM manager_auth WHERE manager_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Initialize user data variables
$user_id = null;
$user_email = null;
$user_status = null;
$user_created_at = null;

if ($result->num_rows > 0) {
    // Fetch user data
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
    $user_email = $row['manager_email'];
    $user_status = $row['status'];
    $user_created_at = $row['created_at'];

    // Generate user profile HTML
    $user_profile_html = "
        <h1>User Profile</h1>
        <p><strong>ID:</strong> " . htmlspecialchars($user_id) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($user_email) . "</p>
        <p><strong>Status:</strong> " . htmlspecialchars($user_status) . "</p>
        <p><strong>Created At:</strong> " . htmlspecialchars($user_created_at) . "</p>
    ";
} else {
    // Handle case where no user data found (though in a session-based system, this should rarely occur if the session management is correct)
    echo "User not found.";
    exit();
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 50%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
        }
        p {
            font-size: 1.2em;
        }
        strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $user_profile_html; ?>
    </div>
</body>
</html>
