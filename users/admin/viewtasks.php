<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic
// session_start();
// $companyId = $_SESSION['company_id'];

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize filter variables
$assigneeFilter = '';
$taskNameFilter = '';

// Check if the form is submitted to filter the results
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['assignee'])) {
        $assigneeFilter = $_POST['assignee'];
    }
    if (!empty($_POST['taskName'])) {
        $taskNameFilter = $_POST['taskName'];
    }
}

// Fetch tasks for this company with filtering
$sql_tasks = "SELECT t.id, t.title, t.description, t.due_date, t.priority, t.assignee, t.file_path, t.status,
              p.projectName
              FROM tasks t
              JOIN projects p ON t.project_id = p.unique_id
              WHERE t.company_id = ?";

// Prepare the statement
$stmt = $conn->prepare($sql_tasks);
$stmt->bind_param("i", $companyId);

// Apply filters to the query
if (!empty($assigneeFilter)) {
    $sql_tasks .= " AND t.assignee = ?";
    $stmt->bind_param("s", $assigneeFilter);
}
if (!empty($taskNameFilter)) {
    $sql_tasks .= " AND t.title LIKE ?";
    $stmt->bind_param("s", '%' . $taskNameFilter . '%');
}

// Execute the query
$stmt->execute();
$result_tasks = $stmt->get_result();


// Define custom CSS styles
echo '<style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: .9em;
        }
        table, th, td {
            border: 1px solid #dddddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }

        /* Default row background color for even rows */
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Hover effect */
        tr:hover {
            background-color: #f1f1f1;
        }

        /* File link styling */
        .file-link a {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border-radius: 10px;
        }
        .file-link a:hover {
            background-color: #2980b9;
            text-decoration: none;
        }

        /* Action button styling */
        .actions a {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 8px;
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .actions a:hover {
            text-decoration: none;
        }
        
        .action-iterate {
            background-color: lightgreen;
        }
            .action-iterate:hover {
            background-color: #27ae60;
            text-decoration: none;
        }
        .action-delete {
            background-color: #e74c3c;
        }
        .action-delete:hover {
            background-color: #c0392b;
            text-decoration: none;
        }

        /* Row background colors based on task status */
        .status-pending {
            background-color: #ff6f6f; /* Light red for pending tasks */
        }
        .status-ongoing {
            background-color: #ffa07a; /* Light orange for ongoing tasks */
        }
        .status-completed {
            background-color: #20b2aa; /* Teal for completed tasks */
        }

        /* Hover effects for status rows */
        .status-pending:hover {
            background-color: #e65a5a; /* Darker red on hover */
        }
        .status-ongoing:hover {
            background-color: #e69163; /* Darker orange on hover */
        }
        .status-completed:hover {
            background-color: #179b96; /* Darker teal on hover */
        }

        /* Color legend styles */
        .color-legend {
            margin-bottom: 0px;
            display: flex;
            flex-direction: row;
            gap: 20px;
            font-size: .8em;
            font-family: Arial, sans-serif;
            color: #333;
        }
        .color-legend div {
            margin-bottom: 0px;
            display: flex;
            align-items: center;
        }
        .color-box {
            width: 10px;
            height: 10px;
            border-radius: 10px;
            display: inline-block;
            margin-right: 5px;
        }
        .color-pending {
            background-color: #ff6f6f;
        }
        .color-ongoing {
            background-color: #ffa07a;
        }
        .color-completed {
            background-color: #20b2aa;
        }

        /* Modal styling */
        .report-modal {
            display: none;
            position: fixed;
            z-index: 99;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .report-modal-content {
            border-radius: 10px;
            background-color: #fff;
            margin: 80px 0 0 calc(23% + 10px) ;
            padding: 20px;
            border: 1px solid #888;
            width: 100%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
      </style>';

// Color legend
echo '<div class="color-legend">
        <div><span class="color-box color-pending"></span> Pending Tasks</div>
        <div><span class="color-box color-ongoing"></span> Ongoing Tasks</div>
        <div><span class="color-box color-completed"></span> Completed Tasks</div>
      </div>';

// Filter form
echo '<form method="POST" action="" style="margin-bottom: 20px;">
<label for="assignee">Filter by Assignee:</label>
<input type="text" id="assignee" name="assignee" value="' . htmlspecialchars($assigneeFilter) . '">

<label for="taskName">Filter by Task Name:</label>
<input type="text" id="taskName" name="taskName" value="' . htmlspecialchars($taskNameFilter) . '">

<input type="submit" value="Filter">
</form>';   

// Add modal for task report
echo '<div id="reportModal" class="report-modal">
        <div class="report-modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Task Report</h2>
            <p id="taskReportContent">Here goes the task report...</p>
            <label for="reason">Reason for not accepting:</label>
            <textarea id="reason" rows="4" cols="50"></textarea><br><br>
            <button id="acceptCompletion">Accept Completion</button>
            <button id="sendBack">Send Back for Revisions</button>
        </div>
      </div>';

if ($result_tasks->num_rows == 0) {
    echo '<div style="color: red;">No tasks found for this organization.</div>';
} else {
    echo '<table border="0">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Assignee</th>
                <th>Project</th>
                <th>File</th>
                <th>Actions</th>
            </tr>';

    while ($task = $result_tasks->fetch_assoc()) {
        // Apply row background color based on task status
        $statusClass = 'status-' . strtolower($task['status']);
        echo "<script> console.log('$statusClass'); </script>";

        echo '<tr class="' . $statusClass . '">';
        echo '<td>' . htmlspecialchars($task['title']) . '</td>';
        echo '<td>' . htmlspecialchars(substr($task['description'], 0, 140)) . '...</td>';
        echo '<td>' . htmlspecialchars($task['due_date']) . '</td>';
        echo '<td>' . ucfirst(htmlspecialchars($task['priority'])) . '</td>';
        echo '<td>' . htmlspecialchars($task['assignee']) . '</td>';
        echo '<td>' . htmlspecialchars($task['projectName']) . '</td>';

        // Display file link if available
        echo '<td class="file-link">';
        if (!empty($task['file_path'])) {
            echo '<a href="' . htmlspecialchars($task['file_path']) . '" target="_blank"><img style="width: 25px; height: 25px; cursor: pointer;" src="https://www.svgrepo.com/show/357744/file-check-alt.svg" alt="view-file"></a>';
        } else {
            echo 'No file';
        }
        echo '</td>';

        // Display action buttons
        echo '<td class="actions">';
        if (strtolower($task['status']) == 'completed') {
            // Show "Iterate" button for completed tasks
            echo '<a href="#" class="action-iterate" onclick="openModal(' . $task['id'] . ')"><img style="width: 25px; height: 25px; cursor: pointer;" src="https://www.svgrepo.com/show/335139/popout.svg" alt="Iterate"></a>';
        } else {
            // Show "Update" and "Delete" buttons for non-completed tasks
            echo '<a href="deletetask.php?id=' . $task['id'] . '" class="action-delete" onclick="return confirm(\'Are you sure you want to delete this task?\')"><img style="width: 25px; height: 25px; cursor: pointer;" src="https://www.svgrepo.com/show/502608/delete-2.svg" alt="Delete"></a>';
        }
        echo '</td>';

        echo '</tr>';
    }

    echo '</table>';
}
$conn->close();
?>

<script>
    // Modal handling
    var modal = document.getElementById("reportModal");
    var span = document.getElementsById("close")[0];

    // Open the modal and populate it with the report data
    function openModal(taskId) {
        // Fetch the task report via AJAX or API call, then show the modal
        document.getElementById("taskReportContent").innerHTML = "Loading report for task #" + taskId + "...";
        modal.style.display = "block";
    }

    // Close the modal
    function closeModal() {
        modal.style.display = "none";
    }

    // Close the modal if the user clicks outside of it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>