    <?php
    include '../../sql_queries/db.php';

    if (isset($_GET['team_id'])) {
        $teamId = intval($_GET['team_id']);
    
        // Prepare the SQL statement
        $sql = "SELECT id, team_name, company_id, team_leader_id, project_id, team_description, team_members, created_at, updated_at
                FROM teams
                WHERE id = ?";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $teamId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $teamData = $result->fetch_assoc();
        
            // Convert team_members string to array
            $teamData['team_members'] = $teamData['team_members'] ? explode(',', $teamData['team_members']) : [];
        
            // Send the team data as JSON
            header('Content-Type: application/json');
            echo json_encode($teamData);
        } else {
            echo json_encode(['error' => 'Team not found']);
        }
    
        $stmt->close();
    } else {
        echo json_encode(['error' => 'No team ID provided']);
    }

    $conn->close();
    ?>
