<?php
// Include the database connection file
include '../../sql_queries/db.php';

// Check if the connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch projects and tasks
$sql_projects = "SELECT p.unique_id, p.projectName, p.startDate, p.endDate, p.status, COUNT(t.id) AS task_count 
                 FROM projects p 
                 LEFT JOIN tasks t ON p.unique_id = t.project_id 
                 GROUP BY p.id";
$result_projects = $conn->query($sql_projects);

// fetch employees
$sql_employees = "SELECT unique_id, first_name, last_name FROM employees";
$result_employees = $conn->query($sql_employees);


// Close the database connection
$conn->close();
?>

<style>
    .projects-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        padding: 20px;
    }

    .project-card {
        position: relative;
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background-color: #fff;
        overflow: hidden;
        transition: transform 0.2s;
    }

    .project-card:hover {
        transform: scale(1.02);
    }

    /* Hidden button initially */
    .icon-btn {
        position: absolute;
        bottom: -100%;
        left: 0;
        width: 100%;
        background-color: #20b2aa;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border: none;
        padding: 10px 0;
        border-radius: 0 0 5px 5px;
        cursor: pointer;
        opacity: 0;
        transition: bottom 0.3s ease, opacity 0.3s ease;
    }

    /* Slide up animation on hover */
    .project-card:hover .icon-btn {
        bottom: 0;
        opacity: 1;
    }

    .icon-btn:hover {
        background-color: #179b96;
    }

    /* Button icon adjustments */
    .icon-btn img {
        width: 25px;
        height: 25px;
        cursor: pointer;
    }

    /* Fixed Button Styling */
    #add-project-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 10px 20px;
        background-color: #20b2aa;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: 0.3s ease;
        box-shadow: 3px 3px 4px rgba(0, 0, 0, 0.15);
    }

    #add-project-button:hover {
        background-color: #179b96;
        box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.35);
    }

    #add-project-button img {
        width: 25px;
        height: 25px;
        cursor: pointer;
    }

    /* Modal Overlay */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    /* Modal Content */
    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        max-width: 90%;
        position: relative;
    }

    /* Close Button */
    .close-button {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 24px;
        cursor: pointer;
        color: #333;
    }

    /* Modal Form */
    #add-project-form {
        display: flex;
        flex-direction: column;
    }

    #add-project-form label {
        font-weight: bold;
        margin-bottom: 5px;
    }

    #add-project-form input,
    #add-project-form select {
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    #add-project-form button {
        padding: 10px;
        background-color: #20b2aa;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
</style>

<div class="projects-container">
    <?php
    while ($project = $result_projects->fetch_assoc()) {
        $projectName = $project['projectName'];
        $projectStartDate = $project['startDate'];
        $projectEndDate = $project['endDate'];
        $projectStatus = $project['status'];
        $projectTasksCount = $project['task_count'];
        $projectUniqueId = $project['unique_id'];

        echo "<div class='project-card'>
                <h2>Project Name: $projectName</h2>
                <p>Start Date: $projectStartDate</p>
                <p>End Date: $projectEndDate</p>
                <p>Status: $projectStatus</p>
                <p>Tasks: $projectTasksCount</p>
                <a class='icon-btn' href='project_details.php?project_id=$projectUniqueId'>
                    <img src='https://www.svgrepo.com/show/449333/view-filled.svg' alt='Project Details'>
                </a>
            </div>";
    }
    ?>

    <!-- Fixed Button for Adding New Project -->
    <button id="add-project-button" onclick="toggleProjectModal()"><img src="https://www.svgrepo.com/show/532997/plus-large.svg"></button>

    <!-- Modal for Adding New Project -->
    <div id="add-project-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <span class="close-button" onclick="toggleProjectModal()">&times;</span>
            <h3>Add New Project</h3>
            <form id="add-project-form" action="add_project.php" method="POST">
                <label for="project_name">Project Name:</label>
                <input type="text" id="project_name" name="project_name" required>

                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>

                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>

                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>

                <label for="project_manager">Team Lead:</label>
                <select id="project_manager" name="project_manager" required>
                    <option value="">Select a Team Lead</option>
                    <!-- Add dynamic options here from PHP if necessary -->
                    <?php while ($employee = $result_employees->fetch_assoc()) { ?>
                        <option value="<?php echo $employee['unique_id']; ?>">
                            <?php echo $employee['first_name'];
                            echo " ";
                            echo $employee['last_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <input type="submit" value="Add Project">
            </form>
        </div>
    </div>
    <!-- script -->
    <script src="../../scripts/projdashboard.js"></script>
</div>