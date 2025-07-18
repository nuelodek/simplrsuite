<?php
include '../../sql_queries/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function updateTeam($conn, $teamData) {
    $sql = "UPDATE teams SET team_name = ?, team_description = ?, team_leader_id = ?, company_id = ?, project_id = ?, team_members = ? WHERE team_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiissi", $teamData['name'], $teamData['description'], $teamData['leader_id'], $teamData['company_id'], $teamData['project_id'], $teamData['members_json'], $teamData['team_id']);
    return $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teamData = [
        'team_id' => $_POST['team_id'],
        'name' => $_POST['team_name'],
        'description' => $_POST['team_description'],
        'leader_id' => $_POST['team_leader_id'],
        'company_id' => $_POST['company_id'],
        'project_id' => $_POST['project_id'],
        'members' => isset($_POST['team_members']) ? array_diff($_POST['team_members'], [$_POST['team_leader_id']]) : [],
    ];
    $teamData['members_json'] = json_encode($teamData['members']);

    if (updateTeam($conn, $teamData)) {
        echo "{$teamData['name']} updated successfully";
    } else {
        echo "Error updating team: " . $conn->error;
    }
}
