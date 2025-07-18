<?php 
 
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
 ?>
 
  <form method="POST" action="revoketools.php" class="customizeforms">
      <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($company_id, ENT_QUOTES, 'UTF-8'); ?>">
      
      
      
             <div class="input-container">
      <select name="employee_id" id="employee_idrev" required>
          <option value="">Select Employee</option>
          <!-- Options will be populated by JavaScript -->
      </select>
      <label for="employee_id">Select Employee:</label>
</div>

             <div class="input-container">

      <select name="tool_ids[]" id="tool_idsrev" multiple required>
          <!-- Options will be populated by JavaScript -->
      </select>

      <label for="tool_ids">Select Tools to Revoke:</label>
</div>
      <button type="submit" name="revoke" style="padding: 10px; background-color: #f44336; color: white; border: none; cursor: pointer; margin-top: 10px;">Revoke Tools</button>
  </form>


<script>
    // Function to fetch employees and populate the employee dropdown
    function fetchEmployees() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_employees.php', true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var employees = JSON.parse(xhr.responseText);
                    var employeeSelect = document.getElementById('employee_idrev');
                    employeeSelect.innerHTML = '<option value="">Select Employee</option>'; // Reset options
                    employees.forEach(function(employee) {
                        var option = document.createElement('option');
                        option.value = employee.id;
                        option.textContent = employee.first_name + ' ' + employee.last_name;
                        employeeSelect.appendChild(option);
                    });
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    alert('Failed to fetch employees. Please try again later.');
                }
            } else {
                console.error('XHR error:', xhr.statusText);
                alert('Failed to fetch employees. Please try again later.');
            }
        };
        xhr.onerror = function() {
            console.error('XHR error:', xhr.statusText);
            alert('Failed to fetch employees. Please try again later.');
        };
        xhr.send();
    }

    // Function to fetch tools for a selected employee
    function fetchTools() {
        var employee_id = document.getElementById('employee_idrev').value;
        var company_id = document.querySelector('input[name="company_id"]').value;
        var toolSelect = document.getElementById('tool_idsrev');

        if (employee_id) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_tools.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var tools = JSON.parse(xhr.responseText);
                        toolSelect.innerHTML = ''; // Clear existing options
                        if (tools.length > 0) {
                            tools.forEach(function(tool) {
                                var option = document.createElement('option');
                                option.value = tool.id;
                                option.textContent = tool.tool_name;
                                toolSelect.appendChild(option);
                            });
                        } else {
                                var option = document.createElement('option');
                                  option.value = '';
                                  option.textContent = 'No tools assigned';
                                  option.disabled = true;
                                  toolSelect.appendChild(option);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                        alert('Failed to fetch tools. Please try again later.');
                    }
                } else {
                    console.error('XHR error:', xhr.statusText);
                    alert('Failed to fetch tools. Please try again later.');
                }
            };

            xhr.onerror = function() {
                console.error('XHR error:', xhr.statusText);
                alert('Failed to fetch tools. Please try again later.');
            };

            var params = 'fetch_tools=true&employee_id=' + encodeURIComponent(employee_id) + '&company_id=' + encodeURIComponent(company_id);
            xhr.send(params);
        } else {
            toolSelect.innerHTML = ''; // Clear options
            var option = document.createElement('option');
            option.value = '';
            option.disabled = true;
            option.textContent = 'Select an employee to show list of assigned tools you can revoked';
            toolSelect.appendChild(option);
        }
    }

    // Initialize the employee dropdown on page load
    document.addEventListener('DOMContentLoaded', fetchEmployees);

    // Add event listener to fetch tools when employee is selected
    document.getElementById('employee_idrev').addEventListener('change', fetchTools);
</script>
