<?php
include '../../sql_queries/db.php';
?>

<form method="POST" id="updateteamform" class="customizeforms" enctype="multipart/form-data">
    <div class="input-container">
        <select name="team_id" id="team_id" required onchange="loadTeamData(this.value)">
            <option value="" disabled selected>Select Team to Update</option>
            <?php
            $sql_teams = "SELECT id, team_name FROM teams WHERE company_id = ? ORDER BY team_name";
            $stmt = $conn->prepare($sql_teams);
            $stmt->bind_param("i", $companyId);
            $stmt->execute();
            $result_teams = $stmt->get_result();

            if ($result_teams->num_rows > 0) {
                while ($team = $result_teams->fetch_assoc()) {
                    echo '<option value="' . $team['id'] . '">' . htmlspecialchars($team['team_name']) . '</option>';
                }
            }
            $stmt->close();
            ?>
        </select>
        <label for="team_id">Select Team:</label>
    </div>

    <div class="input-container">
        <input type="text" name="team_name" id="team_name" required placeholder=" " onblur="checkTeamName(this.value)" disabled>
        <label for="team_name">New Team Name:</label>
        <span id="team_name_error" style="color: red; display: none;">Team name already exists</span>
    </div>

    <div class="input-container">
        <textarea name="team_description" id="team_description" rows="4" placeholder="Team Description (Roles and Functions):" disabled></textarea>
    </div>

    <div class="input-container">
        <select name="team_leader_id" id="team_leader_id" required onchange="updateTeamMembers()" disabled>
            <option value="" disabled selected>Select New Team Leader</option>
            <?php
            $sql_employees = "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM employees WHERE company_id = ? ORDER BY full_name";
            $stmt = $conn->prepare($sql_employees);
            $stmt->bind_param("i", $companyId);
            $stmt->execute();
            $result_employees = $stmt->get_result();

            if ($result_employees->num_rows > 0) {
                while ($employee = $result_employees->fetch_assoc()) {
                    echo '<option value="' . $employee['id'] . '">' . htmlspecialchars($employee['full_name']) . '</option>';
                }
            }
            $stmt->close();
            ?>
        </select>
        <label for="team_leader_id">New Team Leader:</label>
    </div>

    <div class="input-container">
        <select name="team_members[]" id="team_members" multiple disabled>
            <option value="" disabled>Select Team Members</option>
            <?php
            $stmt = $conn->prepare($sql_employees);
            $stmt->bind_param("i", $companyId);
            $stmt->execute();
            $result_employees = $stmt->get_result();

            if ($result_employees->num_rows > 0) {
                while ($employee = $result_employees->fetch_assoc()) {
                    echo '<option value="' . $employee['id'] . '">' . htmlspecialchars($employee['full_name']) . '</option>';
                }
            }
            $stmt->close();
            ?>
        </select>
        <label for="team_members">Team Members:</label>
    </div>

    <div class="input-container">
        <select name="project_id" id="project_id" required disabled>
            <option value="" disabled selected>Select Project</option>
            <?php
            $sql_projects = "SELECT id, projectName FROM projects WHERE company_id = ? ORDER BY projectName";
            $stmt = $conn->prepare($sql_projects);
            $stmt->bind_param("i", $companyId);
            $stmt->execute();
            $result_projects = $stmt->get_result();

            if ($result_projects->num_rows > 0) {
                while ($project = $result_projects->fetch_assoc()) {
                    echo '<option value="' . $project['id'] . '">' . htmlspecialchars($project['projectName']) . '</option>';
                }
            }
            $stmt->close();
            ?>
        </select>
        <label for="project_id">Project:</label>
    </div>

    <input type="hidden" name="company_id" id="company_id" value="<?php echo $companyId; ?>">
    <input type="submit" value="Update Team" disabled>
</form>

<script>
const teamFetchOverlay = document.createElement('div');
teamFetchOverlay.id = 'teamFetchLoaderModal';
teamFetchOverlay.innerHTML = `
    <div class="team-fetch-modal-content-custom">
        <div id="team-fetch-loader-custom"></div>
        <div id="team-fetch-parse-status-custom" style="display: none; text-align: center; margin-top: 10px;"></div>
    </div>
`;
document.body.appendChild(teamFetchOverlay);

// Create and add CSS for the modal and loader
const teamFetchStyle = document.createElement('style');
teamFetchStyle.textContent = `
    #teamFetchLoaderModal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .team-fetch-modal-content-custom {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    #team-fetch-loader-custom {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: teamFetchSpin 1s linear infinite;
    }
    @keyframes teamFetchSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(teamFetchStyle);

// Show modal function
function showTeamFetchModal() {
    teamFetchOverlay.style.display = 'block';
}

function loadTeamData(teamId) {
    // Show loader overlay
    showTeamFetchModal();

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var teamData = JSON.parse(this.responseText);
                document.getElementById('team_name').value = teamData.team_name;
                document.getElementById('team_description').value = teamData.team_description;
                document.getElementById('team_leader_id').value = teamData.team_leader_id;
                document.getElementById('project_id').value = teamData.project_id;
                console.log(teamData);
                
                var teamMembersSelect = document.getElementById('team_members');
                teamData.team_members.forEach(function(memberId) {
                    for (var i = 0; i < teamMembersSelect.options.length; i++) {
                        if (teamMembersSelect.options[i].value == memberId) {
                            teamMembersSelect.options[i].selected = true;
                            break;
                        }
                    }
                });

                // Enable all form elements
                var formElements = document.getElementById('updateteamform').elements;
                for (var i = 0; i < formElements.length; i++) {
                    formElements[i].disabled = false;
                }

                updateTeamMembers();
            } catch (e) {
                console.error("Error parsing JSON:", e);
            } finally {
                // Hide loader overlay
                teamFetchOverlay.style.display = 'none';
            }
        }
    };
    xhr.open("GET", "getteamdata.php?team_id=" + encodeURIComponent(teamId), true);
    xhr.send();
}

function checkTeamName(teamName) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText === "exists") {
                document.getElementById("team_name_error").style.display = "block";
                document.getElementById("team_name").setCustomValidity("Team name already exists");
            } else {
                document.getElementById("team_name_error").style.display = "none";
                document.getElementById("team_name").setCustomValidity("");
            }
        }
    };
    xhr.open("GET", "check_team_name.php?team_name=" + encodeURIComponent(teamName) + "&company_id=<?php echo $companyId; ?>", true);
    xhr.send();
}

function updateTeamMembers() {
    var teamLeaderSelect = document.getElementById('team_leader_id');
    var teamMembersSelect = document.getElementById('team_members');
    var selectedLeaderId = teamLeaderSelect.value;

    // Store currently selected members
    var selectedMembers = Array.from(teamMembersSelect.selectedOptions).map(option => option.value);

    // Enable all options
    for (var i = 0; i < teamMembersSelect.options.length; i++) {
        teamMembersSelect.options[i].disabled = false;
    }

    // If a team leader is selected, disable that option in team members
    if (selectedLeaderId) {
        for (var i = 0; i < teamMembersSelect.options.length; i++) {
            if (teamMembersSelect.options[i].value === selectedLeaderId) {
                teamMembersSelect.options[i].disabled = true;
                teamMembersSelect.options[i].selected = false;
                break;
            }
        }
    }

    // Reselect previously selected members (except the new leader)
    for (var i = 0; i < teamMembersSelect.options.length; i++) {
        if (selectedMembers.includes(teamMembersSelect.options[i].value) && teamMembersSelect.options[i].value !== selectedLeaderId) {
            teamMembersSelect.options[i].selected = true;
        }
    }
}

document.getElementById('updateteamform').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var updateTeamData = new FormData(this);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'updateteams.php', true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Request successful
            alert(xhr.responseText);
            // Handle the response as needed
        } else {
            // Request failed
            console.error('Error:', xhr.status);
        }
    };
    
    xhr.onerror = function() {
        console.error('Request failed');
    };
    
    xhr.send(updateTeamData);
});
</script>
