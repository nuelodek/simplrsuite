<?php
session_start();
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_GET['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Verify the token and update the password
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($identifier);
        $stmt->fetch();
        $stmt->close();

        // Function to update password in a given table
        function updatePassword($conn, $table, $identifier, $hashed_password) {
            $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $identifier);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            return $affected_rows;
        }

        // Check each table and update the password if a match is found
        $tables = ['manager_auth', 'employees_auth', 'users'];
        $password_updated = false;

        foreach ($tables as $table) {
            if (updatePassword($conn, $table, $identifier, $hashed_password) > 0) {
                $password_updated = true;
                break;
            }
        }

        // Check if the password was successfully updated
        if ($password_updated) {
            // Remove the token from password_resets table
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->close();

            echo "Password has been reset successfully.";
        } else {
            echo "Error updating password.";
        }
    } else {
        echo "Invalid token.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Sign Up / Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <title>Reset Password</title>

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

 </head>


   <body>

<div class="container">

    <h2 id="formDescription">Reset your password</h2>


<form method="POST" action="forgetpassword.php">
                   
                    <label>Type your new password*</label>
                    <input type="password" name="new_password" placeholder="New Password" required>
                    
                    
                                        <label>Confirm Password</label>

                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <div class="social-login">

                        <input type="submit" value="Reset Password"> 

                    </div>
                    </form>
        
 </div>
 <script>
        function validateForm() {
            var fullName = document.getElementById('full').value;
            var identifier = document.getElementById('identifier').value;
            var password = document.getElementById('password').value;
            var agree = document.getElementById('agree').checked;

            if (fullName === "" || identifier === "" || password === "") {
                alert("All fields are required.");
                return false;
            }
            if (!agree) {
                alert("You must agree to the terms and conditions.");
                return false;
            }
            return true;
        }

        function validateLoginForm() {
            var identifier = document.getElementById('login-identifier').value;
            var password = document.getElementById('login-password').value;

            if (identifier === "" || password === "") {
                alert("All fields are required.");
                return false;
            }
            return true;
        }

        function validateForgotPasswordForm() {
            var identifier = document.getElementById('forgot-identifier').value;

            if (identifier === "") {
                alert("Identifier is required.");
                return false;
            }
            return true;
        }
    </script>

</body>
</html>