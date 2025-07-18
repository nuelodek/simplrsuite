<?php
// Include the database connection file
include '../../sql_queries/db.php';
session_start();

// Check if companyId is set in the session
if (!isset($_SESSION['company_id'])) {
    // Redirect to login.html if companyId is not found
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['unique_id'];
$companyId = $_SESSION['company_id'];
$companyName = $_SESSION['companyName'];

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/dashboard.css">
    <title>Project Management Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap');

        html,
        body {
            font-family: "DM Sans", sans-serif;
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar styles */
        .sidebar {
            width: 230px;
            height: 100vh;
            background-color: #f0f0f0;
            color: #333;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 0px;
        }

        .sidebar .menulist {
            width: 100%;
        }

        .sidebar .menu {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .sidebar .menu li {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .sidebar .menu li:hover {
            background-color: #888;
        }

        .sidebar .menu li img {
            margin-right: 15px;
            width: 20px;
            height: 20px;
        }

        .company-info {
            display: flex;
            align-items: center;
            margin: 20px;
            text-align: left;
            color: #fff;
        }

        .company-info img {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            margin-right: 10px;
        }

        .company-info .company-details span {
            display: block;
            color: #333;
        }

        .active{
            border-bottom: 2px solid #4CAF50;
            font-weight: bolder;
            transition-duration: .5s;
        }

        /* Main content styles */
        .content-panel {
            margin-left: 240px; /* Sidebar width */
            padding: 20px;
            display: none; /* Hide all sections by default */
        }

        .content-panel.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="sidebar">

        <!-- Company Info Section -->
        <div class="company-info">
            <img src="simple-social-svgrepo-com.png" alt="Company Logo">
            <div class="company-details">
                <span style="font-weight: bold;"><?php echo $companyName; ?></span>
                <span style="font-size: 10px;">Company ID: <?php echo $companyId; ?></span>
            </div>
        </div>

        <!-- Menu Section -->
        <div class="menulist">
            <ul class="menu">
                <li class="active" onclick="displayPanel('dashboard', this)"><img src="https://www.svgrepo.com/show/485607/dashboard-layout.svg" alt="Dashboard Icon">Dashboard</li>
                <li onclick="displayPanel('projects', this)"><img src="https://www.svgrepo.com/show/432552/projects-alt.svg" alt="Projects Icon">Projects</li>
                <li onclick="displayPanel('teams', this)"><img src="https://www.svgrepo.com/show/458732/group.svg" alt="Teams Icon">Teams</li>
                <li onclick="displayPanel('reports', this)"><img src="https://www.svgrepo.com/show/465520/medical-report.svg" alt="Reports Icon">Reports</li>
                <li onclick="displayPanel('settings', this)"><img src="https://www.svgrepo.com/show/524954/settings.svg" alt="Settings Icon">Settings</li>
                <li onclick="logout()"><img src="https://www.svgrepo.com/show/470849/logout.svg" alt="Logout Icon">Logout</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <!-- Content Panels -->
        <div id="dashboard" class="content-panel active">Welcome to the Dashboard
            <?php include 'dashboard.php'; ?>
        </div>
        <div id="projects" class="content-panel">Project information goes here
            <?php include 'allprojects.php'; ?>
        </div>
        <div id="teams" class="content-panel">Teams information goes here</div>
        <div id="reports" class="content-panel">Reports information goes here</div>
        <div id="settings" class="content-panel">Settings information goes here</div>
    </div>

    <script src="../../scripts/projdashboard.js"></script>
</body>

</html>