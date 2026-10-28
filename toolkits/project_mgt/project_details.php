<?php
// Include the database connection file
include '../../sql_queries/db.php';

// Start session to retrieve company information
session_start();

// Check if companyId is set in the session
if (!isset($_SESSION['company_id'])) {
    header("Location: login.html");
    exit();
}

// Retrieve project_id from the query parameter
$projectId = is_string($_GET['project_id'] ?? null) ? $_GET['project_id'] : '';

// Fetch project details
$stmt_project = $conn->prepare("SELECT projectName, startDate, endDate, status, project_manager_id
                FROM projects
                WHERE company_id = ? AND unique_id = ? AND archived_at IS NULL");
$stmt_project->bind_param('is',$_SESSION['company_id'],$projectId);
$stmt_project->execute();
$project = $stmt_project->get_result()->fetch_assoc();
if (!$project) {
    http_response_code(404);
    exit('Project not found.');
}

// Fetch tasks associated with the project
$stmt_tasks = $conn->prepare("SELECT id, title, assignee, due_date, priority, status
              FROM tasks
              WHERE company_id = ? AND project_id = ? AND archived_at IS NULL
              ORDER BY due_date ASC");
$stmt_tasks->bind_param('is',$_SESSION['company_id'],$projectId);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();

// Fetch employees for the assignee dropdown
$sql_employees = "SELECT * FROM employees WHERE company_id = ? AND archived_at IS NULL";
$stmt_employees = $conn->prepare($sql_employees);
$stmt_employees->bind_param('i',$_SESSION['company_id']);
$stmt_employees->execute();
$result_employees = $stmt_employees->get_result();

// Retrieve employee data and store in an associative array
$employees = [];
while ($employee = $result_employees->fetch_assoc()) {
    $employees[$employee['unique_id']] = $employee['first_name'] . ' ' . $employee['last_name'];
}


// Close the database connection
// $conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/project_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Project Details</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap');

        html,
        body {
            font-family: "DM Sans", sans-serif;
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Layout styling */
        .project-details-container {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .project-info {
            position: relative;
            display: flex;
            /* justify-content: space-between; */
            /* align-items: flex-start; */
            /* gap: 20px; */
            border-bottom: 2px solid #ddd;
        }

        .project-info span{
            font-weight: bold;
            font-size: 22px;
        }

        /* Project name styling */
        .project-tag {
            position: absolute;
            top: 0px;
            left: 0px;
            background-color: #898121;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            border-radius: 5px 0px 5px 0px;
            font-size: 24px;
            margin: 0;
        }

        /* Styling for project details to align vertically */
        .project-details {
            margin-top: 20px;
            display: flex;
            flex-direction: row;
            gap: 80px; 
        }

        /* Icon styling in each project detail */
        .project-details p {
            display: flex;
            align-items: end;
            font-size: 16px;
        }

        .project-details i {
            font-size: 1.7rem;
            margin-right: 8px;
            color: #898121;
            /* Customize icon color */
        }

        /* .project-info h2 {
            display: inline-block;
            word-break: break-word;
            max-width: 25ch;
            white-space: normal;
        } */

        .project-info,
        .task-list {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            padding: 20px;
        }

        .task-list h3 {
            color: #2c3e50;
        }

        .task-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .task-list th,
        .task-list td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .task-list tr:last-child td {
            border-bottom: none;
        }

        .task-status {
            font-weight: bold;
        }

        .completed {
            color: #2ecc71;
        }

        .in-progress {
            color: #e67e22;
        }

        .pending {
            color: #e74c3c;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            max-width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-content input {
            width: 200px;
            max-width: 80%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        select {
            width: 200px;
            max-width: 80%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .close-button {
            font-size: 20px;
            cursor: pointer;
            color: #888;
            float: right;
        }

        .icon-btn {
            background-color: #2ecc71;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .icon-btn:hover {
            background-color: #27ae60;
        }

        .icon-btn img {
            width: 25px;
            height: 25px;
            cursor: pointer;
        }

        .title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;

        }
    </style>
</head>

<body>
    <div class="project-details-container">
        <div class="project-info">
            <span class="project-tag"><?php echo $project['projectName']; ?></span>
            <div class="project-details">
                <p><i class="fa fa-calendar-alt"></i> <strong>Start-Date:</strong> <?php echo $project['startDate']; ?></p>
                <p><i class="fa fa-calendar-check"></i> <strong>End-Date:</strong> <?php echo $project['endDate']; ?></p>
                <p><i class="fa fa-tasks"></i> <strong>Status:</strong> <?php echo $project['status']; ?></p>
                <p><i class="fa fa-user-tie"></i> <strong>Team-Lead:</strong>
                    <?php
                    // Fetch project manager name from employees table
                    $stmt_manager = $conn->prepare("SELECT first_name, last_name FROM employees WHERE company_id = ? AND unique_id = ? AND archived_at IS NULL");
                    $stmt_manager->bind_param('is',$_SESSION['company_id'],$project['project_manager_id']);
                    $stmt_manager->execute();
                    $result_manager = $stmt_manager->get_result();
                    $manager = $result_manager->fetch_assoc();
                    echo htmlspecialchars(trim(($manager['first_name'] ?? '') . ' ' . ($manager['last_name'] ?? '')));
                    ?>
                </p>
            </div>

        </div>

        <div class="task-list">
            <div class="title-bar">
                <h3>Tasks for <?php echo $project['projectName']; ?></h3>
                <!-- Button to add tasks -->
                <button class="icon-btn" id="add-task-button" onclick="toggleModal()"><img src="https://www.svgrepo.com/show/532997/plus-large.svg"></button>
            </div>
            <!-- Modal for Adding Task -->
            <div id="add-task-modal" class="modal-overlay" style="display: none;">
                <div class="modal-content">
                    <span class="close-button" onclick="toggleModal()">&times;</span>
                    <h3>Add New Task</h3>
                    <form action="add_task.php" method="POST">
                        <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
                        <label for="title">Task Title:</label>
                        <input type="text" id="title" name="title" required>

                        <label for="assignee">Assignee:</label>
                        <select id="assignee" name="assignee" required>
                            <option value="John Doe">Select an assignee</option>
                            <?php foreach ($employees as $unique_id => $name) { ?>
                                <option value="<?php echo $unique_id; ?>">
                                    <?php echo $name; ?>
                                </option>
                            <?php } ?>
                        </select>

                        <label for="due_date">Due Date:</label>
                        <input type="date" id="due_date" name="due_date" required>

                        <label for="priority">Priority:</label>
                        <select id="priority" name="priority">
                            <option value="low">low</option>
                            <option value="medium">medium</option>
                            <option value="high">high</option>
                        </select>

                        <input type="submit" value="Add Task">
                    </form>
                </div>
            </div>
            <!-- End of Modal -->

            <!-- Table to display tasks -->
            <!-- Table to display tasks -->
            <table>
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Assignee</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($task = $result_tasks->fetch_assoc()) {
                        $statusClass = strtolower(str_replace(" ", "-", $task['status']));
                        $taskAssigneeId = $task['assignee'];
                        $taskAssigneeName = $employees[$taskAssigneeId] ?? 'Unknown'; // Show name or 'Unknown' if not found
                        echo "<tr>
                <td>{$task['title']}</td>
                <td>
                    <select onchange='updateAssignee(\"{$task['id']}\", this.value)'>";
                        foreach ($employees as $unique_id => $name) {
                            $selected = $unique_id === $taskAssigneeId ? 'selected' : '';
                            echo "<option value='$unique_id' $selected>$name</option>";
                        }
                        echo    "</select>
                </td>
                <td>{$task['due_date']}</td>
                <td>
                    <select onchange='updateStatus(\"{$task['id']}\", this.value)'>
                        <option value='pending' " . ($task['status'] === 'pending' ? 'selected' : '') . ">pending</option>
                        <option value='ongoing' " . ($task['status'] === 'ongoing' ? 'selected' : '') . ">ongoing</option>
                        <option value='completed' " . ($task['status'] === 'completed' ? 'selected' : '') . ">completed</option>
                    </select>
                </td>
                <td>{$task['priority']}</td>
            </tr>";
                    } ?>
                </tbody>
            </table>

        </div>
    </div>

    <script src="../../scripts/projdetails.js"></script>
</body>

</html>