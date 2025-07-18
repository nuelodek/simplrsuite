<script src="scripts/index.js"></script>
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
        <li><a href="campaigns.php">campaigns</a></li>
    </ul>

    <!-- Additional Actions and User Profile -->
    <div class="navbar-right">
        <div class="searchbar-container">
            <input class="searchbar" id="searchBar" type="text" placeholder="Search">
        </div>
        <div class="icons">
            <span onclick="openSearchBar()">🔍</span>
            <span onclick="openCalender()">📅</span>
            <span onclick="openSettingsModal()">⚙️</span>
            <span class="profile">👤</span>
        </div>
    </div>
</nav>

<div class="calender" id="calender">
    <div class="calendar-header">
        <button id="prevMonth" onclick="navigateMonth(-1)">&#9664;</button>
        <span class="month-year" id="monthYear"></span>
        <button id="nextMonth" onclick="navigateMonth(1)">&#9654;</button>
    </div>
    <div class="calendar-body">
        <div class="day-names">
            <span>Sun</span>
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
        </div>
        <div class="days" id="days">
            <!-- Days will be dynamically filled here -->
        </div>
    </div>
</div>