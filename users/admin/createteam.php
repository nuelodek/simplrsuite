<?php
include '../../sql_queries/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function createTeam($conn, $teamData) {
    $sql = "INSERT INTO teams (team_name, team_description, team_leader_id, company_id, project_id, team_members) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiisi", $teamData['name'], $teamData['description'], $teamData['leader_id'], $teamData['company_id'], $teamData['project_id'], $teamData['members_json']);
    return $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teamData = [
        'name' => $_POST['team_name'],
        'description' => $_POST['team_description'],
        'leader_id' => $_POST['team_leader_id'],
        'company_id' => $_POST['company_id'],
        'project_id' => $_POST['project_id'],
        'members' => isset($_POST['team_members']) ? array_diff($_POST['team_members'], [$_POST['team_leader_id']]) : [],
    ];
    $teamData['members_json'] = json_encode($teamData['members']);

    if (createTeam($conn, $teamData)) {
        echo "{$teamData['name']} created successfully";
    } else {
        echo "Error creating team: " . $conn->error;
    }
}
