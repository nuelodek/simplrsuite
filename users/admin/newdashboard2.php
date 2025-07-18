<?php

include '../../sql_queries/db.php';
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Check if companyId is set in the session
if (!isset($_SESSION['company_id'])) {
    // Redirect to login.html if companyId is not found
    header("Location: login.html");
    exit();
} 

$userId = $_SESSION['user_id'];
$email = $_SESSION['email'];
$companyName = $_SESSION['company_name'];
$industry = $_SESSION['industry'];
$companyId = $_SESSION['company_id'];
$status = $_SESSION['status'];


// Check if the user's profile is completed
$completedprofile = ""; // Declare the variable outside the loop

$sql = "SELECT completed_profile FROM users WHERE company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $completedprofile = $row['completed_profile'];
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Simplr for Business </title>
        <link rel="icon" type="image/png" href="/simple-social-svgrepo-com.png">  
<link rel="stylesheet" href="/public/styles/nicetable.css">
<script
  src="https://js.sentry-cdn.com/bd57a327b8c04ca2bc2907391b50147a.min.js"
  crossorigin="anonymous"
></script>

    <script src="https://kit.fontawesome.com/a75a719a54.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
    <link rel="stylesheet" href="../../public/styles/profilestyles.css">
    <link rel="stylesheet" href="../../public/styles/button.css">

<style>
    .messagemodal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.6);
            z-index: 1000;
        }

        .messagemodal-content {
            background-color: #ffffff;
            margin: 5% auto;
            padding: 25px;
            width: 95%;
            max-width: none;
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .messagemodal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .messagemodal-header h2 {
            margin: 0;
            color: #333;
        }

        .close {
            cursor: pointer;
            font-size: 28px;
            color: #777;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #333;
        }

        .messagemodal-body {
            display: flex;
            justify-content: space-between;
            height: 400px;
        }

        /* Left Panel */
        .left-panel {
            width: 20%;
            padding: 15px;
            border-radius: 8px;
            overflow-y: auto;
            max-height: 400px;
            font-size: 0.8em;
        }

        .left-panel h3 {
            margin-top: 0;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .left-panel h3 .fa-plus {
            cursor: pointer;
            color: #007bff;
        }

        .left-panel ul {
            list-style-type: none;
            padding: 0;
        }

        .left-panel ul li {
            margin: 12px 0;
        }

        .left-panel ul li a {
            text-decoration: none;
            color: #4a4a4a;
            transition: color 0.3s ease;
        }

        .left-panel ul li a:hover {
            color: #007bff;
        }

        .left-panel .category {
            margin-top: 20px;
        }

        .left-panel .category h4 {
            margin-bottom: 10px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .left-panel .category h4 .fa-plus {
            cursor: pointer;
            color: #007bff;
        }

        /* Middle Section */
        .message-section {
            width: 50%;
            font-size: 0.9em;
            display: flex;
            flex-direction: column;
            padding: 15px;
            overflow-y: auto;
            max-height: 500px;
        }

        .message-section h3 {
            margin-top: 0;
            color: #333;
        }

        #messages {
            flex-grow: 1;
            padding: 15px;
            margin-bottom: 15px;
            overflow-y: auto;
            max-height: 250px;
            border-radius: 8px;
            background-color: #fff;
            color: #808080;
        }

        #messageInput {
            padding: 12px;
            width: calc(100% - 24px);
            margin-bottom: 15px;
            resize: vertical;
            max-height: 100px;
            border: none;
            outline: none;
        }

        .message-icons {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 4px;
            background-color: transparent;
        }

        .message-icons i {
            font-size: 20px;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s ease;
            margin-right: 15px;
        }

        .message-icons i:hover {
            color: #007bff;
        }

        .message-icons .fa-paper-plane {
            margin-left: auto;
            color: #007bff;
        }

        /* Right Panel */
        .right-panel {
            width: 20%;
            padding: 15px;
            border-radius: 8px;
            overflow-y: auto;
            max-height: 400px;
            font-size: 0.9em;
        }

        .right-panel h3 {
            margin-top: 0;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .right-panel h3 .fa-plus {
            cursor: pointer;
            color: #007bff;
        }

        .right-panel ul {
            list-style-type: none;
            padding: 0;
        }

        .right-panel ul li {
            margin: 12px 0;
            color: #4a4a4a;
        }

        #openModalBtn {
            margin: 20px;
            font-size: 16px;
        }

        .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .profile-section {
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9em;
        }

        .profile-section h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #333;
            font-size: 1.1em;
        }

        .profile-section ul {
            list-style-type: none;
            padding: 0;
        }

        .profile-section ul li {
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .profile-section .tag {
            display: inline-block;
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75em;
            color: #495057;
        }

        .profile-picture {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .profile-tabs {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .profile-tabs a {
            text-decoration: none;
            color: #333;
            padding: 3px 6px;
            border-radius: 5px;
            font-size: 0.8em;
        }

        .profile-tabs a:hover {
            background-color: #f0f0f0;
        }

        .profile-tabs a.active {
            background-color: #007bff;
            color: #fff;
        }

        /* Transparent scrollbar styles */
        .left-panel, .message-section, .right-panel, #messages {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        }

        .left-panel::-webkit-scrollbar,
        .message-section::-webkit-scrollbar,
        .right-panel::-webkit-scrollbar,
        #messages::-webkit-scrollbar {
            width: 6px;
        }

        .left-panel::-webkit-scrollbar-track,
        .message-section::-webkit-scrollbar-track,
        .right-panel::-webkit-scrollbar-track,
        #messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .left-panel::-webkit-scrollbar-thumb,
        .message-section::-webkit-scrollbar-thumb,
        .right-panel::-webkit-scrollbar-thumb,
        #messages::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
    </style>
    </head>
<body>
    
    <div class="main-content">
    <div class="content">
    <div class="sidebar">
    <div class="logo" style="width:100px;" ></div>
    <div class="menulist" style="font-size: 12px;"> 
    
    <ul class="menu"> 
    
     <div style="display: flex; align-items: center; margin-left: 0px; margin-top: 20px; margin-bottom: 10px; color: black;" >
        <img src="../../../../simple-social-svgrepo-com-removebg-preview.png" style="width: 25px; height: 25px; background-color: none; border-radius: 5px;">
        <div style="display: flex; flex-direction: column; margin-left: 10px;">
            <span style="color: ; font-weight: bold;"> <?php echo $companyName; ?></span>
            <span style="color: ; font-size: 8px;"> Company ID: <?php echo $companyId; ?> </span>
          </div>
     </div>
        <li onclick="toggleLearn()"> <img src="https://www.svgrepo.com/show/501525/dashboard.svg" alt="Learn Icon">Dashboard <span><small style="background-color: #FFD700;">5 alerts</small></span></li>
        <li onclick="openEmployeeModal()"><img src="https://www.svgrepo.com/show/532824/folder-plus.svg" alt="Categories Icon"> Manage Company   
        <!--<img src="https://www.svgrepo.com/show/465697/next.svg" class="arrowbutton" alt="Next Arrow">--> </li>
        <li onclick="openNotificationsModal()"><img src="https://www.svgrepo.com/show/460025/notification-alt.svg" alt="Notifications Icon">Notifications <span><small style="background-color: #90EE90;">10 new</small></span></li>
        <li onclick="openMessagesModal()"> <img src="https://www.svgrepo.com/show/522602/messages.svg" alt="Message Icon"> Messages <span><small style="background-color: #87CEFA;">100+ unread</small></span></li></li>
        <li onclick="openSupportModal()"><img src="https://www.svgrepo.com/show/503592/support.svg" alt="Wallet Icon">Support <span><small style="background-color: #FFA07A;">1 ticket open</small></span></li>
        <li onclick="openBankModal()"><img src="https://www.svgrepo.com/show/521497/bank.svg" alt="Support Icon">Bank & Wallets <small style="background-color: #DDA0DD;"> Credits - 2 <br> Debits - 12 </small></span></li>              
        <li onclick="openAnalyticsModal()"><img src="https://www.svgrepo.com/show/453406/reports.svg" alt="Support Icon">Analytics <small style="background-color: #20B2AA;"> 12 new </small></span></li>              
                  <li onclick="toggleLeftNavigation()">
                      <img src="https://www.svgrepo.com/show/488643/options.svg" alt="Support Icon">More 
                      <img src="https://www.svgrepo.com/show/465697/next.svg" class="arrowbutton" alt="Next Arrow" onclick="toggleLeftNavigation(event)">
                  </li>
        
                  <script>
                  function toggleLeftNavigation(event) {
                      if (event) {
                          event.stopPropagation();
                      }
                      var leftNav = document.getElementById('leftNavigation');
                      if (leftNav.style.width === '250px') {
                          leftNav.style.width = '0';
                      } else {
                          leftNav.style.width = '200px';
                      }
                  }
                  </script>
        
                  <div id="leftNavigation" style="height: 300px; width: 0; position: fixed; z-index: 9999; top: 200px; left: 250px; background-color: #fff; overflow-x: hidden; transition: 0.5s; padding-top: 20px; border-radius: 5px;">
                      <a href="javascript:void(0)" class="closebtn" onclick="toggleLeftNavigation()" style="border-radius: 50%; position: absolute; right: 10px; top: 10px; text-decoration: none;">×</a>
                      <ul style="border-radius: 10px; list-style-type: none; padding-left: 0;">
                          <li style="margin-bottom: 5px;"><a href="#" style="text-decoration: none; margin-left: 30px;">Company Settings</a></li>
                          <li style="margin-bottom: 5px;"><a href="#" style="text-decoration: none; margin-left: 30px;">User Management</a></li>
                          <li style="margin-bottom: 5px;"><a href="#" style="text-decoration: none; margin-left: 30px;">Billing & Invoices</a></li>
                          <li style="margin-bottom: 5px;"><a href="#" style="text-decoration: none; margin-left: 30px;">Security & Compliance</a></li>
                          <li style="margin-bottom: 5px;"><a href="#" style="text-decoration: none; margin-left: 30px;">Data Backup & Recovery</a></li>
                          <li style="margin-bottom: 5px;"><a href="#" style="text-decoration: none; margin-left: 30px;">Help & Documentation</a></li>
                          <li style="margin-bottom: 5px;"><a href="#" style="text-decoration: none; margin-left: 30px;">Notification Preferences</a></li>
                        </ul>                  </div>
                    
                </ul>
       
    </div>
    <div style="margin-top: 20px;">
    <ul class="menu">
                        <li onclick="openSettingsModal()"><img src="https://www.svgrepo.com/show/524954/settings.svg" alt="Support Icon">Settings </li>
<li onclick="window.location.href='logout.php'"><img src="https://www.svgrepo.com/show/470849/logout.svg" alt="Support Icon">Logout</li>
    
    </ul>
    </div>
    </div>
      
    </div>
    <div class="sectionq">

<header> 
        <!--<div class="logo menulog">-->
             

<div class="menu-icons">
  <a href="#" title="Home" style="position: relative;">
    <img src="https://www.svgrepo.com/show/524649/home-1.svg">
    <span style="position: absolute; background-color: yellow; padding: 5px; border-radius: 3px; top: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; display: none;">Home</span>
  </a>
  <a href="#" class="language-switch change-language-button" title="Languages" style="position: relative;">
    <img class="imageflag" src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/79/Flag_of_Nigeria.svg/2000px-Flag_of_Nigeria.svg.png">
    <span style="position: absolute; background-color: yellow; padding: 5px; border-radius: 3px; top: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; display: none;">Languages</span>
  </a>
  <a href="#" title="Vault" style="position: relative;" onclick="openVaultModal()">
    <img src="https://www.svgrepo.com/show/310190/vault.svg">
    <span style="position: absolute; background-color: yellow; padding: 5px; border-radius: 3px; top: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; display: none;">Vault</span>
  </a>
</div>
<style>
.menu-icons a:hover span {
  display: block;
}
</style>
        <div class="search-bar">
           
            <input type="text" placeholder="Global Search...">
        </div>
        <div class="menu-icons">
            <a href="#"><img src="https://www.svgrepo.com/show/458734/group-share.svg"></a>
            <a href="#"><img src="https://www.svgrepo.com/show/487479/invoice.svg"></a>
            <a href="#"><img src="https://www.svgrepo.com/show/533396/calendar-lines-pen.svg"></a>
            <a href="#"><img src="https://www.svgrepo.com/show/526839/calendar-search.svg"></a>
            <a href="#"><img src="https://www.svgrepo.com/show/330019/azurefunctions.svg" onclick="openProjectModal()"></a>
            <a href="#"><img src="https://www.svgrepo.com/show/442334/preferences-desktop-online-accounts-symbolic.svg" onclick="openCloudModal()"></a>
            
<a href="#" style="background-color: black; border-radius: 5px; color: white; padding: 10px; font-size: 13px;">Trial expires in 48 hours</a>
        </div>
        <img src="simple-social-svgrepo-com.png" alt="Profile Picture" class="profile-pic">
</header>
<div class="idontknow" style="display: flex; justify-content: space-between; align-items: center; margin: 10px 20px; background-color: #f0f0f0; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
<div style="text-align: left;">
    <a href="#" onclick="openUpdatesModal()"      class="updates-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Updates (2)</a>
    <a href="#" onclick="openReportsModal()"       class="reports-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Reports (7)</a>
    <!-- <a href="#" onclick="openTalentModal()"       class="employees-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Talents (2)</a> -->
    <a href="#" onclick="openAppIntegrationModal()"          class="connect-apps-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Apps (10+)</a>
    <a href="#" onclick="openKpiModal()"          class="kpi-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">KPIs</a>
    <a href="#" onclick="openActivityLogsModal()" class="activitylogs-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Logs</a>
    <a href="#" class="alerts-link" style="font-size: 14px; color: #ff0000; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Alerts (19+)</a>
</div>

<div style="text-align: right;">

<!-- saved for later -->
    <!-- <a href="#" onclick="openProjectModal()" class="create-task-button" style="font-size: 14px; color: #fff; background-color: #007bff; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-right: 15px;">Create Project +</a>
    <a href="#" onclick="openCreateTaskModal()" class="create-task-button" style="font-size: 14px; color: #fff; background-color: #007bff; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-right: 15px;">Create Task +</a>
     -->
    
    <div class="dropdown-widgets" style="display: inline-block; position: relative;">
        <a href="#" class="dropdown-toggle-widgets" style="font-size: 13px; color: #333; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">
            Add Widgets <span style="font-size: 16px;">▾</span>
        </a>
        <div class="dropdown-menu-widgets" style="display: none; position: absolute; background-color: #f9f9f9; min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; border-radius: 5px; padding: 10px; text-align: left; top: 100%; left: 0;">
           
            <?php include '../admin/getwidgets.php'?>
        </div>
    </div>

    <script>
        document.querySelector('.dropdown-toggle-widgets').addEventListener('click', function(e) {
            e.preventDefault();
            var dropdownMenu = this.nextElementSibling;
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        function applyWidgets() {
            var selectedWidgets = Array.from(document.querySelectorAll('input[name="widget"]:checked')).map(cb => cb.value);
            console.log('Applied widgets:', selectedWidgets);
            // Add your logic here to apply the selected widgets
        }
    </script>
    <div class="dropdown" style="display: inline-block; position: relative;">
        <a href="#" class="dropdown-toggle" style="font-size: 14px; color: #333; text-decoration: none; font-weight: bold;">
            Create ▾
        </a>
        <div class="dropdown-menu" style="display: none; position: absolute; background-color: #f9f9f9; min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; top: 100%; right: 0; border-radius: 5px;">
            <a href="#" onclick="openProjectModal()" style="color: black; padding: 8px 12px; text-decoration: none; display: block; font-size: 12px;">Create Project</a>
            <a href="#" onclick="openCreateTaskModal()" style="color: black; padding: 8px 12px; text-decoration: none; display: block; font-size: 12px;">Create Task</a>
            <a href="#" onclick="openWidgetModal()" style="color: black; padding: 8px 12px; text-decoration: none; display: block; font-size: 12px;">Customize Widgets</a>
            <a href="#" onclick="createJot()" style="color: black; padding: 8px 12px; text-decoration: none; display: block; font-size: 12px;">Create Jots</a>
        </div>
    </div>

    <script>
        document.querySelector('.dropdown-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            var dropdownMenu = this.nextElementSibling;
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        function createJot() {
            // Add functionality for creating jots
            console.log('Create Jot clicked');
        }
    </script>    
    <style>
        .dropdown-menu a:hover {
            background-color: #f1f1f1;
        }
    </style>
</div>
</div>


<?php if ($completedprofile == "0") : ?>

  <?php include 'onboardingflow.php'; ?>

<?php endif; ?>

<?php if ($completedprofile == "1") : ?>

  <?php include 'projectflow.php'; ?>

<?php endif; ?>




<style>
.progress-container {
  width: 100%;
  font-size: 11px;
  background-color: black;

  border-radius: 5px;
  overflow: hidden;
}

.progress-fill {
  height: 30px;

  background-color: rgba(255, 152, 0, 0.5);
  transition: width 0.5s ease-in-out;
}

.progress-text {
  color: white;
  display: flex;
  margin-left: 10px;

   align-items: center;
  /* justify-content: center;  */
  height: 100%;
      white-space: nowrap;

  font-weight: bold;
}
</style>


<div class=""></div>

  <div class="widgets-container">
        <div class="widget">
    <div class="widget-content">
        <div class="widget-text">
            <span class="widget-number">
           
           
           <?php
include '../../sql_queries/db.php';

$sql = "SELECT * FROM toolkit_subscriptions WHERE company_id='$companyId'";
$result = $conn->query($sql);

$active_subscriptions = 0;
$tools = ['payroll', 'recruiting', 'crm', 'cybersecurity', 'project', 'strategy', 'risk', 'sales', 'product', 'office'];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        foreach ($tools as $tool) {
            $expiry_date = strtotime($row[$tool . '_expireddate']);
            if ($expiry_date > time()) {
                $active_subscriptions++;
            }
        }
    }
}

echo "<span style=\"padding-left: 0px; text-indent: 0px;\">\n    ";
echo $active_subscriptions;
echo "\n\n</span>";

$conn->close();
?>


                
            </span>
            
            
            
            
            <span class="widget-title">Subscriptions</span>
<small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
<?php
include '../../sql_queries/db.php';

$sql = "SELECT * FROM toolkit_subscriptions WHERE company_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $companyId);
$stmt->execute();
$result = $stmt->get_result();

$subscriptions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subscriptions[] = [
            'id' => $row['id'],
            'email' => $row['email'],
            'payroll_months' => $row['payroll_months'],
            'payroll_startdate' => $row['payroll_startdate'],
            'payroll_expireddate' => $row['payroll_expireddate'],
            'recruiting_months' => $row['recruiting_months'],
            'recruiting_startdate' => $row['recruiting_startdate'],
            'recruiting_expireddate' => $row['recruiting_expireddate'],
            'crm_months' => $row['crm_months'],
            'crm_startdate' => $row['crm_startdate'],
            'crm_expireddate' => $row['crm_expireddate'],
            'cybersecurity_months' => $row['cybersecurity_months'],
            'cybersecurity_startdate' => $row['cybersecurity_startdate'],
            'cybersecurity_expireddate' => $row['cybersecurity_expireddate'],
            'project_months' => $row['project_months'],
            'project_startdate' => $row['project_startdate'],
            'project_expireddate' => $row['project_expireddate'],
            'strategy_months' => $row['strategy_months'],
            'strategy_startdate' => $row['strategy_startdate'],
            'strategy_expireddate' => $row['strategy_expireddate'],
            'risk_months' => $row['risk_months'],
            'risk_startdate' => $row['risk_startdate'],
            'risk_expireddate' => $row['risk_expireddate'],
            'sales_months' => $row['sales_months'],
            'sales_startdate' => $row['sales_startdate'],
            'sales_expireddate' => $row['sales_expireddate'],
            'product_months' => $row['product_months'],
            'product_startdate' => $row['product_startdate'],
            'product_expireddate' => $row['product_expireddate'],
            'office_months' => $row['office_months'],
            'office_startdate' => $row['office_startdate'],
            'office_expireddate' => $row['office_expireddate'],
            'created_at' => $row['created_at']
        ];
    }
} else {
    echo "You have no subscriptions. Kindly subscribe.";
    exit;
}

$conn->close();

$tools = ['payroll', 'recruiting', 'crm', 'cybersecurity', 'project', 'strategy', 'risk', 'sales', 'product', 'office'];
$expiring_tools = [];

foreach ($subscriptions as $subscription) {
    foreach ($tools as $tool) {
        $expiry_date = strtotime($subscription[$tool . '_expireddate']);
        $days_until_expiry = ceil(($expiry_date - time()) / (60 * 60 * 24));
        
        if ($days_until_expiry <= 0) {
            $expiring_tools[] = ucfirst($tool) . " tool has expired. Renew now";
        } elseif ($days_until_expiry <= 30) {
            $expiring_tools[] = ucfirst($tool) . " tool due to expire in {$days_until_expiry} days. Renew now";
        }
    }
}

echo "<span style=\"padding-left: 0px; text-indent: 0px;\">\n    \n";
if (!empty($expiring_tools)) {
    $random_expiring_tool = $expiring_tools[array_rand($expiring_tools)];
    echo $random_expiring_tool . "\n";
} else {
    $random_tool = $tools[array_rand($tools)];
    $random_days = rand(1, 30);
    echo ucfirst($random_tool) . " tool due to expire in {$random_days} days. Renew now\n";
}
echo "\n\n</span>";
?>

</small>



        </div>
        
        <img src="https://www.svgrepo.com/show/503466/paywall.svg" alt="Subscription Icon" class="widget-icon1 widget-icon" onclick="openSubscriptionsModal()">
        

    </div>
    
</div>
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text">
                                    <span class="widget-number">
<?php
include '../../sql_queries/db.php';

$current_date = date('Y-m-d');
$sql = "SELECT COUNT(*) as event_count FROM events WHERE DATE(created_at) = '$current_date' AND company_id = '$companyId'";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
                    echo $row['event_count'];
} else {
                    echo "0";
}
?>
</span>
                    <span class="widget-title"> Events </span>
                  <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">Dev. Team meetings starting in 3 mins. Join now</span>
</small>

                </div>
                <img src="https://www.svgrepo.com/show/446859/events.svg" alt="Active Users Icon" class="widget-icon2 widget-icon" onclick=openEventsModal()>
            </div>
        </div>
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text">
<?php
include '../../sql_queries/db.php';
$companyId = $_SESSION['company_id'];
$query = "SELECT COUNT(*) as employee_count FROM employees WHERE company_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $companyId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$employee_count = $row['employee_count'];
$stmt->close();
?>
<span class="widget-number"><?php echo number_format($employee_count); ?></span>
<span class="widget-title">Employees</span>
                    
<small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">
<?php
include '../../sql_queries/db.php';

$sql = "SELECT ma.logout_time, 
        CASE 
            WHEN ma.user_type = 'manager' THEN m.manager_name
            WHEN ma.user_type = 'employee' THEN CONCAT(e.first_name, ' ', e.last_name)
        END AS user_name
        FROM (
            SELECT manager_id AS id, logout_time, 'manager' AS user_type
            FROM manager_auth
            WHERE company_id = '$companyId' AND logout_time IS NOT NULL
            UNION ALL
            SELECT employee_id, logout_time, 'employee'
            FROM employees_auth
            WHERE company_id = '$companyId' AND logout_time IS NOT NULL
        ) ma
        LEFT JOIN managers m ON ma.id = m.id AND ma.user_type = 'manager'
        LEFT JOIN employees e ON ma.id = e.id AND ma.user_type = 'employee'
        ORDER BY ma.logout_time DESC
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastLogout = new DateTime($row['logout_time']);
    $userName = $row['user_name'];
    echo "Last logout: " . $userName . " at " . $lastLogout->format('g:ia') . " today";
} else {
    echo "No recent logout information available";
}
?>


  </small>
                </div>
                <img src="https://www.svgrepo.com/show/146754/user-list.svg" alt="Inactive Users Icon" class="widget-icon3 widget-icon"   onclick="openActiveUsersModal()">
            </div>
        </div> 
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text">
                    <span class="widget-number">2</span>
                    <span class="widget-title">Automations</span>
                                     
<small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">     2 finance automations completed their task. View Results</span>
</small>


                </div>
             
              
                <img src="https://www.svgrepo.com/show/390204/automation-marketing-launch-rocket-laptop.svg" alt="Task Overview Icon" class="widget-icon4 widget-icon" onclick="openAutomationsModal();">
            </div>
            
        </div>
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text">
                    <span class="widget-number">34</span>
                    <span class="widget-title">Workflows</span>
                    <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">     2900 tasks completed in the last 7 days, 10 still pending</span>
</small>
                  
                </div>
                <img src="https://www.svgrepo.com/show/360836/task-list-f.svg" alt="Notification Icon" class="widget-icon5 widget-icon" onclick="openWorkflowsModal();">
            </div>
        </div>
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text">
                    <span class="widget-number">3,040</span>
                    <span class="widget-title">Mentions</span>
                    <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">    Emmanuel O. and Jeffery T. just mentioned you </span>
</small>
                   
                </div>
                <img src="https://www.svgrepo.com/show/310585/calendar-mention.svg" alt="Highlights Icon" class="widget-icon6 widget-icon" onclick="openMentionsModal();">
            </div>
        </div>
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text">
                    <span class="widget-number">18</span>
                    <span class="widget-title">Intelligence</span>
                    
                    <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">     18 neurons deployed and gathering data on your company processes </span>
</small>
                  
                </div>
            <img src="https://www.svgrepo.com/show/482780/brain-3.svg" alt="Goal Tracker Icon" class="widget-icon7 widget-icon" onclick="openSIntelligenceModal();">
            </div>
        </div>
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text">
                    <span class="widget-number">12</span>
                    <span class="widget-title">Insights</span>
                    
                      <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">     Emmanuel O. suggested you integrate Github app and Simplr Project Mgt. tool </span>
</small>
                </div>
                <img src="https://www.svgrepo.com/show/335413/insights-portal.svg" alt="AI-Powered Insights Icon" class="widget-icon8 widget-icon" onclick="openInsightsModal()">
            </div>
        </div>
        <div class="widget">
            <div class="widget-content">
                <div class="widget-text"> 
                    <span class="widget-number">

                    <?php
include '../../sql_queries/db.php';

$current_date = date('Y-m-d');
$sql = "SELECT COUNT(*) as team_count FROM teams WHERE DATE(created_at) = '$current_date' AND company_id = '$companyId' AND team_status = 'active'";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
                    echo $row['team_count'];
} else {
                    echo "0";
}
?>
</span>



                    </span>
                    <span class="widget-title">Teams</span>
                     
                      <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
    <span style="padding-left: 0px; text-indent: 0px;">      Your Product team and Operations team just hit a milestone. Celebrate them. </span>
</small>
                    
                </div>
                <img src="https://www.svgrepo.com/show/458732/group.svg" alt="Communication Hub Icon" class="widget-icon9 widget-icon" onclick="openTeamsModal();">
            </div>
        </div>
    </div>


</div>

<div id="employeeModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Manage Company</h2>
    </div>
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'CreateDepartments')">Create Departments</button>
      <button class="tablinks" onclick="openTab(event, 'CreateManagers')">Create Managers</button>
      <button class="tablinks" onclick="openTab(event, 'AddEmployees')">Add Employees</button>
      <button class="tablinks" onclick="openTab(event, 'ViewEmployees')">View Employees</button>
      <button class="tablinks" onclick="openTab(event, 'ViewDepartments')">View Departments <sup style="font-size: 0.5em; background-color: red; color: white; padding: 2px 4px; border-radius: 10px; margin-left: 5px;">pending</sup></button>
      <button class="tablinks" onclick="openTab(event, 'ViewManagers')">View Managers  <sup style="font-size: 0.5em; background-color: red; color: white; padding: 2px 4px; border-radius: 10px; margin-left: 5px;">pending</sup></button>
      <button class="tablinks" onclick="openTab(event, 'ApproveEmployees')">Approve Employees <sup style="font-size: 0.5em; background-color: red; color: white; padding: 2px 4px; border-radius: 10px; margin-left: 5px;">pending</sup></button>

    </div>

    <div id="CreateDepartments" class="tabcontent">

         <form action ="/mycompany/createdepartment.php" method="POST" class="customizeforms">
                    <input type="hidden" name="company_id" value="<?php echo $companyId ?>" hidde> 
                    
                    <div class="input-container">
                    <input type="text" id="new_department" name="department_name" placeholder=" " required>
                        <label for="new_department">Department Name:</label>
                    </div>
                    
                    <div class="input-container">

                    <input type="text" name="location" placeholder=" ">
                    

                    <label for="location">Location(optional):</label> 
                    </div>
                    
                    <input type="submit" value="Create Department">
                    </form>


    </div>

    <div id="CreateManagers" class="tabcontent" >
      <form action="../admin/createmanager.php" method="POST" class="customizeforms">
                        <input type="hidden" name="company_id" value="<?php echo $companyId; ?>"> 
                        

                      <div class="input-container">
                        <select name="selectemployee_id" id="selectemployee_id" onchange="populateManagerInfo()">
                        <option value='' disabled selected>Select manager</option>

                         <?php
                          include '../../sql_queries/db.php';
                            
                          $stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM employees WHERE company_id = ? AND role_id != 2");
                          $stmt->bind_param("i", $companyId);
                          $stmt->execute();
                          $result = $stmt->get_result();
                          while ($row = $result->fetch_assoc()) {

                            echo "<option value='" . $row['id'] . "' data-email='" . $row['email'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "</option>";
                          }
                          $stmt->close();
                          ?>
                        </select>
                      </div>
                    
                      <div class="input-container">
                          <input type="text" class="new_manager" name="manager_name" id="manager_name" placeholder=" " required readonly>
                          <label for="new_manager">Manager Name:</label>
                      </div>
                      <div class="input-container">
                          <input type="email" id="manager_email" name="manager_email" placeholder="" required readonly>
                          <label for="manager_email">Manager Email:</label>
                      </div>

                      <script>
                      function populateManagerInfo() {
                          var select = document.getElementById('selectemployee_id');
                          var selectedOption = select.options[select.selectedIndex];
                          document.getElementById('manager_name').value = selectedOption.text;
                          document.getElementById('manager_email').value = selectedOption.getAttribute('data-email');
                      }
                      </script>
                                            <div class="input-container">

                        <select name="department_id" required>
                            <option value="" disabled selected>Select a department</option>
                            <?php
                            // PHP code to fetch departments from the database and populate the select options
                            include '../../sql_queries/db.php';

                            $result = $conn->query("SELECT id, department_name FROM departments WHERE company_id = $companyId AND manager_id IS NULL");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value=\"" . $row['id'] . "\">" . $row['department_name'] . "</option>";
                            }
                            ?>   
                        </select>
                                                <label for="department_id">Department:</label>

                        </div>
                        <input type="hidden" id="role" name="role_name" value="Manager">
                        <input type="hidden" name="description" value="Manager with access to managerial functions">
                        <input type="submit" value="Create Manager">
                    </form>
    </div>

    <div id="AddEmployees" class="tabcontent">
       <form id="inviteStaffForm" method="POST" action="../admin/invitestaff.php" class="customizeforms">
                    
        <div class="input-container">

<input type="text" id="first_name" name="first_name" placeholder="" required autocomplete="given-name">
        <label for="first_name">First Name:</label>

        </div>
    
        <div class="input-container">

        <input type="text" id="last_name" name="last_name" placeholder="" required autocomplete="given-name">
        <label for="last_name">Last Name:</label>


        </div>
    
        <div class="input-container">

        <input type="email" name="email" placeholder="" required autocomplete="given-name">
        <label for="email">Email:</label>

        </div>
    
        <div class="input-container">

                    <input type="tel" id="staffphone" name="staffphone" placeholder="">
                                        <label for="phone">Staff Phone:</label>

        </div>
   
         <div class="input-container">
    
                    <input type="date" id="hire_date"  placeholder="" name="hire_date" required>
                                        <label for="hire_date">Hire Date:</label>

        </div>
    
        <div class="input-container">

                    <input type="text" id="job_title" placeholder="" name="job_title">
                                        <label for="job_title">Job Title:</label>

        </div>

        <div class="input-container">
        
                    <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                        <option value="" disabled selected>Select a department</option>
                        <?php
                        // PHP code to fetch departments with managers from the database
                        include '../../sql_queries/db.php';

                        $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $companyId");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                        }
                        ?>
                    </select>
                                        <label for="department">Department:</label>

        </div>
                    <input type="hidden" id="employeemanager_id" name="manager_id" required>
                    <input type="hidden" id="employeerole" name="role_name" value="Employee">
                    <input type="hidden" name="description" value="Employee with access to staff functions">
                    <input type="hidden" id="company_id" name="company_id" value="<?php echo $companyId; ?>" required>
                            <input type="submit" value="Invite Staff">
    
                </form>
    </div>

    <div id="ViewEmployees" class="tabcontent">
      <!--<h3>View Employees</h3>-->
                    <?php include '../../users/admin/viewemployees.php'; ?> 
    </div>
  </div>
</div>
<div id="appIntegrationModal" class="modal" style="display:none;">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close">×</span>
        <h2>App Integrations</h2>
      </div>
    <div class="tab">
        <button class="tablinks" onclick="openTab(event, 'Finance')">Finance & Payroll (5)</button>
        <button class="tablinks" onclick="openTab(event, 'Development')">Product Dev. (3) <sup style="font-size: 0.5em; background-color: red; color: white; padding: 2px 4px; border-radius: 10px; margin-left: 5px;">+3 new</sup></button></button>
        <button class="tablinks" onclick="openTab(event, 'RiskMgt')">Risk Mgt (2)</button>
        <button class="tablinks" onclick="openTab(event, 'ProjectManagement')">Project Mgt (4)</button>
        <button class="tablinks" onclick="openTab(event, 'HumanResources')">Recruitment (3)</button>
        <button class="tablinks" onclick="openTab(event, 'CustomerRelations')">CRM (2)</button>
        <button class="tablinks" onclick="openTab(event, 'InventoryManagement')">Inventory Mgt (1)</button>
        <button class="tablinks" onclick="openTab(event, 'Marketing')">Marketing (3)</button>
        <button class="tablinks" onclick="openTab(event, 'Analytics')">Strategy & Modelling (4)</button>
        <button class="tablinks" onclick="openTab(event, 'Communication')">Office Mgt (2)</button>
        <button class="tablinks" onclick="openTab(event, 'Research')">Cyber Security (1)</button>
        <!--<button class="tablinks new-tool" onclick="openTab(event, 'NewTool')">New Tool (1) <sup style="font-size: 0.5em; background-color: red; color: white; padding: 2px 4px; border-radius: 10px; margin-left: 5px;">3 new</sup></button>-->
      </div>

<style>
    
    
    /*.tool-logo {*/
    /*    width: 35px;*/
    /*    height: 35px;*/
    /*    border-radius: 5px;*/
    /*    margin-right: 10px;"*/
    /*}*/
    
    /*.btn {*/
    /*   padding: 5px 20px; font-size: 12px; background-color: #635BFF; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;"*/
    /*}*/
    
    /*.tool-item {*/
    /*    display: flex; */
    /*    align-items: center;*/
    /*    margin-bottom: 5px;*/
    /*}*/
  .tool-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 columns */
    gap: 16px; /* Space between items */
    margin: 0;
    padding: 0;
    list-style: none;
}

.tool-item {
    display: flex;
    flex-direction: column;
    align-items: center; /* Center-aligns the items */
    text-align: center;
}

.tool-logo {
    width: 50px; /* Adjust this value as needed */
    height: 50px; /* Maintain aspect ratio */
    margin-bottom: 8px;
    border-radius: 10px;
    /*border: 1px solid pink;*/
    padding: 2px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adds a subtle shadow */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transition */
}

.tool-logo:hover {
    transform: scale(1.1); /* Slightly enlarges the element on hover */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Darker shadow on hover */
}


.btn {
    padding: 8px 16px;
    /*display:none;*/
    font-size: 14px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background-color: grey; /* Bootstrap primary color */
    color: white;
    border-radius: 4px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .tool-list {
        grid-template-columns: repeat(3, 1fr); /* 3 columns for medium screens */
    }
}

@media (max-width: 900px) {
    .tool-list {
        grid-template-columns: repeat(2, 1fr); /* 2 columns for small screens */
    }
}

@media (max-width: 600px) {
    .tool-list {
        grid-template-columns: 1fr; /* 1 column for extra-small screens */
    }
}

</style>

      <div id="Finance" class="tabcontent">
        <h3>Finance Tools</h3>
        <div class="tool-list">
         <div class="tool-item">
            <img src="integratedimg/image_processing20210904-7932-1eyochb.gif" alt="Stripe Logo" class="tool-logo stripelogo">
            <button onclick="showIntegrationSteps('Stripe')" class="btn btn-primary" >Integrate Stripe</button>
</div>
          <div class="tool-item">
            <img src="integratedimg/Plaid_(company)-Vertical-White-Dark-Background-Logo.wine.svg" alt="Plaid Logo" class="tool-logo plaidlogo">
            <button onclick="showIntegrationSteps('Plaid')" class="btn btn-primary">Integrate Plaid</button>
          </div>
         
          <div class="tool-item">
          <img src="integratedimg/quickbooks.png" alt="QuickBooks Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('QuickBooks')" class="btn btn-primary">Integrate QuickBooks</button>
        </div>
        <div class="tool-item">
          <img src="integratedimg/ZpfeV3LKHrpE6lY4bHEHuFEbQ6JKYIYxW1Ma38h9.png
" alt="Xero Logo" style="border: 1px solid grey" class="tool-logo">
          <button onclick="showIntegrationSteps('Xero')" class="btn btn-primary">Integrate Xero</button>
        </div>
        <div class="tool-item">
          <img src="integratedimg/square-logo-png-transparent.png" alt="Square Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Square')" class="btn btn-primary">Integrate Square</button>
        </div>
        <div class="tool-item">
          <img src="integratedimg/pngimg.com - paypal_PNG7.png
" alt="PayPal Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('PayPal')" class="btn btn-primary">Integrate PayPal</button>
        </div>
        <div class="tool-item">
          <img src="integratedimg/bf41e6ce97e3682ae318ab75b3295716_icon.png
" alt="FreshBooks Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('FreshBooks')" class="btn btn-primary">Integrate FreshBooks</button>
        </div>
        <div class="tool-item">
          <img src="https://cdn.prod.website-files.com/62446230dcb514b828a6e237/624dedebca225a69a155c7b6_wave-logo-black.svg" alt="Wave Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Wave')" class="btn btn-primary">Integrate Wave</button>
        </div>
        <div class="tool-item">
          <img src="https://arizent.brightspotcdn.com/a0/53/c4a6a0ae4961bc65ba9e26065d13/sage-logo14.png" alt="Sage Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Sage')" class="btn btn-primary">Integrate Sage</button>
        </div>
        <div class="tool-item">
          <img src="https://1000logos.net/wp-content/uploads/2021/04/ADP-logo.png" alt="ADP Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('ADP')" class="btn btn-primary">Integrate ADP</button>
        </div>
        <div class="tool-item">
          <img src="https://prod.gusto-assets.com/assets/svg/header/gusto-logo-bfed4fde4bd2b5dd364ab0e58f77fc90aeefa2f1ecdb7fff524279642e23f9c2.svg" alt="Gusto Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Gusto')" class="btn btn-primary">Integrate Gusto</button>
        </div>
        <div class="tool-item">
          <img src="https://www.merchantmaverick.com/wp-content/uploads/2019/02/Paychex_logo.png" alt="Paychex Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Paychex')" class="btn btn-primary">Integrate Paychex</button>
        </div>
        <div class="tool-item">
          <img src="https://cdn.prod.website-files.com/63e56114746188c54e2936e0/6488d4091595a41dd00d36b5_BILL.png" alt="Bill.com Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Bill.com')" class="btn btn-primary">Integrate Bill.com</button>
        </div>
        <div class="tool-item">
          <img src="https://new.expensify.com/og-preview-image.png" alt="Expensify Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Expensify')" class="btn btn-primary">Integrate Expensify</button>
        </div>
        <div class="tool-item">
          <img src="https://logosandtypes.com/wp-content/uploads/2020/07/Netsuite.png" alt="NetSuite Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('NetSuite')" class="btn btn-primary">Integrate NetSuite</button>
        </div>
        <div class="tool-item">
          <img src="https://avatars.slack-edge.com/2019-08-29/730880231907_caa71cf812305a064114_512.png" alt="Zoho Books Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Zoho Books')" class="btn btn-primary">Integrate Zoho Books</button>
        </div>
        <div class="tool-item">
          <img src="https://tipalti.com/wp-content/themes/Tipalti-GoTeam/assets/images/featured-image.webp" alt="Tipalti Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Tipalti')" class="btn btn-primary">Integrate Tipalti</button>
        </div>
        <div class="tool-item">
          <img src="https://download.logo.wine/logo/Workday%2C_Inc./Workday%2C_Inc.-Logo.wine.png" alt="Workday Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Workday')" class="btn btn-primary">Integrate Workday</button>
        </div>
        <div class="tool-item">
          <img src="https://logosandtypes.com/wp-content/uploads/2020/08/recurly.png" alt="Recurly Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Recurly')" class="btn btn-primary">Integrate Recurly</button>
        </div>
        <div class="tool-item">
          <img src="https://logosandtypes.com/wp-content/uploads/2020/06/Chargebee.png" alt="Chargebee Logo" class="tool-logo">
          <button onclick="showIntegrationSteps('Chargebee')" class="btn btn-primary">Integrate Chargebee</button>
        </div>

        </div>
      </div>

      <div id="Development" class="tabcontent">
        <h3>Development Tools</h3>
        <div class="tool-list">
          <div class="tool-item">
            <img src="github-logo.png" alt="GitHub Logo" class="tool-logo">
            <button onclick="showIntegrationSteps('GitHub')" class="btn btn-primary">Integrate GitHub</button>
          </div>
          <div class="tool-item">
            <img src="zapier-logo.png" alt="Zapier Logo" class="tool-logo">
            <button onclick="showIntegrationSteps('Zapier')" class="btn btn-primary">Integrate Zapier</button>
          </div> <div class="tool-list">
            <div class="tool-item">
              <img src="github-logo.png" alt="GitHub Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('GitHub')" class="btn btn-primary">Integrate GitHub</button>
            </div>
            <div class="tool-item">
              <img src="zapier-logo.png" alt="Zapier Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Zapier')" class="btn btn-primary">Integrate Zapier</button>
            </div>
            <div class="tool-item">
              <img src="jira-logo.png" alt="Jira Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Jira')" class="btn btn-primary">Integrate Jira</button>
            </div>
            <div class="tool-item">
              <img src="trello-logo.png" alt="Trello Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Trello')" class="btn btn-primary">Integrate Trello</button>
            </div>
            <div class="tool-item">
              <img src="asana-logo.png" alt="Asana Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Asana')" class="btn btn-primary">Integrate Asana</button>
            </div>
            <div class="tool-item">
              <img src="slack-logo.png" alt="Slack Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Slack')" class="btn btn-primary">Integrate Slack</button>
            </div>
            <div class="tool-item">
              <img src="figma-logo.png" alt="Figma Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Figma')" class="btn btn-primary">Integrate Figma</button>
            </div>
            <div class="tool-item">
              <img src="gitlab-logo.png" alt="GitLab Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('GitLab')" class="btn btn-primary">Integrate GitLab</button>
            </div>
            <div class="tool-item">
              <img src="confluence-logo.png" alt="Confluence Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Confluence')" class="btn btn-primary">Integrate Confluence</button>
            </div>
            <div class="tool-item">
              <img src="azure-devops-logo.png" alt="Azure DevOps Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Azure DevOps')" class="btn btn-primary">Integrate Azure DevOps</button>
            </div>
          </div>

        </div>
      </div>

      <div id="RiskMgt" class="tabcontent">
        <h3>Risk Management Tools</h3>
        <div class="tool-list">
         <div class="tool-item">
              <img src="risktools-logo.png" alt="Risk Tools Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('RiskTools')" class="btn btn-primary">Integrate Risk Tools</button>
            </div>
            <div class="tool-item">
              <img src="eramba-logo.png" alt="Eramba Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Eramba')" class="btn btn-primary">Integrate Eramba</button>
            </div>
            <div class="tool-item">
              <img src="resolver-logo.png" alt="Resolver Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Resolver')" class="btn btn-primary">Integrate Resolver</button>
            </div>
            <div class="tool-item">
              <img src="riskonnect-logo.png" alt="Riskonnect Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Riskonnect')" class="btn btn-primary">Integrate Riskonnect</button>
            </div>
            <div class="tool-item">
              <img src="metricstream-logo.png" alt="MetricStream Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('MetricStream')" class="btn btn-primary">Integrate MetricStream</button>
            </div>
            <div class="tool-item">
              <img src="logicmanager-logo.png" alt="LogicManager Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('LogicManager')" class="btn btn-primary">Integrate LogicManager</button>
            </div>
            <div class="tool-item">
              <img src="sap-grm-logo.png" alt="SAP GRC Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('SAP GRC')" class="btn btn-primary">Integrate SAP GRC</button>
            </div>
            <div class="tool-item">
              <img src="archer-logo.png" alt="Archer Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Archer')" class="btn btn-primary">Integrate Archer</button>
            </div>
            <div class="tool-item">
              <img src="galvanize-logo.png" alt="Galvanize Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Galvanize')" class="btn btn-primary">Integrate Galvanize</button>
            </div>
            <div class="tool-item">
              <img src="onspring-logo.png" alt="OnSpring Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('OnSpring')" class="btn btn-primary">Integrate OnSpring</button>
            </div>
            <div class="tool-item">
              <img src="servicenow-grm-logo.png" alt="ServiceNow GRM Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('ServiceNow GRM')" class="btn btn-primary">Integrate ServiceNow GRM</button>
            </div>
            <div class="tool-item">
              <img src="camms-logo.png" alt="CAMMS Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('CAMMS')" class="btn btn-primary">Integrate CAMMS</button>
            </div>
            <div class="tool-item">
              <img src="protecht-logo.png" alt="Protecht Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Protecht')" class="btn btn-primary">Integrate Protecht</button>
            </div>
            <div class="tool-item">
              <img src="riskrecon-logo.png" alt="RiskRecon Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('RiskRecon')" class="btn btn-primary">Integrate RiskRecon</button>
            </div>
            <div class="tool-item">
              <img src="bwise-logo.png" alt="BWise Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('BWise')" class="btn btn-primary">Integrate BWise</button>
            </div>
            <div class="tool-item">
              <img src="acuity-logo.png" alt="Acuity Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Acuity')" class="btn btn-primary">Integrate Acuity</button>
            </div>
            <div class="tool-item">
              <img src="sword-grc-logo.png" alt="Sword GRC Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Sword GRC')" class="btn btn-primary">Integrate Sword GRC</button>
            </div>
            <div class="tool-item">
              <img src="riskvision-logo.png" alt="RiskVision Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('RiskVision')" class="btn btn-primary">Integrate RiskVision</button>
            </div>
            <div class="tool-item">
              <img src="cura-logo.png" alt="CURA Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('CURA')" class="btn btn-primary">Integrate CURA</button>
            </div>
            <div class="tool-item">
              <img src="optial-logo.png" alt="Optial Logo" class="tool-logo">
              <button onclick="showIntegrationSteps('Optial')" class="btn btn-primary">Integrate Optial</button>
            </div>

        </div>
      </div>

<!-- remove this line --->

 <div id="integrationSteps" style="display: none;">
        <h3 id="integrationTitle"></h3>
        <div id="integrationContent"></div>
      </div>
    </div>
  </div>
<div id="kpiModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Key Performance Indicators (KPIs)</h2>
    </div>
    <div class="tab">
      <button class="tablinks" onclick="openKpiTab(event, 'FinancialKPIs')">Financial KPIs</button>
      <button class="tablinks" onclick="openKpiTab(event, 'OperationalKPIs')">Operational KPIs</button>
      <button class="tablinks" onclick="openKpiTab(event, 'CustomerKPIs')">Customer KPIs</button>
      <button class="tablinks" onclick="openKpiTab(event, 'EmployeeKPIs')">Employee KPIs</button>
    </div>

    <div id="FinancialKPIs" class="tabcontent">
      <h3>Financial KPIs</h3>
      <div class="kpi-list">
        <div class="kpi-item">
          <h4>Revenue Growth Rate</h4>
          <div class="kpi-value">8.5%</div>
          <div class="kpi-trend positive">↑ 2.1%</div>
        </div>
        <div class="kpi-item">
          <h4>Profit Margin</h4>
          <div class="kpi-value">15.3%</div>
          <div class="kpi-trend negative">↓ 0.7%</div>
        </div>
        <div class="kpi-item">
          <h4>Return on Investment (ROI)</h4>
          <div class="kpi-value">12.8%</div>
          <div class="kpi-trend positive">↑ 1.5%</div>
        </div>
      </div>
    </div>

    <div id="OperationalKPIs" class="tabcontent">
      <h3>Operational KPIs</h3>
      <div class="kpi-list">
        <div class="kpi-item">
          <h4>On-Time Delivery Rate</h4>
          <div class="kpi-value">94.2%</div>
          <div class="kpi-trend positive">↑ 1.8%</div>
        </div>
        <div class="kpi-item">
          <h4>Inventory Turnover</h4>
          <div class="kpi-value">6.5</div>
          <div class="kpi-trend negative">↓ 0.3</div>
        </div>
        <div class="kpi-item">
          <h4>Capacity Utilization</h4>
          <div class="kpi-value">78.9%</div>
          <div class="kpi-trend positive">↑ 3.2%</div>
        </div>
      </div>
    </div>

    <div id="CustomerKPIs" class="tabcontent">
      <h3>Customer KPIs</h3>
      <div class="kpi-list">
        <div class="kpi-item">
          <h4>Customer Satisfaction Score</h4>
          <div class="kpi-value">8.7/10</div>
          <div class="kpi-trend positive">↑ 0.3</div>
        </div>
        <div class="kpi-item">
          <h4>Customer Retention Rate</h4>
          <div class="kpi-value">85.6%</div>
          <div class="kpi-trend negative">↓ 1.2%</div>
        </div>
        <div class="kpi-item">
          <h4>Net Promoter Score (NPS)</h4>
          <div class="kpi-value">62</div>
          <div class="kpi-trend positive">↑ 5</div>
        </div>
      </div>
    </div>

    <div id="EmployeeKPIs" class="tabcontent">
      <h3>Employee KPIs</h3>
      <div class="kpi-list">
        <div class="kpi-item">
          <h4>Employee Engagement Score</h4>
          <div class="kpi-value">7.8/10</div>
          <div class="kpi-trend positive">↑ 0.5</div>
        </div>
        <div class="kpi-item">
          <h4>Employee Turnover Rate</h4>
          <div class="kpi-value">12.3%</div>
          <div class="kpi-trend negative">↑ 1.5%</div>
        </div>
        <div class="kpi-item">
          <h4>Training Completion Rate</h4>
          <div class="kpi-value">92.7%</div>
          <div class="kpi-trend positive">↑ 2.9%</div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="alertsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Alerts</h2>
    </div>
    <div class="modal-body">
      <div id="alertsList">
        <!-- Alerts will be dynamically added here -->
      </div>
    </div>
  </div>
</div>
<div id="activityLogsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Activity Logs</h2>
    </div>
    <div class="modal-body">
      <div class="log-details" style="display:none;">
      
      <div class="input-container">
        <select id="logType">
          <option value="all">All Logs</option>
          <option value="user">User Activity</option>
          <option value="system">System Events</option>
          <option value="security">Security Alerts</option>
        </select>
                <label> Log Types </label>
        </div>

              <div class="input-container">

        <input type="date" id="startDate">
        <label> Start Date </label>
        
        </div>
                      <div class="input-container">

        <input type="date" id="endDate">
                <label for="endDate"> End Date </label>

        </div>
                <button onclick="filterLogs()">Filter</button>
      </div>
      
      <br>

      <div class="log-list">
        <table id="logTable">
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>Type</th>
              <th>User</th>
              <th>Action</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody id="logTableBody">
            <!-- Log entries will be dynamically added here -->
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <button onclick="previousPage()">« Previous</button>
        <span id="currentPage">Page 1</span>
        <button onclick="nextPage()">Next »</button>
      </div>
    </div>
  </div>
</div>
<div id="reportsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Reports</h2>
    </div>
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'FinancialReports')">Financial Reports</button>
      <button class="tablinks" onclick="openTab(event, 'OperationalReports')">Operational Reports</button>
      <button class="tablinks" onclick="openTab(event, 'HRReports')">HR Reports</button>
      <button class="tablinks" onclick="openTab(event, 'SalesReports')">Sales Reports</button>
      <button class="tablinks" onclick="openTab(event, 'CustomReports')">Custom Reports</button>
    </div>

    <div id="FinancialReports" class="tabcontent">
      <h3>Financial Reports</h3>
      <ul>
        <li><a href="#">Income Statement</a></li>
        <li><a href="#">Balance Sheet</a></li>
        <li><a href="#">Cash Flow Statement</a></li>
        <li><a href="#">Profit and Loss</a></li>
      </ul>
    </div>

    <div id="OperationalReports" class="tabcontent">
      <h3>Operational Reports</h3>
      <ul>
        <li><a href="#">Productivity Report</a></li>
        <li><a href="#">Inventory Report</a></li>
        <li><a href="#">Quality Control Report</a></li>
        <li><a href="#">Equipment Utilization Report</a></li>
      </ul>
    </div>

    <div id="HRReports" class="tabcontent">
      <h3>HR Reports</h3>
      <ul>
        <li><a href="#">Employee Turnover Report</a></li>
        <li><a href="#">Recruitment Report</a></li>
        <li><a href="#">Training and Development Report</a></li>
        <li><a href="#">Performance Evaluation Report</a></li>
      </ul>
    </div>

    <div id="SalesReports" class="tabcontent">
      <h3>Sales Reports</h3>
      <ul>
        <li><a href="#">Sales by Region</a></li>
        <li><a href="#">Product Performance Report</a></li>
        <li><a href="#">Customer Acquisition Report</a></li>
        <li><a href="#">Sales Pipeline Report</a></li>
      </ul>
    </div>

    <div id="CustomReports" class="tabcontent">
      <!--<h3>Custom Reports</h3>-->
      <!--<p>Create a custom report by selecting the data points you need:</p>-->
      <form id="customReportForm">
        <div class="input-container">
          <input type="text" id="reportName" name="reportName" required>
                    <label for="reportName">Report Name:</label>

        </div>
        <div class="input-container">
          <select id="dataPoints" name="dataPoints" multiple>
            <option value="revenue">Revenue</option>
            <option value="expenses">Expenses</option>
            <option value="employees">Employees</option>
            <option value="customers">Customers</option>
            <option value="products">Products</option>
          </select>
                    <label for="dataPoints">Select Data Points:</label>

        </div>
        <div class="input-container">
          <input type="date" id="startDate" name="startDate" required>
                              <label for="startRange">Start Date:</label>
        </div>
        <div class="input-container">

          <input type="date" id="endDate" name="endDate" required>
          
                    <label for="endRange">End Date:</label>

        </div>
        <button type="submit" class="btn btn-primary">Generate Custom Report</button>
      </form>
    </div>
  </div>
</div>
<div id="updatesModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Updates</h2>
    </div>
    <div class="updates-container">
      <div class="update-item">
        <h3>Update 1</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <span class="update-date">May 15, 2023</span>
      </div>
      <div class="update-item">
        <h3>Update 2</h3>
        <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        <span class="update-date">May 10, 2023</span>
      </div>
    </div>
  </div>
</div>
<div id="talentModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Talent Suggestions</h2>
    </div>
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'SuggestTalent')">Suggest Talent</button>
      <button class="tablinks" onclick="openTab(event, 'ViewSuggestions')">View Suggestions</button>
    </div>


    

    <div id="SuggestTalent" class="tabcontent">
        
        
       <?php

// include '../../sql_queries/db.php';


// // Check connection
// if (!$conn) {
//     die("Connection failed: " . mysqli_connect_error());
// }

// // Query to get names and talent_id from talents table with limit and offset
// $query = "SELECT talent_id, name FROM talents";
// $result = mysqli_query($conn, $query);

// // Display talents
// while ($row = mysqli_fetch_assoc($result)) {
//     echo "<div>";
//     echo $row['name'];
//     echo " <button onclick='suggestToRecruiter(" . $row['talent_id'] . ")'>Suggest to Recruiter</button>";
//     echo "</div>";
// }

?>

        
     
    </div>

    <div id="ViewSuggestions" class="tabcontent">
      <!--<h3>View Talent Suggestions</h3>-->
             <?php // include 'employees/work/talentlist.php'; ?>

    </div>
  </div>
</div> 
<div id="createTaskModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Create Task</h2>
    </div>
    <div class="modal-body">
      
     

      <?php include 'createtaskform.php' ?>



    </div>
  </div>
</div>
<div id="changeLanguageModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close">×</span>
        <h2>Change Language</h2>
      </div>
      <div class="modal-body">
        <form id="changeLanguageForm" class="styled-form">
          <div class="form-group">
            <label for="language">Select Language:</label>
            <ul id="languageList" class="language-list">
              <li>
                <span>English</span>
                <button type="button" class="choose-language" data-lang="en">Choose</button>
              </li>
              <li>
                <span>Español</span>
                <button type="button" class="choose-language" data-lang="es">Choose</button>
              </li>
              <li>
                <span>Français</span>
                <button type="button" class="choose-language" data-lang="fr">Choose</button>
              </li>
              <li>
                <span>Deutsch</span>
                <button type="button" class="choose-language" data-lang="de">Choose</button>
              </li>
              <li>
                <span>Italiano</span>
                <button type="button" class="choose-language" data-lang="it">Choose</button>
              </li>
              <!-- Add more language options as needed -->
            </ul>
          </div>
        </form>
      </div>
    </div>
  </div>
<div id="bankWalletModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Bank Accounts and Wallets</h2>
    </div>
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'NGNBankAccounts')">Create Naira Account</button>
      <button class="tablinks" onclick="openTab(event, 'USBankAccounts')">Create Dollar Account</button>
      <button class="tablinks" onclick="openTab(event, 'CreateNairaCard')">Create Naira Card</button>
      <button class="tablinks"  onclick="openTab(event, 'CreateDollarCard')">Create Dollar Card</button>

      <button class="tablinks" onclick="openTab(event, 'Wallets')">Wallets</button>
    </div>

    <div id="NGNBankAccounts" class="tabcontent">       
           <?php include '../../users/admin/createbankaccount.php'; ?>
    </div>

    <div id="USBankAccounts" class="tabcontent">
          <?php include '../../users/admin/createusbankaccount.php'; ?>
    </div>

    <div id="CreateNairaCard" class="tabcontent">

    <?php include '../../users/admin/createnairacard.php';?>

    </div>

    <div id="CreateDollarCard" class="tabcontent">

    <?php include '../../users/admin/createdollarcard.php';?>

    </div>

    <div id="Wallets" class="tabcontent">
      <h3>Wallets</h3>
      <div id="walletList">
        <!-- Wallet items will be dynamically added here -->
      </div>
    </div>
  </div>
</div>


<!-- top level menu --->


<div id="createProjectModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Create Project</h2>
    </div>
    <div class="modal-body">
     
     
     <form id="createprojectform" method="POST" enctype="multipart" class="customizeforms">
        <div class="input-container">
          <input type="text" id="projectName" name="projectName" required placeholder=" ">
          <label for="projectName">Project Name:</label>
        </div>
      
        <div class="input-container">
          <textarea id="projectDescription" name="projectDescription" rows="3" placeholder="Describe your project" style="font-size: 10px; color: green;"></textarea>
        </div>
        
        <div class="input-container">
          <input id="projectFile" type="file" name="projectfile" placeholder=" " multiple>
          <label for="projectFile">Attachments:</label>
        </div>
        
        <div class="input-container">
          <input type="date" id="startDate" name="startDate" required>
          <label for="startDate">Start Date:</label> 
        </div>

        <div class="input-container">
          <input type="date" id="endDate" name="endDate" required>
          <label for="endDate">End Date:</label> 
        </div>

                <div class="input-container">
                  <select id="projectDepartment" name="projectDepartment" required>
                    <option value="" disabled selected>Select a department</option>
                    <?php include '../../users/admin/fetchdepartements.php'; ?>
                    <!-- Add more departments as needed -->
                  </select>
                  <label for="projectDepartment">Department:</label>
                </div>
        
        <div class="input-container">
          <select id="projectStatus" name="projectStatus" required>
            <option value="planning">Planning</option>
            <option value="inProgress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
          <label for="projectStatus">Project Status:</label>
        </div>

        <div class="input-container"> 
          <select id="projectManager" name="projectManager" required>
            <option value="" disabled>Select a project manager</option>
            <!-- Add options dynamically based on employees -->
            <?php include '../../users/admin/fetchmanagersforproject.php';?>
          </select>
          <label for="projectManager">Project Manager:</label>
        </div>

        <div class="input-container">
          <input type="hidden" id="projectCompany_id" name="company_id" value="<?php echo $companyId; ?>" required>
        </div>
          
        <input type="submit" value="Create Project">
      </form>
    </div>
  </div>
  </div>
  <script>
    document.getElementById('createprojectform').addEventListener('submit', function(e) {
        e.preventDefault();
          
        var projectformData = new FormData(this);
          
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'createproject.php', true);
          
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Request successful
                alert(xhr.responseText);
             document.getElementById('createprojectform').reset();

                // Handle the response as needed
            } else {
                // Request failed
                console.error('Error:', xhr.status);
            }
        };
          
        xhr.onerror = function() {
            console.error('Request failed');
        };
          
        xhr.send(projectformData);
    });
    </script>
      
      
      
      <div id="createVaultModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Upload Vault </h2>
    </div>
    <div class="modal-body">
      <form method="post" id="vaultform" enctype="multipart/form-data">
        
        

        <div class="input-container">
          <input type="text" id="asset_name" name="asset_name" required placeholder="">
          <label for="vaultName">Asset Name:</label>
        </div>

        <div class="input-container">
          <textarea id="assest_description" name="asset_description" placeholder="Asset Description"></textarea>
          <label for="Asset Description"></label>
        </div>

        <div class="input-container">
          <input type="file" id="asset_file" name="asset_file[]" multiple>
          <label for="UploadAsset">Upload sensitive files, environment variables, or source code snippets, etc (Optional):</label>
        </div>

        <div class="input-container">
          <select id="asset_access_level" name="asset_access_level" required>
            <option value="" disabled>Select Access Level</option>
            <option value="private">Private</option>
            <option value="team">Team</option>
            <option value="company">Company-wide</option>
          </select>
        <label for="accesslevel">Select Access Level:</label>
        </div>

        
        
        
        <div class="encryptflex" style="display: flex; flex-direction: row; gap: 5px;">
  <div class="input-container" style="flex: 2;">
    <input type="text" id="asset_password" name="asset_password" required>
    <label for="asset_password">Encryption Code:</label>
  </div>

  <div class="buttonflex" style="flex: 1;">
    <button class="geneencrypt" onclick="xoloencyrptgenerate(); event.preventDefault();" style="width: 100%; padding: 11px 10px; background-color: black; color: white; outline: none; border-radius: 5px; border: none;">Generate Encryption Key with Xolo</button>
  </div>
  
  <script>
        function xoloencyrptgenerate() {
    // Generate a random 256-bit encryption code
    const encryptionCode = Array.from(crypto.getRandomValues(new Uint8Array(32)))
      .map(b => b.toString(16).padStart(2, '0'))
      .join('');

    // Place the encryption code inside input with id = asset_password
    document.getElementById('asset_password').value = encryptionCode;

    return encryptionCode;
  }
  </script>
</div>
          
        <div class="input-container">
          <input type="text" id="asset_tags" name="asset_tags" placeholder="">
          <label for="assettags">Add (comma-separated) tags to help organize your vaults:</label>
          
        </div>

        <div class="input-container">
          <input type="date" id="asset_expiry" name="asset_expiry">
        <label for="asset_expiry">Set an expiration date for this asset (Optional):</label>

        </div>

<div class="input-container">
          <input type="hidden" id="vaultcompany_id" name="company_id" value="<?php echo $companyId; ?>" required>
        </div>
        
        <input type="submit" value="Upload to Vault">
      </form>
    </div>
  </div>
</div>

<div id="customizeWidgetModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Customize Widgets</h2>
    </div>
    <div class="modal-body">

        <?php include '../users/admin/getwidgets.php' ?>
        
        
    </div> 
    </div>
  </div>


<div id="subscriptionsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Manage Tools </h2>
    </div>
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'DisplayTools')">Toolkit Subscriptions</button>
      <button class="tablinks" onclick="openTab(event, 'AssignTools')">Assign Tools</button>
      <button class="tablinks" onclick="openTab(event, 'RevokeTools')">Revoke Tools </button>
    
            <button class="tablinks" onclick="openTab(event, 'DisplayAssigned')">Tool Assignees</button>

    </div>

   <div id="DisplayTools" class="displaytool tabcontent">
                        <?php include '../../users/admin/subtable.php'; ?>
   </div>


    <div id="AssignTools" class="assigntools tabcontent">
      <?php // include '../../users/admin/assigntools.php'; ?>
   </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
     $.ajax({
          url: '../../users/admin/assigntools.php',
          method: 'GET',
          dataType: 'html',
          success: function(data) {
           $('#AssignTools').html(data);
          },
          error: function(xhr, status, error) {
           console.error('Error loading assign tools:', error);
           $('#AssignTools').html('Error loading assign tools. Please try again later.');
          }
     });
    });
    </script>


   <div id="RevokeTools" class="revoketools tabcontent">
      <?php include '../../users/admin/revokefetch.php'; ?>
   
   </div>
   
   <div id="DisplayAssigned" class="displayassigned tabcontent">
      <?php  include '../../users/admin/displayedtoolsassigned.php'; ?>
   
   </div>
   

   </div>
   </div>

<div id="eventsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Manage Events </h2>


      
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'CreateEvent')">Create Event</button>
            <button class="tablinks" onclick="openTab(event, 'ViewEvent')">View Event</button>
            <button class="tablinks" onclick="openTab(event, 'SendReminder')">Send Reminder</button>
            <button class="tablinks" onclick="openTab(event, 'DeleteEvent')">Delete Event</button>
                      </div>
                  
                      <div id="CreateEvent" class="createevent tabcontent">
                        <?php include '../../users/admin/createeventform.php'; ?>
                      </div>
            
                      <div id="ViewEvent" class="viewevent tabcontent">
                        <?php include '../../users/admin/viewevents.php'; ?>
                      </div>
                          
                      <div id="SendReminder" class="sendreminder tabcontent">
                        <?php include '../../users/admin/sendeventreminder.php'; ?>
                      </div>
                      
                      <div id="DeleteEvent" class="deleteevent tabcontent">
                        <?php include '../../users/admin/deleteevents.php'; ?>
                      </div>
            
    </div>
  </div>
</div>



<div id="activeUsersModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Manage Employees </h2>


    </div>
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'AddEmployee')">Add Employee</button>
      <button class="tablinks" onclick="openTab(event, 'ViewEmployees')">View Employees</button>
      <button class="tablinks" onclick="openTab(event, 'EditEmployee')">Edit Employee</button>
      <button class="tablinks" onclick="openTab(event, 'DeleteEmployee')">Delete Employee</button>
    </div>

    <div id="AddEmployee" class="addemployee tabcontent">
      <?php include '../../users/admin/addemployeeform.php'; ?>
    </div>

    <div id="ViewEmployees" class="viewemployees tabcontent">
      <?php include '../../users/admin/viewemployees.php'; ?>
    </div>

    <div id="EditEmployee" class="editemployee tabcontent">
      <?php include '../../users/admin/editemployee.php'; ?>
    </div>

    <div id="DeleteEmployee" class="deleteemployee tabcontent">
      <?php include '../../users/admin/deleteemployee.php'; ?>
    </div>





    </div>
  </div>
</div>

<div id="automationsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Manage Automations</h2>
    </div>


    
        <div class="tab">
          <button class="tablinks" onclick="openTab(event, 'CreateAutomation')">Create Automation</button>
          <button class="tablinks" onclick="openTab(event, 'ViewAutomations')">View Automations</button>
          <button class="tablinks" onclick="openTab(event, 'EditAutomation')">Edit Automation</button>
          <button class="tablinks" onclick="openTab(event, 'DeleteAutomation')">Delete Automation</button>
        </div>
    
        <div id="CreateAutomation" class="createautomation tabcontent">
          <?php include '../../users/admin/createautomation.php'; ?>
        </div>
    
        <div id="ViewAutomations" class="viewautomations tabcontent">
          <?php include '../../users/admin/viewautomations.php'; ?>
        </div>
    
        <div id="EditAutomation" class="editautomation tabcontent">
          <?php include '../../users/admin/editautomation.php'; ?>
        </div>
    
        <div id="DeleteAutomation" class="deleteautomation tabcontent">
          <?php include '../../users/admin/deleteautomation.php'; ?>
        </div>
    
  </div>
</div>

<div id="workflowsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Manage Workflows</h2>
    </div>
  </div>
</div>

<div id="mentionsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Mentions</h2>

      </div>



      <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'AllMentions')">All Mentions</button>
            <button class="tablinks" onclick="openTab(event, 'UnreadMentions')">Unread Mentions</button>
            <button class="tablinks" onclick="openTab(event, 'MentionSettings')">Mention Settings</button>
          </div>
      
          <div id="AllMentions" class="allmentions tabcontent">
            <?php include '../../users/admin/allmentions.php'; ?>
          </div>
      
          <div id="UnreadMentions" class="unreadmentions tabcontent">
            <?php include '../../users/admin/unreadmentions.php'; ?>
          </div>
      
          <div id="MentionSettings" class="mentionsettings tabcontent">
            <?php include '../../users/admin/mentionsettings.php'; ?>
          </div>
      
  </div>
</div>

<div id="sIntelligenceModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Intelligence</h2>
    </div>

    
        <div class="tab">
          <button class="tablinks" onclick="openTab(event, 'CreateNuerons')">Create Nuerons</button>
          <button class="tablinks" onclick="openTab(event, 'ActiveNuerons')">View Nuerons </button>
          <button class="tablinks" onclick="openTab(event, 'NueronActivity')">Nueron Activity </button>
          <button class="tablinks" onclick="openTab(event, 'NueronParser')">Nueron Parser</button>
          <button class="tablinks" onclick="openTab(event, 'NueronRequest')">Nueron Request</button>

        </div>
    
        <div id="CreateNuerons" class="createnuerons tabcontent">
          <?php include '../../users/admin/createnueronsform.php'; ?>
        </div>
    
        <div id="ActiveNuerons" class="activenuerons tabcontent">
          <?php include '../../users/admin/activenuerons.php'; ?>
        </div>
    
        <div id="NueronActivity" class="nueronactivity tabcontent">
          <?php include '../../users/admin/nueronactivity.php'; ?>
        </div>
        <div id="NueronParser" class="nueronparser tabcontent">
          <!-- <div id="nueronParserContent"></div>
          <script>
            fetch('../../users/admin/nueronparser.html')
              .then(response => response.text())
              .then(data => {
                document.getElementById('nueronParserContent').innerHTML = data;
              })
              .catch(error => console.error('Error:', error));
          </script> -->

          <?php include "nueronparser.html"; ?>
        </div>


        <div id="NueronRequest" class="nueron-request tabcontent">
          <?php include "../../users/admin/requestcustomnuerons.php"; ?>
        </div>

  </div>
</div>

<div id="insightsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Insights</h2>
    </div>

        <div class="tab">
          <button class="tablinks" onclick="openTab(event, 'OverviewInsights')">Overview</button>
          <button class="tablinks" onclick="openTab(event, 'TrendsInsights')">Trends</button>
          <button class="tablinks" onclick="openTab(event, 'RecommendationsInsights')">Recommendations</button>
        </div>
    
        <div id="OverviewInsights" class="overviewinsights tabcontent">
          <?php include '../../users/admin/overviewinsights.php'; ?>
        </div>
    
        <div id="TrendsInsights" class="trendsinsights tabcontent">
          <?php include '../../users/admin/trendsinsights.php'; ?>
        </div>
    
        <div id="RecommendationsInsights" class="recommendationsinsights tabcontent">
          <?php include '../../users/admin/recommendationsinsights.php'; ?>
        </div>
    
  </div>
</div>

<div id="teamsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2>Manage Teams </h2>
    </div>
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'CreateTeams')">Create Team</button>
      <button class="tablinks" onclick="openTab(event, 'ViewTeams')">View Teams</button>
      <button class="tablinks" onclick="openTab(event, 'UpdateTeams')">Update Teams</button>


    </div>

   <div id="CreateTeams" class="createteams tabcontent">
        <?php  include '../../users/admin/createteamform.php'; ?>
   </div>

 
    <div id="ViewTeams" class="viewteams tabcontent">
      <?php include '../../users/admin/viewteams.php'; ?>
   </div>
   
  
  <div id="UpdateTeams" class="updateteams tabcontent">
      <?php include '../../users/admin/updateteamform.php';?>

   </div>
   </div>
   
</div> 
   
   <div id="settingsModal" class="modal">
     <div class="modal-content">
       <div class="modal-header">
         <span class="close">×</span>
         <h2>Settings</h2>
       </div>
       <div class="tab">
         <button class="tablinks" onclick="openTab(event, 'GeneralSettings')">General</button>
         <button class="tablinks" onclick="openTab(event, 'SecuritySettings')">Security</button>
         <button class="tablinks" onclick="openTab(event, 'NotificationSettings')">Notifications</button>
       </div>
   
       <div id="GeneralSettings" class="generalsettings tabcontent">
         <?php include '../../users/admin/generalsettings.php'; ?>
       </div>
   
       <div id="SecuritySettings" class="securitysettings tabcontent">
         <?php include '../../users/admin/securitysettings.php'; ?>
       </div>
   
       <div id="NotificationSettings" class="notificationsettings tabcontent">
         <?php include '../../users/admin/notificationsettings.php'; ?>
       </div>
     </div>
   </div>
   

   
      <div id="notificationsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close" onclick="closeNotificationModal()">×</span>
            <h2>Notifications</h2>
          </div>
          <div id="notificationContent">
            <!-- Notification content will be loaded here -->
          </div>
        </div>
      </div>
   
      
        
     <!-- Modal -->
    <div id="messagesModal" class="messagemodal">
        <div class="messagemodal-content">
            <div class="messagemodal-header">
                <h2>Messages</h2>
                <span class="close" onclick="closeModal()">×</span>
            </div>
            <div class="messagemodal-body">
                <!-- Left Panel for Navigation -->
                <div class="left-panel">
                    <h3>Panels <i class="fas fa-plus"></i></h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-rss icon"></i>Feed</a></li>
                        <li><a href="#"><i class="fas fa-play icon"></i>Start Here</a></li>
                        <li><a href="#"><i class="fas fa-bell icon"></i>What's New</a></li>
                        <li><a href="#"><i class="fas fa-book icon"></i>Resources</a></li>
                    </ul>
                    <div class="category">
                        <h4>Teams <i class="fas fa-plus"></i></h4>
                        <ul>
                            <li><a href="#"><i class="fas fa-users icon"></i>Marketing</a></li>
                            <li><a href="#"><i class="fas fa-users icon"></i>Development</a></li>
                        </ul>
                    </div>
                    <div class="category">
                        <h4>Managers <i class="fas fa-plus"></i></h4>
                        <ul>
                            <li><a href="#"><i class="fas fa-user-tie icon"></i>John Doe</a></li>
                            <li><a href="#"><i class="fas fa-user-tie icon"></i>Jane Smith</a></li>
                        </ul>
                    </div>
                    <div class="category">
                        <h4>Employees <i class="fas fa-plus"></i></h4>
                        <ul>
                            <li><a href="#"><i class="fas fa-user icon"></i>Alice Johnson</a></li>
                            <li><a href="#"><i class="fas fa-user icon"></i>Bob Williams</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Middle Section for Messages -->
                <div class="message-section">
                        <div id="messages">
                        <!-- Example Messages -->
                        <p><strong>User1:</strong> Hi there!</p>
                        <p><strong>User2:</strong> Hello, how's it going?</p>
                    </div>
                    <div class="message region" style="border: 1px solid #ccc; padding: 10px; border-radius: 10px; margin-bottom:-8px;"  >
                    <textarea id="messageInput" placeholder="Type a message..."></textarea>
                    <div class="message-icons">
                        <i class="fas fa-smile"></i>
                        <i class="fas fa-paperclip"></i>
                        <i class="fas fa-image"></i>
                        <i class="fas fa-video"></i>
                        <i class="fas fa-microphone"></i>
                        <i class="fas fa-paper-plane" onclick="sendMessage()"></i>
                    </div>
                </div>
                </div>
                
                <!-- Right Panel for Profile -->
                <div class="right-panel">
                    <div class="profile-section">
                        <h3>Profile</h3>
                        <img src="https://cdn.jsdelivr.net/gh/alohe/avatars@master/png/bluey_3.png" alt="Profile Picture" class="profile-picture">
                        <h2 style="font-size: 1.2em;">kinghenry50</h2>
                        <div class="profile-tabs">
                            <a href="#" class="active">About</a>
                            <a href="#">Posts</a>
                            <a href="#">Comments</a>
                            <a href="#">Spaces</a>
                        </div>
                        <ul>
                            <li>Employee since January 18, 2024</li>
                            <li>Last seen 1 week ago</li>
                            <li><span class="tag">NEW EMPLOYEE</span></li>
                        </ul>


                        <div class="chat-history">
                            <h4>Chat Highlights</h4>
                            <ul class="commit-like-history">
                                <li>
                                    <span class="commit-avatar">
                                        <img src="https://cdn.jsdelivr.net/gh/alohe/avatars@master/png/bluey_3.png" alt="User Avatar">
                                    </span>
                                    <span class="commit-message">Added new feature for user dashboard</span>
                                    <span class="commit-date">2 days ago</span>
                                </li>
                                <li>
                                    <span class="commit-avatar">
                                        <img src="https://cdn.jsdelivr.net/gh/alohe/avatars@master/png/bluey_2.png" alt="User Avatar">
                                    </span>
                                    <span class="commit-message">Fixed bug in messaging system</span>
                                    <span class="commit-date">4 days ago</span>
                                </li>
                                <li>
                                    <span class="commit-avatar">
                                        <img src="https://cdn.jsdelivr.net/gh/alohe/avatars@master/png/bluey_1.png" alt="User Avatar">
                                    </span>
                                    <span class="commit-message">Updated profile settings</span>
                                    <span class="commit-date">1 week ago</span>
                                </li>
                            </ul>
                        </div>



                                                                          <div class="chat-history-grid">
                                                                              <h4>Chat Activity (260)</h4>

                                                                              <div class="grid-container">
                                                                                  <div class="month-labels">
                                                                                      <span>Jan</span>
                                                                                      <span>Feb</span>
                                                                                      <span>Mar</span>
                                                                                      <span>Apr</span>
                                                                                      <span>May</span>
                                                                                      <span>Jun</span>
                                                                                      <span>Jul</span>
                                                                                      <span>Aug</span>
                                                                                      <span>Sep</span>
                                                                                      <span>Oct</span>
                                                                                      <span>Nov</span>
                                                                                      <span>Dec</span>
                                                                                  </div>
                                                                                  <div id="activity-grid"></div>
                                                                              </div>
                                                                              <div class="legend">
                                                                                  <span class="legend-item">Less</span>
                                                                                  <div class="legend-cell" style="background-color: #ebedf0;"></div>
                                                                                  <div class="legend-cell" style="background-color: #c6e48b;"></div>
                                                                                  <div class="legend-cell" style="background-color: #7bc96f;"></div>
                                                                                  <div class="legend-cell" style="background-color: #239a3b;"></div>
                                                                                  <div class="legend-cell" style="background-color: #196127;"></div>
                                                                                  <span class="legend-item">More</span>
                                                                              </div>
                                                                          </div>

                                                                          <style>
                                                                              .chat-history-grid {
                                                                                  margin-top: 20px;
                                                                                  border-top: 1px solid #e1e4e8;
                                                                                  padding-top: 15px;
                                                                              }
                                                                              .grid-container {
                                                                                  display: flex;
                                                                                  flex-direction: column;
                                                                                  gap: 2px;
                                                                              }
                                                                              .month-labels {
                                                                                  display: flex;
                                                                                  justify-content: space-around;
                                                                                  margin-bottom: 5px;
                                                                                  font-size: 0.8em;
                                                                                  color: #586069;
                                                                              }
                                                                              .grid-row {
                                                                                  display: flex;
                                                                                  gap: 2px;
                                                                              }
                                                                              .day-label {
                                                                                  width: 30px;
                                                                                  font-size: 0.8em;
                                                                                  color: #586069;
                                                                                  text-align: right;
                                                                                  padding-right: 5px;
                                                                              }
                                                                              .grid-cell {
                                                                                  width: 15px;
                                                                                  height: 15px;
                                                                                  border-radius: 2px;
                                                                                  cursor: pointer;
                                                                              }
                                                                              .legend {
                                                                                  display: flex;
                                                                                  align-items: center;
                                                                                  justify-content: flex-end;
                                                                                  margin-top: 10px;
                                                                                  font-size: 0.8em;
                                                                                  color: #586069;
                                                                              }
                                                                              .legend-item {
                                                                                  margin: 0 5px;
                                                                              }
                                                                              .legend-cell {
                                                                                  width: 10px;
                                                                                  height: 10px;
                                                                                  border-radius: 2px;
                                                                                  margin: 0 2px;
                                                                              }
                                                                          </style>

                                                                          <script>
                                                                              document.addEventListener('DOMContentLoaded', function() {
                                                                                  const activityGrid = document.getElementById('activity-grid');
                                                                                  const currentDate = new Date();
                                                                                  const currentYear = currentDate.getFullYear();

                                                                                  const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                                                                  const colors = ['#ebedf0', '#c6e48b', '#7bc96f', '#239a3b', '#196127'];

                                                                                  for (let i = 0; i < 7; i++) {
                                                                                      const row = document.createElement('div');
                                                                                      row.className = 'grid-row';
            
                                                                                      const dayLabel = document.createElement('div');
                                                                                      dayLabel.className = 'day-label';
                                                                                      dayLabel.textContent = days[i];
                                                                                      row.appendChild(dayLabel);

                                                                                      for (let j = 0; j < 52; j++) {
                                                                                          const cell = document.createElement('div');
                                                                                          cell.className = 'grid-cell';
                
                                                                                          const cellDate = new Date(currentYear, 0, 1 + (j * 7) + i);
                                                                                          const colorIndex = Math.floor(Math.random() * colors.length);
                                                                                          cell.style.backgroundColor = colors[colorIndex];

                                                                                          const startDate = new Date(cellDate);
                                                                                          const endDate = new Date(cellDate);
                                                                                          endDate.setDate(endDate.getDate() + 6);

                                                                                          const titleText = `Chat history from ${startDate.toDateString()} - ${endDate.toDateString()}`;
                                                                                          cell.title = titleText;

                                                                                          row.appendChild(cell);
                                                                                      }

                                                                                      activityGrid.appendChild(row);
                                                                                  }
                                                                              });
                                                                          </script>
                        <style>
                            .chat-history {
                                margin-top: 20px;
                                border-top: 1px solid #e1e4e8;
                                padding-top: 15px;
                            }
                            .commit-like-history {
                                list-style-type: none;
                                padding: 0;
                            }
                            .commit-like-history li {
                                margin-bottom: 10px;
                                display: flex;
                                align-items: center;
                            }
                            .commit-avatar img {
                                width: 24px;
                                height: 24px;
                                border-radius: 50%;
                                margin-right: 10px;
                            }
                            .commit-message {
                                flex-grow: 1;
                                font-size: 0.9em;
                            }
                            .commit-date {
                                font-size: 0.8em;
                                color: #586069;
                            }
                        </style>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <button id="openModalBtn" onclick="openModal()">Open Modal</button> -->

    <script>
        function openModal() {
            document.getElementById('modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const messageText = messageInput.value.trim();
            if (messageText) {
                const messageDiv = document.createElement('p');
                messageDiv.innerHTML = '<strong>You:</strong> ' + messageText;
                document.getElementById('messages').appendChild(messageDiv);
                messageInput.value = '';  // Clear the input field
                // Scroll to the bottom of the messages
                const messagesContainer = document.getElementById('messages');
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) {
                closeModal();
            }
        }
    </script>
   
               <div id="analyticsModal" class="modal">
                 <div class="modal-content">
                   <div class="modal-header">
                     <span class="close" onclick="closeAnalyticsModal()">×</span>
                     <h2>Analytics</h2>
                   </div>
                   <div id="analyticsContent">
                     <!-- Analytics content will be loaded here -->
                   </div>
                 </div>
               </div>
   
               <script>
               function openAnalyticsModal() {
                 var analyticsModal = document.getElementById("analyticsModal");
                 analyticsModal.style.display = "block";
                 loadAnalytics();
               }
   
               function closeAnalyticsModal() {
                 var analyticsModal = document.getElementById("analyticsModal");
                 analyticsModal.style.display = "none";
               }
   
               function loadAnalytics() {
                 // AJAX call to fetch analytics
                 var xhr = new XMLHttpRequest();
                 xhr.onreadystatechange = function() {
                   if (this.readyState == 4 && this.status == 200) {
                     document.getElementById("analyticsContent").innerHTML = this.responseText;
                   }
                 };
                 xhr.open("GET", "../../users/admin/fetch_analytics.php", true);
                 xhr.send();
               }
               </script>
   



               <div id="cloudModal" class="modal">
                 <div class="modal-content">
                   <div class="modal-header">
                     <span class="close">×</span>
                     <h2>Cloud Management</h2>
                   </div>

                   <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'CreateInstances')">Create Instances</button>
      <button class="tablinks" onclick="openTab(event, 'ConfigureNetworking')">Configure Networking</button>
      <button class="tablinks" onclick="openTab(event, 'ManageStorage')">Manage Storage</button>
      <button class="tablinks" onclick="openTab(event, 'MonitorResources')">Monitor Resources</button>
      <button class="tablinks" onclick="openTab(event, 'SecurityGroups')">Security Groups</button>
      <button class="tablinks" onclick="openTab(event, 'LoadBalancers')">Load Balancers</button>
      <button class="tablinks" onclick="openTab(event, 'CloudFormation')">Cloud Formation</button>
    </div>
                   <div id="cloudContent">


    <div id="CreateInstances" class="tabcontent">
      <h3>Create Instances</h3>
      <p>Content for creating cloud instances goes here.</p>
    </div>

    <div id="ConfigureNetworking" class="tabcontent">
      <h3>Configure Networking</h3>
      <p>Content for configuring cloud networking goes here.</p>
    </div>

    <div id="ManageStorage" class="tabcontent">
      <h3>Manage Storage</h3>
      <p>Content for managing cloud storage goes here.</p>
    </div>

    <div id="MonitorResources" class="tabcontent">
      <h3>Monitor Resources</h3>
      <p>Content for monitoring cloud resources goes here.</p>
    </div>

    <div id="SecurityGroups" class="tabcontent">
      <h3>Security Groups</h3>
      <p>Content for managing security groups goes here.</p>
    </div>

    <div id="LoadBalancers" class="tabcontent">
      <h3>Load Balancers</h3>
      <p>Content for managing load balancers goes here.</p>
    </div>

    <div id="CloudFormation" class="tabcontent">
      <h3>Cloud Formation</h3>
      <p>Content for cloud formation goes here.</p>
    </div>



                   </div>
                 </div>
               </div>






</div> <!-- main containter -->
   
   
<script src="../../public/scripts/modalopen.js"></script>
</body>
</html>