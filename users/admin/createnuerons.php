
<?php
// Database connection
include '../../sql_queries/db.php';

// Create the neurons table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS neurons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    neuron_name VARCHAR(255) NOT NULL,
    neuron_type VARCHAR(255) NOT NULL,
    neuron_description TEXT,
    execution_type ENUM('once', 'recurring', 'arbitrary') NOT NULL,
    once_datetime DATETIME,
    recurring_interval ENUM('daily', 'weekly', 'monthly'),
    recurring_time TIME,
    recurring_weekdays VARCHAR(255),
    arbitrary_schedule TEXT,
    company_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createTableSQL) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $neuronName = $conn->real_escape_string($_POST['neuronName']);
    $neuronType = $conn->real_escape_string($_POST['neuronType']);
    $neuronDescription = $conn->real_escape_string($_POST['neuronDescription']);
    $executionType = $conn->real_escape_string($_POST['executionType']);
    $companyId = intval($_POST['company_id']);

    $onceDateTime = null;
    $recurringInterval = null;
    $recurringTime = null;
    $recurringWeekdays = null;
    $arbitrarySchedule = null;

    if ($executionType == 'once') {
        $onceDateTime = $conn->real_escape_string($_POST['onceDateTime']);
    } elseif ($executionType == 'recurring') {
        $recurringInterval = $conn->real_escape_string($_POST['recurringInterval']);
        $recurringTime = $conn->real_escape_string($_POST['recurringTime']);
        if ($recurringInterval == 'weekly') {
            $recurringWeekdays = implode(',', $_POST['weekDay']);
        }
    } elseif ($executionType == 'arbitrary') {
        $arbitrarySchedule = $conn->real_escape_string($_POST['arbitrarySchedule']);
    }

    $sql = "INSERT INTO neurons (neuron_name, neuron_type, neuron_description, execution_type, once_datetime, recurring_interval, recurring_time, recurring_weekdays, arbitrary_schedule, company_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $neuronName, $neuronType, $neuronDescription, $executionType, $onceDateTime, $recurringInterval, $recurringTime, $recurringWeekdays, $arbitrarySchedule, $companyId);

    if ($stmt->execute()) {
        echo "Neuron created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
