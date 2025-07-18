<?php 

//include 'profile.php'; 
//include 'session.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Simplr - Admin Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">

</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar" id="sidebar">

        <ul>
<li><a href="#logo" id="logo" onclick="showDiv('logo')">SIMPLR Admin</a></li>
<li><a href="#dashboard" onclick="showDiv('dashboard')">Dashboard</a></li>
<li><a href="#onboarding" onclick="showDiv('onboarding')">Onboarding</a></li>
<li><a href="#tool-management" onclick="showDiv('tool-management')">Tool Management</a></li>
<li><a href="#managecompany" onclick="showDiv('managecompany')">Manage Company</a></li>
<li><a href="#profilie" onclick="showDiv('profile')">Profile</a></li>
<li><a href="#subscriptions" onclick="showDiv('subscriptions')">Subscriptions </a></li>
<li><a href="#chat" onclick="showDiv('chat')">Chat</a></li>
<li><a href="#settings" onclick="showDiv('settings')">Settings</a></li>
<li><a href="#support" onclick="showDiv('support')">Support</a></li>
<li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1>Dashboard</h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Subscriptions</h2>
                        <p id="total-sub"><?php include 'toolsub.php'; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $data['activeUsers']; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php echo $data['inactiveUsers']; ?></p>
                    </div>
                </div>
            </section>

            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>

                <?php include 'assigntools.php'; ?>

            </section>

            <section id="onboarding " style="display:none;">
            </section>


            <section id="settings" style="display:none;">
    
    <h1>Settings</h1>
    
    <div class="toolkit-menu">
    
    <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
    <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
    <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>

    </div>

    <!-- Dark Mode Toggle -->
    <section class="settings-option" id="darkmodal">
        <label for="dark-mode-toggle">Dark Mode:</label>
        <input type="checkbox" id="dark-mode-toggle">
    </section>
    
    <!-- Change Password -->
    <section class="settings-option" id="updatepass">
        <h2>Change Password</h2>
        <form action="change_password.php" method="post">
            <label for="current-password">Current Password:</label>
            <input type="password" id="current-password" name="current-password" required><br><br>
            
            <label for="new-password">New Password:</label>
            <input type="password" id="new-password" name="new-password" required><br><br>
            
            <label for="confirm-password">Confirm New Password:</label>
            <input type="password" id="confirm-password" name="confirm-password" required><br><br>
            
            <input type="submit" value="Change Password">
        </form>
                    </section>
    
    <!-- Update Profile Information -->
    <section class="settings-option" id="updatepro">
        <h2>Update Profile Information</h2>
        <form action="update_profile.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>
            
            <input type="submit" value="Update Profile">
        </form>
                    </section>

    
</section>
            <section id="support" style="display:none;">
                <h1>Support</h1>
                <form action="submit_support.php" method="post" enctype="multipart/form-data">
                    <label for="issue-type">Type of Issue:</label>
                    <select id="issue-type" name="issue-type" required>
                        <option value="">Select</option>
                        <option value="Technical Issue">Technical Issue</option>
                        <option value="Account Issue">Account Issue</option>
                        <option value="Billing Issue">Billing Issue</option>
                        <option value="Other">Other</option>
                    </select><br><br>

                    <label for="query">Query:</label><br>
                    <textarea id="query" name="query" rows="4" cols="50" required></textarea><br><br>

                    <label for="attachment">Attachment (optional):</label><br>
                    <input type="file" id="attachment" name="attachment"><br><br>

                    <input type="submit" value="Submit">
                </form>
            </section>

            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                
                <div class="dashboard-widgets">
                   
                        <?php include 'subtable.php'; ?>
            </section>

            <section id="profile" style="display:none;">
              <?php echo $user_profile_html; ?>
                </section>
            <!-- Add other sections like profile, settings, support here -->
            
             <section id="managecompany" style="display:none;">
    
    <h1>Manage Company</h1>
    
    <div class="toolkit-menu">
    
    <div class="mode-switch tools" onclick="toggleDarkModal()"> Invite Staff </div>
    <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
    <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>

    </div>
                
        <div class="createdepartment">
        <form action ="/mycompany/createdepartment.php" method="POST">
        <h3>Create New Department</h3>
        <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
        <label for="new_department">Department Name:</label>
        <input type="text" id="new_department" name="department_name">
        <label for="location">Location:</label>
        <input type="location" name="location" placeholder="location (optional)">
        <input type="submit" value="Create Department">
        </form>
        </div>
        

       <div class="createmanager">
    <h3>Create New Manager</h3>
    <form action="/mycompany/createmanager.php" method="POST">
        <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
        <label for="new_manager">Manager Name:</label>
        <input type="text" class="new_manager" name="manager_name" required><br><br>
        <label for="manager_email">Manager Email:</label>
        <input type="email" id="manager_email" name="manager_email" required><br><br>
        <label for="department_id">Department:</label>
        <select name="department_id" required>
            <option value="" disabled selected>Select a department</option>
            <?php
            // PHP code to fetch departments from the database and populate the select options
            include 'db.php';
            $result = $conn->query("SELECT id, department_name FROM departments WHERE company_id = $company_id AND manager_id IS NULL");
            while ($row = $result->fetch_assoc()) {
                echo "<option value=\"" . $row['id'] . "\">" . $row['department_name'] . "</option>";
            }
            ?>
        </select><br><br>
        <input type="hidden" id="role" name="role_name" value="Manager">
        <input type="hidden" name="description" value="Manager with access to managerial functions">
        <input type="submit" value="Create Manager">
    </form>
</div>

        
        
        
                <div class="invitestaffs">
    <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
        <div id="staffEntries">
            <div class="staffEntry">
                <h3>Staff Member</h3>
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required><br><br>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required><br><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>

                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone"><br><br>

                <label for="hire_date">Hire Date:</label>
                <input type="date" id="hire_date" name="hire_date" required><br><br>

                <label for="job_title">Job Title:</label>
                <input type="text" id="job_title" name="job_title"><br><br>

                <label for="department">Department:</label>
                <select id="departments" name="department_id" required onchange="setManagerId()">
                    <option value="" disabled selected>Select a department</option>
                    <?php
                    // PHP code to fetch departments with managers from the database
                    include 'db_connection.php';
                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                    }
                    ?>
                </select><br><br>

                <input type="hidden" id="manager_id" name="manager_id" required><br><br>

                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required><br><br>
                        <button type="submit">Invite Staff</button>

            </div>
        </div>
    </form>
</div>

<script>
function setManagerId() {
    var departmentSelect = document.getElementById("departments");
    var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
    var managerId = selectedOption.getAttribute("data-manager-id");
    document.getElementById("manager_id").value = managerId;
}

document.getElementById("addStaffEntry").addEventListener("click", function() {
    var staffEntries = document.getElementById("staffEntries");
    var newEntry = staffEntries.children[0].cloneNode(true);
    staffEntries.appendChild(newEntry);
});
</script>

</section>


        </div>
    </div>
    <script>
        function toggleNested(id) {
            var nestedMenu = document.getElementById(id);
            if (nestedMenu.classList.contains("active")) {
                nestedMenu.classList.remove("active");
            } else {
                nestedMenu.classList.add("active");
            }
        }
    </script>

    <script src="showdiv.js"></script>
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>
