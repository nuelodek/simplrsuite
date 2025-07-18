<?php
include "../../sql_queries/db.php";
session_start();

// check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    header("Location: login.html");
    exit();
}

$company_id = $_SESSION['company_id']; // retrieve all customers
$sql = "SELECT * FROM customers WHERE company_id = '$company_id'";
$result = $conn->query($sql);
$customers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
} else {
    echo "
         <div class='no-customers-container' id='noCustomersContainer'>
        <h3>there are no customers yet</h3>
        <button class='add-customer' onclick='toggleAddCustomerModal()'>add customer</button> 
    </div>
    ";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/customers.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Customers</title>
</head>

<body>
    <nav>
        <?php include 'header.php' ?>
    </nav>
    <!-- add customer modal -->
    <div id="addCustomerModal" class="add-customer-modal item-modal" id="addCustomerModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addCustomerModal')">&times;</span>
            <h2>Add Customer</h2>
            <form action="add_customer.php" method="POST">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" required>

                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" required>

                <label for="businessName">Business Name:</label>
                <input type="text" id="businessName" name="businessName" required>

                <label for="acquiredItem">is this customer acquiring a product or service:</label>
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

    <div class="customers-container">
        <h2>Customers</h2>
        <div class="customers-grid">
            <?php foreach ($customers as $customer) : ?>
                <div class="customer-item">
                    <h3><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></h3>
                    <p>Business Name: <?php echo $customer['business_name']; ?></p>
                    <p>Acquired Item: <?php echo $customer['acquired_item']; ?></p>
                    <a href="customer_details.php?customer_id=<?php echo $customer['customer_id']; ?>" class="view-details">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- customer metrics -->
        <div class="customer-metrics">
            <h3>Customer Metrics</h3>
            <div class="customer-metrics-grid">
                <div class="customer-metrics-card">
                    <div class="card-inner">
                        <button class="flip-button" onclick="flipCard(this.parentElement)">
                            <span class="icon-metric">📊</span>
                        </button>
                        <div class="card-front">
                            <h4>Total Customers</h4>
                            <?php
                            $totalCustomersQuery = "SELECT COUNT(*) AS total FROM customers WHERE company_id = '$company_id'";
                            $totalCustomersResult = $conn->query($totalCustomersQuery);
                            $totalCustomersRow = $totalCustomersResult->fetch_assoc();
                            echo "<p class='metric-value'> you have " . $totalCustomersRow['total'] . " customers</p>";
                            ?>
                        </div>
                        <div class="card-back">
                            <!-- Placeholder for graphical representation -->
                            <h4>Graphical Data</h4>
                            <canvas id="totalCustomersChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <div class="customer-metrics-card">
                    <div class="card-inner">
                        <button class="flip-button" onclick="flipCard(this.parentElement)">
                            <span class="icon-metric">📊</span>
                        </button>
                        <div class="card-front">
                            <h4>New Customers</h4>
                            <p>30 Days</p>
                            <?php
                            $newCustomersQuery = "SELECT COUNT(*) AS new_customers_30_days FROM customers WHERE company_id = '$company_id' AND DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= DATE(created_at)";
                            $newCustomersResult = $conn->query($newCustomersQuery);
                            $newCustomersRow = $newCustomersResult->fetch_assoc();
                            echo "<p class='metric-value'>" . $newCustomersRow['new_customers_30_days'] . " new customers in the last 30 days</p>";
                            ?>
                        </div>
                        <div class="card-back">
                            <!-- Placeholder for graphical representation -->
                            <h4>Graphical Data</h4>
                            <canvas id="newCustomersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- add customer button -->
        <button class="add-customer-fixed" onclick="toggleAddCustomerModal()">Add Customer</button>
    </div>

    <script src="scripts/customers.js"></script>
</body>

</html>