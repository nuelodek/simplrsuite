<?php
include 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the connection is established
if (!isset($conn) || !$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch pending demo requests
$sql = "SELECT * FROM bookdemo WHERE status = 'Pending' ORDER BY demo_date ASC, demo_time ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2 class='mb-4'>Pending Demo Requests</h2>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-hover table-bordered table-striped'>";
    echo "<thead class='thead-dark'>";
    echo "<tr>";
    echo "<th scope='col' class='text-center'>Name</th>";
    echo "<th scope='col' class='text-center'>Email</th>";
    echo "<th scope='col' class='text-center'>Company</th>";
    echo "<th scope='col' class='text-center'>Date</th>";
    echo "<th scope='col' class='text-center'>Time</th>";
    echo "<th scope='col' class='text-center'>Actions</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='align-middle'>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td class='align-middle'>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td class='align-middle'>" . htmlspecialchars($row['company']) . "</td>";
        echo "<td class='align-middle text-center'>" . date('F j, Y', strtotime($row['demo_date'])) . "</td>";
        echo "<td class='align-middle text-center'>" . date('g:i A', strtotime($row['demo_time'])) . "</td>";
        echo "<td class='align-middle text-center'>";
        echo "<div class='btn-group' role='group'>";
        echo "<button class='btn btn-outline-primary btn-sm mr-2' onclick='approveDemo(" . $row['id'] . ")'><i class='fas fa-check'></i> Approve</button>";
        echo "<button class='btn btn-outline-danger btn-sm' onclick='rejectDemo(" . $row['id'] . ")'><i class='fas fa-times'></i> Reject</button>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "<div class='alert alert-info' role='alert'>";
    echo "<i class='fas fa-info-circle'></i> No pending demo requests.";
    echo "</div>";
}
