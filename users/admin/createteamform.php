  <?php
include '../../sql_queries/db.php';

  $companyId = $_SESSION['company_id'];
  ?>
  <form method="POST" id="createteamform" class="customizeforms">
      <div class="input-container">
          <input type="text" name="team_name" id="team_name" required placeholder=" " onblur="checkTeamName(this.value)">
          <label for="team_name">Team Name:</label>
          <span id="team_name_error" class="error-message" style="display: none;">Team name already exists</span>
      </div>

      <div class="input-container">
          <textarea name="team_description" id="team_description" rows="4" placeholder="Team Description (Roles and Functions):"></textarea>
      </div>

      <div class="input-container">
          <select name="team_leader_id" id="team_leader_id" required onchange="updateTeamMembers()">
              <option value="" disabled selected>Select Team Leader</option>
              <?php
              function fetchEmployees($conn, $companyId) {
                  $sql = "SELECT * FROM employees WHERE company_id = ?";
                  $stmt = $conn->prepare($sql);
                  $stmt->bind_param("i", $companyId);
                  $stmt->execute();
                  return $stmt->get_result();
              }

              $result_employees = fetchEmployees($conn, $companyId);

              if ($result_employees->num_rows > 0) {
                  while ($employee = $result_employees->fetch_assoc()) {
                      echo '<option value="' . $employee['id'] . '">' . htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) . '</option>';
                  }
              } else {
                  echo '<option disabled>No employees found for this organization.</option>';
              }
              ?>
          </select>
          <label for="team_leader_id">Team Leader:</label>
      </div>

      <div class="input-container">
          <select name="team_members[]" id="team_members" multiple disabled>
              <option value="" selected>Select Team Members</option>
              <?php
              $result_employees->data_seek(0);
              while ($employee = $result_employees->fetch_assoc()) {
                  echo '<option value="' . $employee['id'] . '">' . htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) . '</option>';
              }
              ?>
          </select>
          <label for="team_members">Team Members:</label>
      </div>

      <div class="input-container">
          <select name="project_id" id="project_id" required>
              <option value="" disabled selected>Select Project</option>
              <?php
              function fetchProjects($conn, $companyId) {
                  $sql = "SELECT * FROM projects WHERE company_id = ? AND (endDate > CURDATE() OR endDate IS NULL)";
                  $stmt = $conn->prepare($sql);
                  $stmt->bind_param("i", $companyId);
                  $stmt->execute();
                  return $stmt->get_result();
              }
              $result_projects = fetchProjects($conn, $companyId);

              if ($result_projects) {
                  $projects = $result_projects->fetch_all(MYSQLI_ASSOC);
                  if (!empty($projects)) {
                      foreach ($projects as $project) {
                          echo '<option value="' . $project['id'] . '">' . htmlspecialchars($project['projectName']) . '</option>';
                      }
                  } else {
                      echo '<option disabled>No active projects found for this organization.</option>';
                  }
              } else {
                  echo '<option disabled>Error fetching projects: ' . $conn->error . '</option>';
              }
              ?>
          </select>
          <label for="project_id">Project:</label>
      </div>

      <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
      <input type="submit" value="Submit">
  </form>

  <script>
  function checkTeamName(teamName) {
      fetch(`check_team_name.php?team_name=${encodeURIComponent(teamName)}&company_id=<?php echo $companyId; ?>`)
          .then(response => response.text())
          .then(data => {
              const errorElement = document.getElementById("team_name_error");
              const inputElement = document.getElementById("team_name");
              if (data === "exists") {
                  errorElement.style.display = "block";
                  inputElement.setCustomValidity("Team name already exists");
              } else {
                  errorElement.style.display = "none";
                  inputElement.setCustomValidity("");
              }
          })
          .catch(error => console.error('Error:', error));
  }

  function updateTeamMembers() {
      const teamLeaderSelect = document.getElementById('team_leader_id');
      const teamMembersSelect = document.getElementById('team_members');
      const selectedLeaderId = teamLeaderSelect.value;

      teamMembersSelect.disabled = !selectedLeaderId;

      if (selectedLeaderId) {
          Array.from(teamMembersSelect.options).forEach(option => {
              option.disabled = option.value === selectedLeaderId;
              option.selected = false;
          });
      }
  }

  document.getElementById('createteamform').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch('createteam.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.text())
      .then(data => {
          alert(data);
          this.reset();
      })
      .catch(error => console.error('Error:', error));
  });
  </script>
