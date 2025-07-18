<?php
include '../../sql_queries/db.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Retrieve all leads from the database
$leads = [];
$sql = "SELECT * FROM leads WHERE company_id = $company_id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leads[] = $row;
    }
}

// Get count of leads by status
$statusDistributionQuery = "SELECT status, COUNT(*) as count FROM leads WHERE company_id = $company_id GROUP BY status";
$statusResult = $conn->query($statusDistributionQuery);

$statusData = [];
while ($row = $statusResult->fetch_assoc()) {
    $statusData[] = $row;
}

// get count of leads by source
// Retrieve count of leads by source
$sourceData = [];
$sql = "SELECT lead_source, COUNT(*) as count 
        FROM leads 
        WHERE company_id = $company_id 
        GROUP BY lead_source";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sourceData[] = $row;
    }
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Leads</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/leads.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>


<body>

    <?php include 'header.php'; ?>



    <div class="leads-container">
        <h1>Leads</h1>
        <table class="lead-table">
            <thead>
                <tr>
                    <th>name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Source</th>
                </tr>
            </thead>


            <?php foreach ($leads as $lead) { ?>
                <tbody>
                    <td><?php echo $lead['lead_name']; ?></td>
                    <td><?php echo $lead['lead_email']; ?></td>
                    <td><?php echo $lead['lead_phone']; ?></td>
                    <?php
                    // dropdown to update status
                    $status = $lead['status'];
                    echo "<td>";
                    echo "<select onchange='updateStatus(\"{$lead['lead_id']}\", this.value)'>";
                    echo "<option value='new' " . ($status === 'new' ? 'selected' : '') . ">new</option>";
                    echo "<option value='contacted' " . ($status === 'contacted' ? 'selected' : '') . ">contacted</option>";
                    echo "<option value='qualified' " . ($status === 'qualified' ? 'selected' : '') . ">qualified</option>";
                    echo "<option value='lost' " . ($status === 'lost' ? 'selected' : '') . ">lost</option>";
                    echo "</select>";
                    echo "</td>";
                    ?>
                    <td><?php echo $lead['lead_source']; ?></td>
                </tbody>
            <?php } ?>
        </table>
    </div>

    <!-- in-Depth graphs that bring out info on data-->
    <div class="title-container">
        <h3>In-Depth analytics</h3>
    </div>
    <div class="charts-dashboard">
        <div class="in-depth-charts-grid">
            <div class="grid-box">
                <!-- Lead Status Distribution Chart -->
                <canvas id="statusDistributionChart" data-status='<?php echo json_encode($statusData); ?>'></canvas>
            </div>
            <div class="grid-box">
                <!-- Lead Source Analysis Chart -->
                <canvas id="sourceDistributionChart" data-source='<?php echo json_encode($sourceData); ?>'></canvas>
            </div>
        </div>
    </div>
    <!-------------------- footer ------------------------>
    <?php include 'footer.php'; ?>

</body>
<script src="scripts/index.js"></script>
<script src="scripts/leads.js"></script>

</html>