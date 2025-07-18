
<?php
// require './session.php';
require '../../sql_queries/db.php';

// Initialize arrays to store data
$months = [];
$userCounts = [];
$paymentAmounts = [];
$projectCounts = [];
$taskCounts = [];

// Get data for the last 6 months
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime("-$i months"));
    
    // Get user registrations
    $userQuery = "SELECT COUNT(*) as count FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $userResult = $conn->query($userQuery);
    $userRow = $userResult->fetch_assoc();
    $userCounts[] = $userRow['count'];
    
    // Get payment amounts
    $paymentQuery = "SELECT SUM(amount) as total FROM totalpayments WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $paymentResult = $conn->query($paymentQuery);
    $paymentRow = $paymentResult->fetch_assoc();
    $paymentAmounts[] = $paymentRow['total'] ?? 0;
    
    // Get project counts
    $projectQuery = "SELECT COUNT(*) as count FROM projects WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $projectResult = $conn->query($projectQuery);
    $projectRow = $projectResult->fetch_assoc();
    $projectCounts[] = $projectRow['count'];
    
    // Get task counts
    $taskQuery = "SELECT COUNT(*) as count FROM tasks WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $taskResult = $conn->query($taskQuery);
    $taskRow = $taskResult->fetch_assoc();
    $taskCounts[] = $taskRow['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trend Insights</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Business Trend Insights</h2>
        
        <div class="chart-container">
            <canvas id="userRegistrationChart"></canvas>
        </div>
        
        <div class="chart-container">
            <canvas id="paymentChart"></canvas>
        </div>
        
        <div class="chart-container">
            <canvas id="projectTaskChart"></canvas>
        </div>
    </div>

    <script>
        // User Registration Chart
        new Chart(document.getElementById('userRegistrationChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'New User Registrations',
                    data: <?php echo json_encode($userCounts); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'User Registration Trends'
                    }
                }
            }
        });

        // Payment Chart
        new Chart(document.getElementById('paymentChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Payment Amounts',
                    data: <?php echo json_encode($paymentAmounts); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Payment Trends'
                    }
                }
            }
        });

        // Project and Task Chart
        new Chart(document.getElementById('projectTaskChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Projects',
                    data: <?php echo json_encode($projectCounts); ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                },
                {
                    label: 'Tasks',
                    data: <?php echo json_encode($taskCounts); ?>,
                    borderColor: 'rgb(153, 102, 255)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Project and Task Trends'
                    }
                }
            }
        });
    </script>
</body>
</html>
