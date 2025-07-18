<?php

session_start(); 

// require 'vendor/autoload.php';

include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// // Enable error logging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
//     require_once 'PHPMailer/src/PHPMailer.php';
//     require_once 'PHPMailer/src/SMTP.php';
//     require_once 'PHPMailer/src/Exception.php';
// }

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

$simplrcompany = "Simplr Business Suite";
$mailpassword = "3)TiskO2(0W8Iu";
$companyusername = 'business@simplr.com.ng';

// Fetch employees to display in the user management interface
$companyid = $_SESSION['company_id'];
$sql_employees = "SELECT * FROM employees WHERE company_id = '$companyid'";
$result_employees = $conn->query($sql_employees);

// Handle form submission to assign tools to employees
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign'])) {
    $employee_id = $_POST['employee_id'];
    $tool_ids = $_POST['tool_ids'];
    
    // Get employee details for email
    $sql_employee = "SELECT first_name, last_name, email FROM employees WHERE id = '$employee_id'";
    $result_employee = $conn->query($sql_employee);
    $employee = $result_employee->fetch_assoc();
    $first_name = $employee['first_name'];
    $last_name = $employee['last_name'];
    $email = $employee['email'];

    $assigned_tools = [];
    
    // Insert into tools_assigned table for each selected tool
    foreach ($tool_ids as $tool_id) {
        // Verify tool ID exists in tools table
        if (!is_tool_id_valid($tool_id, $conn)) {
            echo '<div style="color: red;">Error: Invalid tool ID ' . htmlspecialchars($tool_id) . '.</div>';
            continue;
        }

        $sql_assign_tool = "INSERT INTO tools_assigned (employee_id, tool_id, assigned_at, company_id) 
                            VALUES ('$employee_id', '$tool_id', NOW(), '$company_id')";
        if ($conn->query($sql_assign_tool) === TRUE) {
            $assigned_tools[] = get_tool_name($tool_id, $conn);
        } else {
            echo '<div style="color: red;">Error assigning tool: ' . $conn->error . '</div>';
        }
    }
    
    if (!empty($assigned_tools)) {
         echo '<script>alert("Tools assigned successfully"); window.location.href = "newdashboard.php";</script>';

        // Send notification email
        $emailResult = sendNotificationEmail($first_name, $last_name, $company_name, $email, $assigned_tools);
        // echo $emailResult;
    }
}

// // Function to send notification email
// function sendNotificationEmail($first_name, $last_name, $company_name, $email, $assigned_tools) {
//     global $companyusername, $mailpassword, $simplrcompany; 

//     $mail = new PHPMailer(true);

//     try {
//         // Server settings
//         $mail->isSMTP();
//         $mail->Host       = 'simplr.com.ng'; // Replace with your SMTP server
//         $mail->SMTPAuth   = true;
//         $mail->Username   = $companyusername; // Replace with your email
//         $mail->Password   = $mailpassword; // Replace with your email password
//         $mail->SMTPSecure = 'ssl';
//         $mail->Port       = 465;

//         // Recipients
//         $mail->setFrom($companyusername, $simplrcompany);
//         $mail->addAddress($email, $first_name . ' ' . $last_name);

//         // Content
//         $tools_list = implode(', ', $assigned_tools);
//         $mail->isHTML(true);
//         $mail->Subject = 'Tools Assigned Notification';
//         $mail->Body    = "
//         <html>
//         <head>
//             <style>
//                 body { font-family: Arial, sans-serif; }
//                 .container { width: 600px; margin: 0 auto; }
//                 .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
//                 .content { padding: 20px; }
//                 .footer { background-color: #f4f4f4; padding: 10px; text-align: center; }
//             </style>
//         </head>
//         <body>
//             <div class='container'>
//                 <div class='header'>
//                     <h1>Tools Assigned</h1>
//                 </div>
//                 <div class='content'>
//                     <p>Dear $first_name $last_name,</p>
//                     <p>You have been assigned the following tools by $company_name:</p>
//                     <ul>
//                         <li>$tools_list</li>
//                     </ul>
//                     <p>If you have any questions, please don't hesitate to contact us.</p>
//                 </div>
//                 <div class='footer'>
//                     <p>© " . date("Y") . " $simplrcompany. All rights reserved.</p>
//                 </div>
//             </div>
//         </body>
//         </html>
//         ";

//         $mail->send();
//         return "Notification email sent successfully.";
//         header("Location: dashboard.php");
//         exit();
        
//     } catch (Exception $e) {
//         return "Failed to send notification email. Error: {$mail->ErrorInfo}";
//     }
// }

// Function to check if a tool is already assigned to an employee in the same company
function is_tool_assigned($tool_id, $company_id, $conn) {
    $sql_check = "SELECT * FROM tools_assigned WHERE tool_id = '$tool_id' AND company_id = '$company_id'";
    $result_check = $conn->query($sql_check);
    return ($result_check->num_rows > 0);
}

// Function to fetch tool name based on tool id
function get_tool_name($tool_id, $conn) {
    $sql_tool_name = "SELECT tool_name FROM tools WHERE id = '$tool_id'";
    $result_tool_name = $conn->query($sql_tool_name);
    if ($result_tool_name->num_rows > 0) {
        $row_tool_name = $result_tool_name->fetch_assoc();
        return $row_tool_name['tool_name'];
    }
    return null;
}

// Function to fetch tool id based on tool name
function fetch_tool_id($tool_name, $conn) {
    $sql_tool_id = "SELECT id FROM tools WHERE tool_name = '$tool_name'";
    $result_tool_id = $conn->query($sql_tool_id);
    if ($result_tool_id->num_rows > 0) {
        $row_tool_id = $result_tool_id->fetch_assoc();
        return $row_tool_id['id'];
    }
    return null;
}

// Function to validate tool ID
function is_tool_id_valid($tool_id, $conn) {
    $sql_tool_id = "SELECT id FROM tools WHERE id = '$tool_id'";
    $result_tool_id = $conn->query($sql_tool_id);
    return ($result_tool_id->num_rows > 0); 
}

?>

<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="customizeforms">
 
 
              <div class="input-container">

    <select name="employee_id" id="employee_idass" required>
        <option value="" disabled>Select Employee</option>
      <?php
// Ensure you have a valid database connection in $conn

// Prepare the SQL query
$sql_notools = "SELECT e.* FROM employees e
                LEFT JOIN tools_assigned ta ON e.id = ta.employee_id
                WHERE e.company_id = '$company_id' AND ta.employee_id IS NULL";

$result_notools = $conn->query($sql_notools);

if ($result_notools->num_rows > 0) {
    while ($result_notool = $result_notools->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($result_notool['id']) . '">' . htmlspecialchars($result_notool['id']) . ' - ' . htmlspecialchars($result_notool['first_name']) . ' ' . htmlspecialchars($result_notool['last_name']) . '</option>';
    }
} else {
    echo '<option value="-1" disabled>No employees found</option>';
}
?>

    </select>
    
        <label for="employee_id">Select Employee:</label>

</div>
             <div class="input-container">

    <select name="tool_ids[]" id="tool_idsass" multiple required>
        <?php
        $sql_subscriptions = "SELECT * FROM toolkit_subscriptions WHERE company_id = '$company_id'";
        $result_subscriptions = $conn->query($sql_subscriptions);

        if ($result_subscriptions->num_rows > 0) {
            while ($row_subscription = $result_subscriptions->fetch_assoc()) {
                $tool_options = '';

                // Generate options for each tool based on subscription details
                if ($row_subscription['payroll_months'] > 0 && $row_subscription['payroll_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Payroll & Accounting', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Payroll & Accounting (Expires: ' . $row_subscription['payroll_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['cybersecurity_months'] > 0 && $row_subscription['cybersecurity_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Cybersecurity Console', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Cybersecurity Console (Expires: ' . $row_subscription['cybersecurity_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['project_months'] > 0 && $row_subscription['project_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Project Management', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Project Management (Expires: ' . $row_subscription['project_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['recruiting_months'] > 0 && $row_subscription['recruiting_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Recruiting & Human Resources', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Recruiting & Human Resources (Expires: ' . $row_subscription['recruiting_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['crm_months'] > 0 && $row_subscription['crm_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Customer Relationship Management', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Customer Relationship Management (Expires: ' . $row_subscription['crm_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['strategy_months'] > 0 && $row_subscription['strategy_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Business Strategy', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Business Strategy (Expires: ' . $row_subscription['strategy_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['risk_months'] > 0 && $row_subscription['risk_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Risk Management & Compliance', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Risk Management & Compliance (Expires: ' . $row_subscription['risk_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['sales_months'] > 0 && $row_subscription['sales_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Sales & Marketing', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Sales & Marketing (Expires: ' . $row_subscription['sales_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['product_months'] > 0 && $row_subscription['product_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Product Development', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Product Development (Expires: ' . $row_subscription['product_expireddate'] . ')</option>';
                    }
                }
                if ($row_subscription['office_months'] > 0 && $row_subscription['office_expireddate'] > date('Y-m-d')) {
                    $tool_id = fetch_tool_id('Office Management & Tasking', $conn);
                    if (!is_tool_assigned($tool_id, $company_id, $conn)) {
                        $tool_options .= '<option value="' . $tool_id . '">Office Management & Tasking (Expires: ' . $row_subscription['office_expireddate'] . ')</option>';
                    }
                }

                echo $tool_options;
            }
        } else {
            echo '<option value="-1" disabled>No tools found</option>';
        }
        ?>
    </select>
   
    <label for="tool_ids">Select Tools:</label>

</div>
<input type="hidden" name="assigned_by" value="<?php echo htmlspecialchars($user_id . ' - ' . $company_name); ?>">

             <div class="input-container">


<textarea name="assignment_notes" placeholder="Type in a usability note for the prospective tools (optional)"></textarea>

</div> 
<button type="submit" name="assign" style="padding: 10px; background-color: #f44336; color: white; border: none; cursor: pointer; margin-top: 10px;">Assign Tools</button>
</form>


<?php

$conn->close();
?>
