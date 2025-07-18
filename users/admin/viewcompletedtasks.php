<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch completed tasks for this company
$sql_tasks = "SELECT t.id, t.title, t.description, t.due_date, t.priority, t.assignee, t.file_path, 
              p.projectName 
              FROM tasks t
              JOIN projects p ON t.project_id = p.unique_id
              WHERE t.company_id = '$companyId' AND t.status = 'completed'";
$result_tasks = $conn->query($sql_tasks);

// Define custom CSS styles
echo '<style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .file-link a {
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
        .c-actions a {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 8px;
            background-color: #2ecc71;
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            transition: background-color 0.3s ease;

        }

        .c-actions a:hover {
            background-color: #27ae60;
            text-decoration: none;
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

    // Add modal for task report
echo '<div id="taskModal" class="report-modal">
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
    echo '<div style="color: red;">No completed tasks found for this organization.</div>';
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
        echo '<tr>';
        echo '<td>' . htmlspecialchars($task['title']) . '</td>';
        echo '<td>' . htmlspecialchars($task['description']) . '</td>';
        echo '<td>' . htmlspecialchars($task['due_date']) . '</td>';
        echo '<td>' . ucfirst(htmlspecialchars($task['priority'])) . '</td>';
        echo '<td>' . htmlspecialchars($task['assignee']) . '</td>';
        echo '<td>' . htmlspecialchars($task['projectName']) . '</td>';
        
        // Display file link if available
        echo '<td class="file-link">';
        if (!empty($task['file_path'])) {
            echo '<a href="' . htmlspecialchars($task['file_path']) . '" target="_blank"><img style="width: 25px; height: 25px; cursor: pointer;" src="https://www.svgrepo.com/show/532786/file-pencil-alt.svg" alt="view-file"></a>';
        } else {
            echo 'No file';
        }
        echo '</td>';
        
        // Action buttons
        echo '<td class="c-actions">
                <a href="#" class="action-iterate" onclick="openModal(' . $task['id'] . ')">Check</a>
              </td>';
        echo '</tr>';
    }

    echo '</table>';
}

$conn->close();
?>

<script>
// Modal handling
var modal = document.getElementById("taskModal");
console.log("openModal");
var span = document.getElementsById("close")[0];

// Open the modal and populate it with the report data
function openModal(taskId) {
    // Fetch the task report via AJAX or API call, then show the modal
    document.getElementById("taskReportContent").innerHTML = "Loading report for task #" + taskId + "...";
    console.log("openModal");
    modal.style.display = "block";
}

// Close the modal
function closeModal (){
    modal.style.display = "none";
}

// Close the modal if the user clicks outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
