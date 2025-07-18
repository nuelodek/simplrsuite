
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $first_name; ?>  <?php $company_name; ?> Workspace </title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="support_form.css">
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="widgetcustomizer.css">
<link rel="stylesheet" href="managecompany.css">
<script src="showdiv.js" defer></script>
</head>
<body>
        <nav class="sidebar" id="sidebar">
     
     <style>
         
        
          
     </style>

            
            
<img src="../simple-social-svgrepo-com.png" style="width:40px; height: 40px;  margin-left: 20px;>">
        <ul>

<li><a href="#dashboard" onclick="toggleDashboard()">Dashboard</a></li>
<li><a href="#job-postings" onclick="toggleJobPostings()">Job Postings</a></li>
<li>
    <a href="#candidate-management" onclick="toggleCandidateManagement()">Candidate Management</a>
    <ul id="candidateManagementSubMenu" style="display: none;">
        <li><a href="#talent-pool" onclick="toggleTalentPool()">Talent Pool</a></li>
        <li><a href="/applicant-tracking" onclick="toggleApplicantTracking()" target="#">Applicant Tracking</a></li>
        <li><a href="#resume-screening" onclick="toggleResumeScreening()">Resume Screening</a></li>
        <li><a href="#interview-scheduling" onclick="toggleInterviewScheduling()">Interview Scheduling</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#candidate-management"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('candidateManagementSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});
</script>

<li><a href="#reports" onclick="toggleReports()">Reports</a>
    <ul id="reportsSubMenu" style="display: none;">
        <li><a href="#daily-report" onclick="toggleDailyReport()">Daily Report</a></li>
        <li><a href="#monthly-report" onclick="toggleMonthlyReport()">Monthly Report</a></li>
        <li><a href="#quarterly-report" onclick="toggleQuarterlyReport()">Quarterly Report</a></li>
        <li><a href="#yearly-report" onclick="toggleYearlyReport()">Yearly Report</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#reports"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('reportsSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleDailyReport() {
    // Implementation for daily report
}

function toggleMonthlyReport() {
    // Implementation for monthly report
}

function toggleQuarterlyReport() {
    // Implementation for quarterly report
}

function toggleYearlyReport() {
    // Implementation for yearly report
}
</script>



</li>
<li><a href="#applicant-communication" onclick="toggleApplicantCommunication()">Chats</a>
    <ul id="communicationSubMenu" style="display: none;">
        <li><a href="#applicant-messages" onclick="toggleApplicantMessages()">Applicant Messages</a></li>
        <li><a href="#coworker-messages" onclick="toggleCoworkerMessages()">Coworker Messages</a></li>
        <li><a href="#manager-messages" onclick="toggleManagerMessages()">Manager Messages</a></li>
        <li><a href="#executive-messages" onclick="toggleExecutiveMessages()">Executive Messages</a></li>
        <li><a href="#send-mail" onclick="toggleSendMail()">Send Mail</a></li>

    </ul>
</li>

<script>
document.querySelector('a[href="#applicant-communication"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('communicationSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleApplicantMessages() {
    // Implementation for applicant messages
}

function toggleCoworkerMessages() {
    // Implementation for coworker messages
}

function toggleManagerMessages() {
    // Implementation for manager messages
}

function toggleExecutiveMessages() {
    // Implementation for executive messages
}
</script>






<li><a href="#onboarding" onclick="toggleOnboarding()">Onboarding</a>


    <ul id="onboardingSubMenu" style="display: none;">
        <li><a href="#add-employee" onclick="toggleAddEmployee()">Add Employee</a></li>
        <li><a href="#edit-employees" onclick="toggleEditEmployees()">Edit Employees</a></li>




        <li><a href="#send-onboard-documents" onclick="toggleSendOnboardDocuments()">Send Onboard Documents</a></li>
    </ul>
</li>

<script>
document.querySelector('a[href="#onboarding"]').addEventListener('click', function(e) {
    e.preventDefault();
    var subMenu = document.getElementById('onboardingSubMenu');
    subMenu.style.display = subMenu.style.display === 'none' ? 'block' : 'none';
});

function toggleOnboarding() {
    // Implementation for onboarding
}

function toggleAddEmployee() {
    // Implementation for add employee
}

function toggleSendOnboardDocuments() {
    // Implementation for send onboard documents
}
</script>





</li>







<li><a href="#settings" onclick="toggleSettings()">Settings</a></li>
<li><a href="#support" onclick="toggleSupport()">Support</a></li>


        <li><a href="logout.php">Logout</a></li> 
            </ul>
        </nav>
        
        <div class="main-content">
        
            <section id="dashboard">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1> Welecome <?php echo $first_name; ?></h1>
                    <button class="customize-widgets" onclick="toggleCustomizer" style="background-color: transparent; padding: 10px; border: none;"> Customize Widgets</button>
                </div>
               
               
               
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h2>Tool Assigned </h2>
                        <p id="total-sub">
                        <?php echo $tool_name; ?></p>
                        </div>
                    <div class="widget">
                        <h2>Active Users</h2>
                        <p id="active-users"><?php echo $user_id; ?></p>
                    </div>
                    <div class="widget">
                        <h2>Inactive Users</h2>
                        <p id="inactive-users"><?php $company_name; ?></p>
                    </div>
                </div>
                
                
            </section>
            
            
            
            
            <section id="talentpool"> 
            
            <h2> Talent Pool </h2>
                            <?php include '../talentlist.php'; ?>

            
            </section>
            
            <section id="tool-management" style="display:none;">
            <h2>Tool Management </h2>
            
            
              <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleAssignTools()"> Assign Tools </div>
            <div class="mode-switch tools" onclick="toggleRevokeTools()"> Revoke Tools </div>


            </div>
            <div class="assigntools" style="display: none">
                                <?php include 'assigntools.php'; ?>

            </div>
            <div class="revoketools" style="display: none">
                                <?php include 'revokefetch.php'; ?>

            </div>

            </section>

            </section>
            <section id="settings" style="display:none;">
            <h1>Settings</h1>
    
            <div class="toolkit-menu">
    
            <div class="mode-switch tools" onclick="toggleDarkModal()"> Themes </div>
            <div class="mode-switch tools" onclick="toggleUpdatePass()"> Update Password </div>
            <div class="mode-switch tools" onclick="toggleUpdatePro()"> Update Username </div>
        
            </div>

            <!-- Dark Mode Toggle -->
            <section class="settings-option" id="darkmodal" style="display:none;">
                <label for="dark-mode-toggle">Dark Mode:</label>
                <input type="checkbox" id="dark-mode-toggle">
            </section>
            
            <!-- Change Password -->
            <section class="settings-option" id="updatepass" style="display:none;">
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
                <section class="settings-option" id="updatepro" style="display:none;">
                    <h2>Update Profile Information</h2>
                        <form action="update_profile.php" method="post">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required><br><br>
                            
                            <label for="email">Email:</label>
                            <input type="email" name="email" required><br><br>
                            
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
            <section id="chat" style="display:none;"></section>
            <section id="subscriptions" style="display:none;">
                <h1>Subscriptions</h1>
                

                        <?php include 'subtable.php'; ?>
            </section>
            <section id="profile" style="display:none;">
            
            <h1>User Profile</h1>

                <?php include 'session.php'; ?>
                  
                <p><strong>ID:</strong> <?php echo $user_id; ?> </p>

            </section>
            <section id="managecompany" style="display:none;">
    
                         <h1>Manage Company</h1>
                            
                            <div class="toolkit-menu">
                            
                            <div class="mode-manage comp" onclick="toggleDept()"> Create Department </div>
                            <div class="mode-manage comp" onclick="toggleManager()"> Assign Managers </div>
                            <div class="mode-manage comp" onclick="toggleEmployee()"> Invite Employees </div>
                        
                            </div>
                            
                        <section class="createdepartment managecomp" id="createdepartment" style="display:none" >
                            <form action ="/mycompany/createdepartment.php" method="POST">
                            <h3>Create New Department</h3>
                            <input type="hidden" name="company_id" value="<?php echo $company_id ?>" hidden> 
                            <label for="new_department">Department Name:</label>
                            <input type="text" id="new_department" name="department_name">
                            <label for="location">Location:</label>        
                            <input type="address" name="location" placeholder="location (optional)"><br> 
                            <input type="submit" value="Create Department">
                            </form>
                        </section>
                    
            
                       <section class="createmanager managecomp" id="createmanager"  style="display:none" >
                            <h3>Create New Manager</h3>
                            <form action="/mycompany/createmanager.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>"> 
                                <label for="new_manager">Manager Name:</label>
                                <input type="text" class="new_manager" name="manager_name" required>
                                <label for="manager_email">Manager Email:</label>
                                <input type="email" id="manager_email" name="manager_email" required>
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
                            </section>
            
                            <section class="createstaff managecomp" id="createstaff"  style="display:none" >
                                <form id="inviteStaffForm" method="POST" action="/mycompany/invitestaff.php">
                                <h3>Staff Member</h3>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                
                                <label for="email">Email:</label>
                                <input type="email" name="email" required>
                
                                <label for="phone">Phone:</label>
                                <input type="tel" id="phone" name="phone">
                
                                <label for="hire_date">Hire Date:</label>
                                <input type="date" id="hire_date" name="hire_date" required>
                
                                <label for="job_title">Job Title:</label>
                                <input type="text" id="job_title" name="job_title">
                
                                <label for="department">Department:</label>
                                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                                    <option value="" disabled selected>Select a department</option>
                                    <?php
                                    // PHP code to fetch departments with managers from the database
                                    include 'db.php';
                                    $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $company_id");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                
                                <input type="hidden" id="employeemanager_id" name="manager_id" required>
                                <input type="hidden" id="employeerole" name="role_name" value="Employee">
                                <input type="hidden" name="description" value="Employee with access to staff functions">
                                <input type="hidden" id="company_id" name="company_id" value="<?php echo $company_id; ?>" required>
                                        <input type="submit" value="Invite Staff">
                
                            </form>
                            </section>
            
                        
                        
                        <script>
                        function setManagerId() {
                            var departmentSelect = document.getElementById("departments");
                            var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                            var managerId = selectedOption.getAttribute("data-manager-id");
                            document.getElementById("manager_id").value = managerId;
                        }
                        
                        </script>

            </section>

        </div>
    <script>
  function toggleDashboard() {
      document.getElementById("dashboard").style.display = "block";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }


  function toggleToolManagement() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "block";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleManageCompany() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "block";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleProfile() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "block";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSubscriptions() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "block";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleChat() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "block";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "none";
  }

  function toggleSettings() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "block";
      document.getElementById("support").style.display = "none";
  }

  function toggleSupport() {
      document.getElementById("dashboard").style.display = "none";
      document.getElementById("tool-management").style.display = "none";
      document.getElementById("managecompany").style.display = "none";
      document.getElementById("profile").style.display = "none";
      document.getElementById("subscriptions").style.display = "none";
      document.getElementById("chat").style.display = "none";
      document.getElementById("settings").style.display = "none";
      document.getElementById("support").style.display = "block";
  }
  </script>
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
    
    
    <script>
     function toggleAssignTools() {
      document.querySelector(".assigntools").style.display = "block";
      document.querySelector(".revoketools").style.display = "none";
     
  }
  
      function toggleRevokeTools() {
      document.querySelector(".assigntools").style.display = "none";
      document.querySelector(".revoketools").style.display = "block";
     
  }
  
  </script>
    
    
    <script src="showsettings.js"></script>
    <script src="settings.js"></script>
    <script src="managecompany.js"></script>
</body>
</html>
