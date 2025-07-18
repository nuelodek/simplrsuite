<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch tasks for this company
$sql_tasks = "SELECT t.id, t.title, t.description, t.due_date, t.priority, t.assignee, t.file_path, 
              p.projectName 
              FROM tasks t
              JOIN projects p ON t.project_id = p.unique_id
              WHERE t.company_id = '$companyId' AND t.status = 'ongoing'";
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
        
        .action-update {
            background-color: lightgreen;
        }
            .action-update:hover {
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
        /* Modal styles */
        .update-modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            overflow: auto;
        }
        .ongoing-modal-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        input[type="text"], textarea, select, input[type="date"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #2ecc71;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #27ae60;
        }
        .update-btn {
            background-color: #2ecc71;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;        
        }
        .update-btn:hover, .update-btn:focus {
            background-color: #27ae60;
        }
      </style>';

// Modal for updating tasks
echo '<div id="updateModal" class="update-modal">
        <div class="ongoing-modal-content">
            <span class="close" onclick="closeUpdateModal()">&times;</span>
            <h2>Update Task</h2>
            <form id="updateTaskForm">
                <input type="hidden" name="taskId" id="updateTaskId">
                <label for="updateTitle">Title:</label>
                <input type="text" name="updateTitle" id="updateTitle" required><br><br>
                <label for="updateDescription">Description:</label>
                <textarea name="updateDescription" id="updateDescription"></textarea><br><br>
                <label for="updateDueDate">Due Date:</label>
                <input type="date" name="updateDueDate" id="updateDueDate" required><br><br>
                <label for="updatePriority">Priority:</label>
                <select name="updatePriority" id="updatePriority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select><br><br>
                <label for="updateAssignee">Assignee:</label>
                <input type="text" name="updateAssignee" id="updateAssignee" required><br><br>
                <button class="update-btn" type="button" onclick="submitUpdateTask()">Update Task</button>
                <p id="updateMessage" style="color: green;"></p>
            </form>
        </div>
    </div>';

// Display tasks
if ($result_tasks->num_rows == 0) {
    echo '<div style="color: red;">No tasks found for this organization.</div>';
} else {
    echo '<table>
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
        // Ensure that special characters in task fields are escaped safely for JavaScript
        $task_js_data = json_encode([
            'id' => $task['id'],
            'title' => $task['title'],
            'description' => $task['description'],
            'due_date' => $task['due_date'],
            'priority' => $task['priority'],
            'assignee' => $task['assignee']
        ]);

        echo '<tr>';
        echo '<td>' . htmlspecialchars($task['title']) . '</td>';
        echo '<td>' . htmlspecialchars(substr($task['description'], 0, 50)) . '...</td>';
        echo '<td>' . htmlspecialchars($task['due_date']) . '</td>';
        echo '<td>' . ucfirst(htmlspecialchars($task['priority'])) . '</td>';
        echo '<td>' . htmlspecialchars($task['assignee']) . '</td>';
        echo '<td>' . htmlspecialchars($task['projectName']) . '</td>';
        
        // Display file link if available
        echo '<td class="file-link">';
        if (!empty($task['file_path'])) {
            echo '<a href="' . htmlspecialchars($task['file_path']) . '" target="_blank"><img style="width: 25px; height: 25px; cursor: pointer;" src="https://www.svgrepo.com/show/532786/file-pencil-alt.svg" alt="view-file"></a>';
        } else {
            echo 'No File';
        }
        echo '</td>';
        
        // Display action buttons
        echo '<td class="actions">';
        echo '<a href="#" class="action-update" onclick=\'openUpdateModal(' . $task_js_data . ')\'><img style="width: 25px; height: 25px; cursor: pointer;" src="https://www.svgrepo.com/show/470171/update-alt-2.svg" alt="Update"></a>';
        echo '<a class="action-delete" href="deletetask.php?taskId=' . $task['id'] . '"><img style="width: 25px; height: 25px; cursor: pointer;" src="https://www.svgrepo.com/show/502608/delete-2.svg" alt="Delete"></a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
}
// Add JavaScript for modal functionality and AJAX form submission
?>
<script>
        function openUpdateModal(task) {
            console.log('task');
            document.getElementById("updateTaskId").value = task.id;
            document.getElementById("updateTitle").value = task.title;
            document.getElementById("updateDescription").value = task.description;
            document.getElementById("updateDueDate").value = task.due_date;
            document.getElementById("updatePriority").value = task.priority;
            document.getElementById("updateAssignee").value = task.assignee;
            document.getElementById("updateModal").style.display = "block";
        }

        function closeUpdateModal() {
            document.getElementById("updateModal").style.display = "none";
        }

        // Submit the form using Fetch API
        function submitUpdateTask() {
            const form = document.getElementById("updateTaskForm");
            const formData = new FormData(form);

            fetch("updatetask.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    document.getElementById("updateMessage").innerText = data.message;
                    setTimeout(() => {
                        location.reload(); // Reload
                    }, 1000);
                } else {
                    document.getElementById("updateMessage").innerText = "Error: " + data.message;
                }
            })
            .catch(error => {
                document.getElementById("updateMessage").innerText = "Error: " + error.message;
            });
        }
      </script>
