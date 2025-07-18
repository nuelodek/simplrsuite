<?php 
// session_start();    
include '../../sql_queries/db.php';
?>

<form id="createTaskForm" method="POST" enctype="multipart/form-data" class="customizeforms">
  <div class="input-container">
      <select name="project_id" id="project_id" required>
          <?php
          $projects_query = "SELECT id, projectName FROM projects WHERE company_id = ?";
          $stmt = $conn->prepare($projects_query);
          $stmt->bind_param("i", $companyId);
          $stmt->execute();
          $projects_result = $stmt->get_result();
          if ($projects_result->num_rows > 0) { 
              while ($project = $projects_result->fetch_assoc()) {
                  echo "<option value='" . $project['id'] . "'>" . htmlspecialchars($project['projectName']) . "</option>";
              } 
          } else { 
              echo "<option value='' disabled selected>No projects found for your organization</option>";
          }
          $stmt->close();
          ?>
      </select>
      <label for="project_id">Select Project:</label>
  </div>   
  
  <div class="taskflex" style="display: flex; flex-direction: row; gap: 5px;">
      <div class="input-container" style="flex: 1;">
          <input type="text" id="taskTitle" name="taskTitle" required placeholder=" ">
          <label for="taskTitle">Task Title:</label>
      </div>
  
      <div class="buttonflex">
          <button class="genetaskbutton" onclick="xologenerate(); event.preventDefault();" style="padding: 11px 10px; background-color: black; color: white; outline: none; border-radius: 5px; border: none; white-space: nowrap;">Generate Task with Xolo</button>
      </div>
  </div>

  <div class="input-container">
      <textarea id="taskDescription" name="taskDescription" rows="3" placeholder="Describe your task" style="font-size: 10px; color: green;"></textarea>
      <!-- <label for="taskDescription">Task Description:</label> -->
  </div>
    
  <div class="input-container">
      <input id="taskFile" type="file" name="taskfile[]" placeholder=" " multiple>
      <label for="taskFile">Attachments:</label>
  </div>
    
  <div class="input-container">
      <input type="date" id="dueDate" name="dueDate" required>
      <label for="dueDate">Due Date:</label> 
  </div>

  <div class="input-container">
      <select id="priority" name="priority" required>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
      </select>
      <label for="priority">Priority:</label>
  </div>

  <div class="input-container"> 
      <select id="assignee" name="assignee" required>
          <option value="" disabled selected>Select an employee</option>
          <?php include '../../users/admin/fetchemployeesfortask.php';?>
      </select>
      <label for="assignee">Assign:</label>
  </div>

  <div class="input-container">
      <input type="hidden" id="taskcompany_id" name="company_id" value="<?php echo htmlspecialchars($companyId); ?>" required>
  </div>
      
  <input type="submit" value="Create Task">
</form>
  
<script>
document.getElementById('createTaskForm').addEventListener('submit', function(e) {
  e.preventDefault();
      
  var createTaskData = new FormData(this);
      
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'createtask.php', true);
      
  xhr.onload = function() {
      if (xhr.status === 200) {
          alert(xhr.responseText);
          console.log(xhr.responseText);
          document.getElementById('createTaskForm').reset();
      } else {
          console.error('Error:', xhr.status);
      }
  };
      
  xhr.onerror = function() {
      console.error('Request failed');
  };
      
  xhr.send(createTaskData);
});
</script>
