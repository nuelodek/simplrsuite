<?php
include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic
$companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : null;

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch teams for this company
$sql_teams = "SELECT t.id, t.team_name, t.team_description, t.team_leader_id, t.project_id, t.team_members, 
              e.first_name AS leader_first_name, e.last_name AS leader_last_name, 
              p.projectName 
              FROM teams t
              JOIN employees e ON t.team_leader_id = e.id
              JOIN projects p ON t.project_id = p.id
              WHERE t.company_id = '$companyId'";
$result_teams = $conn->query($sql_teams);

if ($result_teams->num_rows == 0) {
    echo '<div style="color: red;">No teams found for this organization.</div>';
} else {
    echo '<table border="0" class="nice-table">
            <tr>
                <th>Team Name</th>
                <th>Team Description</th>
                <th>Team Leader</th>
                <th>Team Members</th>
                <th>Project</th>
                <th>Actions</th>
            </tr>';

    while ($team = $result_teams->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($team['team_name']) . '</td>';
        echo '<td>' . htmlspecialchars($team['team_description']) . '</td>';
        echo '<td>' . htmlspecialchars($team['leader_first_name'] . ' ' . $team['leader_last_name']) . '</td>';
        
        // Fetch and display team members
        $team_members = json_decode($team['team_members'], true);
        echo '<td>';
        if (!empty($team_members)) {
            $sql_members = "SELECT first_name, last_name FROM employees WHERE id IN (" . implode(',', $team_members) . ")";
            $result_members = $conn->query($sql_members);
            while ($member = $result_members->fetch_assoc()) {
                echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '<br>';
            }
        } else {
            echo 'No members';
        }
        echo '</td>';
        
        echo '<td>' . htmlspecialchars($team['projectName']) . '</td>';
        echo '<td>
                <a href="updateteam.php?id=' . $team['id'] . '">Update</a> | 
                <a href="deleteteam.php?id=' . $team['id'] . '" onclick="return confirm(\'Are you sure you want to delete this team?\')">Delete</a>
              </td>';
        echo '</tr>';
    }

    echo '</table>';
}

$conn->close();
?>
