<?php
// Include database connection
require_once '../../sql_queries/db.php';

// Check if team_id is provided
if (isset($_GET['team_id'])) {
    $team_id = intval($_GET['team_id']);

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $team = $result->fetch_assoc();

        

        // Fetch all employees for team leader and members selection
        $stmt_employees = $conn->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM employees WHERE company_id = ?");
        $stmt_employees->bind_param("i", $team['company_id']);
        $stmt_employees->execute();
        $result_employees = $stmt_employees->get_result();

        $employees = array();
        while ($employee = $result_employees->fetch_assoc()) {
            $employees[] = $employee;
        }

        // Fetch all projects for project selection
        $stmt_projects = $conn->prepare("SELECT id, project_name FROM projects WHERE company_id = ?");
        $stmt_projects->bind_param("i", $team['company_id']);
        $stmt_projects->execute();
        $result_projects = $stmt_projects->get_result();

        $projects = array();
        while ($project = $result_projects->fetch_assoc()) {
            $projects[] = $project;
        }

        // Prepare the response
        $response = array(
            'team_name' => $team['team_name'],
            'team_description' => $team['team_description'],
            'team_leader_id' => $team['team_leader_id'],
            'project_id' => $team['project_id'],
            'team_members' => $team_members,
            'all_employees' => $employees,
            'all_projects' => $projects
        );

        // Send the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Team not found'));
    }

    $stmt->close();
    $stmt_members->close();
    $stmt_employees->close();
    $stmt_projects->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'No team ID provided'));
}

$conn->close();
