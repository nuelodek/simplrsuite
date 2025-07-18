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
    $sql = "SELECT ea.id, ea.email, ea.password, ea.company_id, 
                   e.id as employee_id, e.first_name, e.last_name, e.department_id, 
                   e.role_id, e.manager_id, e.phone, e.hire_date, e.job_title, e.created_at, e.updated_at,
                   ta.tool_id, ta.assigned_at, ta.assigned_by, ta.assignment_notes
            FROM employees_auth ea
            LEFT JOIN employees e ON ea.id = e.id
            LEFT JOIN tools_assigned ta ON e.id = ta.employee_id
            WHERE ea.email = ?";
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
            $_SESSION['employee_id'] = $row['employee_id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['company_id'] = $row['company_id'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['department_id'] = $row['department_id'];
            $_SESSION['manager_id'] = $row['manager_id'];
            $_SESSION['role_id'] = $row['role_id'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['hire_date'] = $row['hire_date'];
            $_SESSION['job_title'] = $row['job_title'];
            $_SESSION['created_at'] = $row['created_at'];
            $_SESSION['updated_at'] = $row['updated_at'];
            $_SESSION['tool_id'] = $row['tool_id'];
            $_SESSION['assigned_at'] = $row['assigned_at'];
            $_SESSION['assigned_by'] = $row['assigned_by'];
            $_SESSION['assignment_notes'] = $row['assignment_notes'];
            
            
            header("Location: workspace.php");
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with this email!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html> 
<head>
    <meta charset="UTF-8" />
    <title>Sign Up / Login</title>
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
</head>
<body>

<div class="container">
<form id="authForm" action="login.php" method="post">
    <h3 class="word">SIMPLR</h3>
    <p id="formDescription">Login your all-in-one business management account</p>

    <label>Email Address*</label>
    <input type="email" name="email" placeholder="name@company.com" required>
    <label>Password*</label>
    <input type="password" name="password" placeholder="********" required>
  
    <input type="submit" value="Login"> 
    <div style="display:flex; flex-direction:column;">      
    <small>Lost your password? <a href="passwordreset.html" id="resetlink">Reset</a></small>

 </div>

</form>
</div>

</body>
</html>
