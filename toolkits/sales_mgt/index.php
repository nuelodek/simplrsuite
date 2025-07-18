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


// fetch all meetings
$sql_meetings = "SELECT * FROM meetings WHERE company_id = '$companyId' ORDER BY meeting_date DESC";
$result_meetings = $conn->query($sql_meetings);
$mettings = [];
while ($meeting = $result_meetings->fetch_assoc()) {
    $meetings[] = $meeting;
}

// fetch upcoming meetings
$sql_upcoming_meetings = "SELECT * FROM meetings WHERE company_id = '$companyId' AND meeting_date >= CURDATE() ORDER BY meeting_date ASC";
$result_upcoming_meetings = $conn->query($sql_upcoming_meetings);
$upcoming_meetings = [];
while ($upcoming_meeting = $result_upcoming_meetings->fetch_assoc()) {
    $upcoming_meetings[] = $upcoming_meeting;
}


// Close the database connection
// $conn->close();



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management Dashboard</title>
    <link rel="stylesheet" href="styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <!-- Logo and Title -->
            <div class="navbar-left">
                <div class="logo">🔗</div>
                <span class="title">CRM</span>
            </div>

            <!-- Navigation Links -->
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="leads.php">Leads</a></li>
                <li><a href="contacts.php">Contacts</a></li>
                <li><a href="customers.php">Customers</a></li>
                <li><a href="accounts.php">Accounts</a></li>
                <li><a href="deals.php">Deals</a></li>
                <li><a href="#tasks">Tasks</a></li>
                <li><a href="#projects">Projects</a></li>
            </ul>

            <!-- Additional Actions and User Profile -->
            <div class="navbar-right">
                <div class="icons">
                    <span id="addBtn" onclick="toggleAdd()">➕</span>
                    <div id="addDropdown" class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-modal="addMeetingModal">Add Meeting</a>
                        <a href="#" class="dropdown-item" data-modal="addLeadModal">Add Lead</a>
                        <a href="#" class="dropdown-item" data-modal="addContactModal">Add Contact</a>
                        <a href="#" class="dropdown-item" data-modal="addAccountModal">Add Account</a>
                        <a href="#" class="dropdown-item" data-modal="addCustomerModal">Add Customer</a>
                        <!-- Add more options as needed -->
                    </div>
                    <span>🔍</span> 
                    <span>⚙️</span>
                    <span class="profile">👤</span>
                </div>
            </div>
        </nav>

        <!-- MODALS FOR ADDING NEW ITEMS -->
        <!-- Add New meeting Modal -->
        <div id="addMeetingModal" class="item-modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addMeetingModal')">&times;</span>
                <h2>Add Meeting</h2>
                <form action="add_meeting.php" method="POST">
                    <label for="meetingTitle">Title:</label>
                    <input type="text" id="meetingTitle" name="meetingTitle" required>

                    <label for="meetingDate">Date:</label>
                    <input type="date" id="meetingDate" name="meetingDate" required>

                    <label for="meetingLocation">Location:</label>
                    <select id="meetingLocation" name="meetingLocation" onchange="handleLocationChange()" required>
                        <option value="">Select Location</option>
                        <option value="online">online</option>
                        <option value="in-person">in person</option>
                    </select>

                    <!-- Online Options (Google Meet, Zoom) -->
                    <div id="onlineOptions" class="dynamic-field">
                        <label for="onlinePlatform">Start Instant Meeting:</label>
                        <select id="onlinePlatform" name="onlinePlatform">
                            <option value="">Select Platform</option>
                            <option value="Google Meet">Google Meet</option>
                            <option value="Zoom">Zoom</option>
                        </select>
                    </div>

                    <!-- In-Person Address Input -->
                    <div id="inPersonAddress" class="dynamic-field">
                        <label for="meetingAddress">Address:</label>
                        <input type="text" id="meetingAddress" name="meetingAddress" placeholder="Enter meeting address">
                    </div>

                    <label for="meetingTime">Time:</label>
                    <input type="time" id="meetingTime" name="meetingTime" required>

                    <label for="meetingDetails">Details:</label>
                    <textarea id="meetingDetails" name="meetingDetails"></textarea>
                    <button type="submit">Add Meeting</button>
                </form>
            </div>
        </div>

        <div id="addLeadModal" class="item-modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addLeadModal')">&times;</span>
                <h2>Add Lead</h2>
                <form action="add_lead.php" method="POST">
                    <label for="leadName">Lead Name:</label>
                    <input type="text" id="leadName" name="leadName" required>

                    <label for="leadEmail">Email:</label>
                    <input type="email" id="leadEmail" name="leadEmail" required>

                    <label for="leadSource">Source:</label>
                    <select id="leadSource" name="leadSource" onchange="handleSourceChange()" required>
                        <option value="">Select Source</option>
                        <option value="Website">Website</option>
                        <option value="Social-Media">Social Media</option>
                        <option value="Email">Email</option>
                        <option value="Referral">Referral</option>
                    </select>

                    <div id="additionalFieldsContainer"></div>

                    <label for="leadPhone">Phone:</label>
                    <input type="tel" id="leadPhone" name="leadPhone">

                    <button type="submit">Add Lead</button>
                </form>
            </div>
        </div>

        <div id="addContactModal" class="item-modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addContactModal')">&times;</span>
                <h2>Add Contact</h2>
                <form action="add_contact.php" method="POST">
                    <label for="contactName">Name:</label>
                    <input type="text" id="contactName" name="contactName" required>

                    <label for="contactEmail">Email:</label>
                    <input type="email" id="contactEmail" name="contactEmail" required>

                    <label for="contactPhone">Phone:</label>
                    <input type="tel" id="contactPhone" name="contactPhone" required>

                    <button type="submit">Add Contact</button>
                </form>
            </div>
        </div>

        <div id="addAccountModal" class="item-modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addAccountModal')">&times;</span>
                <h2>Add Account</h2>
                <form action="add_account.php" method="POST">
                    <label for="accountOwner">Owner:</label>
                    <select id="accountOwner" name="accountOwner" required>
                        <option value="">Select Owner</option>
                        <!-- Fetch and display account owners from the database -->
                        <?php
                        //fetch all cutomers from the database
                        $customer_sql = "SELECT * FROM customers WHERE company_id = $companyId";
                        $result = mysqli_query($conn, $customer_sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $customer_id = $row['customer_id'];
                            $business_name = $row['business_name'];
                            echo "<option value='$customer_id'>$business_name</option>";
                        }
                        if(mysqli_num_rows($result) == 0){
                            echo "<option value=''>No customers found</option>";
                        }
                        ?>
                    </select>

                    <label for="accountEmail">Email:</label>
                    <input type="email" id="accountEmail" name="accountEmail" required>

                    <label for="accountPhone">Phone:</label>
                    <input type="tel" id="accountPhone" name="accountPhone" required>

                    <button type="submit">Add Account</button>
                </form>
            </div>
        </div>

        <div id="addCustomerModal" class="item-modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addCustomerModal')">&times;</span>
                <h2>Add Customer</h2>
                <form action="add_customer.php" method="POST">
                    <label for="customerName">First Name:</label>
                    <input type="text" id="firstName" name="firstName" required>

                    <label for="lastName">Last Name:</label>
                    <input type="text" id="lastName" name="lastName" required>

                    <label for="businessName">Business Name:</label>
                    <input type="text" id="businessName" name="businessName" required>

                    <label for="acquiredItem">is this customer acquiring a product:</label>
                    <select id="acquiredItem" name="acquiredItem" required>
                        <option value="service">Service</option>
                        <option value="product">Product</option>
                    </select>

                    <label for="customerEmail">Email:</label>
                    <input type="email" id="customerEmail" name="customerEmail" required>

                    <label for="customerPhone">Phone:</label>
                    <input type="tel" id="customerPhone" name="customerPhone" required>

                    <label for="customerAddress">Address:</label>
                    <input type="text" id="customerAddress" name="customerAddress" required>

                    <button type="submit">Add Customer</button>
                </form>
            </div>
        </div>

        <!-- Welcome Message and Dropdown Menu -->
        <div class="header">
            <div class="welcome">
                <img class="company-icon" src="https://www.svgrepo.com/show/356903/organization.svg" alt="Company Icon">
                <h2>Welcome, <?php echo $companyName; ?></h2>
            </div>
            <div class="actions">
                <button class="reload-btn" onclick="reloadPage()"><img src="https://www.svgrepo.com/show/514213/reload.svg" alt="Reload Icon"></button>
                <select class="d-select" id="homeDropdown">
                    <option value="home">Favour Orji's Home</option>
                </select>
                <div class="dropdown">
                    <button onclick="toggleDropdown()" class="dropdown-btn">☰</button>
                    <div id="dropdownMenu" class="dropdown-content">
                        <button onclick="addComponent()">Add Component</button>
                        <button>Reorder</button>
                        <button>View in Full Screen</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal for choosing component -->
        <!-- Modal structure -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <h3>Select Component to Add</h3>

                <!-- Radio buttons for different component options -->
                <form id="component-selection-form">
                    <label>
                        <input type="checkbox" name="components" value="Sales Pipeline" checked /> Sales Pipeline
                    </label>
                    <label>
                        <input type="checkbox" name="components" value="Upcoming Meetings" checked /> Upcoming Meetings
                    </label>
                    <label>
                        <input type="checkbox" name="components" value="Customer Overview" checked /> Customer Overview
                    </label>
                    <label>
                        <input type="checkbox" name="components" value="Revenue Trend" checked /> Revenue Trend
                    </label>
                    <label>
                        <input type="checkbox" name="components" value="Lead Sources" /> Lead Sources
                    </label>
                    <label>
                        <input type="checkbox" name="components" value="Deals by Stage" /> Deals by Stage
                    </label>
                    <button type="button" id="add-components-btn">Add Selected Components</button>
                </form>
            </div>
        </div>
    </header>
    <!-- Navbar -->

    <!-- Main Content Area -->
    <div class="dashboard">
        <div class="grid-overview">
            <div class="box" id="salesPipelineBox">
                <h3>sales Pipeline</h3>
                <canvas id="salesPipelineChart"></canvas>
            </div>

            <div class="box" id="upcomingMeetingsBox">
                <h3>upcoming Meetings</h3>
                <div class="meetings-container">
                    <?php
                    foreach ($upcoming_meetings as $meeting) {
                        echo '
                        <div class="meeting-card">
                        <span class="meeting-tag">
                        <h4>' . $meeting['meeting_title'] . ' with ' . $meeting['attendee'] . '</h4>
                        </span>
                        <div class="meeting-details">
                          <p>Date: ' . $meeting['meeting_date'] . '</p>
                          <p>Time: ' . $meeting['meeting_time'] . '</p>
                          <p>Location: ' . $meeting['location'] . '</p>
                        </div>
                        </div>
                        ';
                    }
                    ?>
                </div>
            </div>

            <div class="box" id="customerOverviewBox">
                <h3>customer overview</h3>
                <canvas id="customerOverviewChart"></canvas>
            </div>

            <div class="box" id="revenueTrendBox">
                <h3>Revenue Trend</h3>
                <canvas id="revenueTrendChart"></canvas>
            </div>

            <div class="box" id="leadSourcesBox">
                <h3>Lead sources</h3>
                <canvas id="leadSourcesChart"></canvas>
            </div>

            <div class="box" id="dealsByStageBox">
                <h3>Deals</h3>
                <canvas id="dealsByStageChart"></canvas>
            </div>
            <!-- Additional boxes will be added here dynamically -->
        </div>
    </div>

    <script src="scripts/index.js"></script>
</body>

</html>