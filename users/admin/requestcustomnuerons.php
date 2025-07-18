
<?php 
include '../../sql_queries/db.php';
?>



<form id="requestCustomNeuronForm" method="POST" class="customizeforms">
  <div class="input-container">
    <input type="text" id="neuronName" name="neuronName" required placeholder=" ">
    <label for="neuronName">Requested Neuron Name:</label>
  </div>

  <div class="input-container">
    <textarea id="neuronDescription" name="neuronDescription" rows="3" placeholder="Describe the custom neuron's function"></textarea>
    <label for="neuronDescription">Custom Neuron Description:</label>
  </div>

  <div class="input-container">
    <select id="executionType" name="executionType" required>
      <option value="once">Run Once</option>
      <option value="recurring">Recurring</option>
      <option value="arbitrary">Arbitrary</option>
    </select>
    <label for="executionType">Desired Execution Type:</label>
  </div>

  <div id="onceExecution" class="execution-options" style="display:none;">
    <div class="input-container">
      <input type="datetime-local" id="onceDateTime" name="onceDateTime">
      <label for="onceDateTime">Desired Date and Time:</label>
    </div>
  </div>

  <div id="recurringExecution" class="execution-options" style="display:none;">
    <div class="input-container">
      <select id="recurringInterval" name="recurringInterval">
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
      </select>
      <label for="recurringInterval">Desired Interval:</label>
    </div>
    <div class="input-container">
      <input type="time" id="recurringTime" name="recurringTime">
      <label for="recurringTime">Desired Time:</label>
    </div>
    <div id="weeklyOptions" style="display:none;">
      <div class="input-container">
        <select id="weekDay" name="weekDay" multiple>
          <option value="1">Monday</option>
          <option value="2">Tuesday</option>
          <option value="3">Wednesday</option>
          <option value="4">Thursday</option>
          <option value="5">Friday</option>
          <option value="6">Saturday</option>
          <option value="0">Sunday</option>
        </select>
        <label for="weekDay">Desired Day(s) of the week:</label>
      </div>
    </div>
  </div>

  <div id="arbitraryExecution" class="execution-options" style="display:none;">
    <div class="input-container">
      <textarea id="arbitrarySchedule" name="arbitrarySchedule" rows="3" placeholder="Describe the desired arbitrary schedule"></textarea>
      <label for="arbitrarySchedule">Arbitrary Schedule:</label>
    </div>
  </div>

  <div class="input-container">
    <input type="hidden" id="company_id" name="company_id" value="<?php echo htmlspecialchars($companyId); ?>" required>
  </div>

  <input type="submit" value="Request Custom Neuron">
  <p><small><i class="fas fa-info-circle"></i> Before requesting a neuron, please check our existing automations as we frequently update them.</small></p>

</form>

<script>
document.getElementById('requestCustomNeuronForm').addEventListener('submit', function(e) {
  e.preventDefault();

  var requestCustomNeuronData = new FormData(this);

  fetch('requestcustomneuron.php', {
    method: 'POST',
    body: requestCustomNeuronData
  })
  .then(response => response.text())
  .then(data => {
    alert(data);
    console.log(data);
    document.getElementById('requestCustomNeuronForm').reset();
  })
  .catch(error => {
    console.error('Error:', error);
  });
});

document.getElementById('executionType').addEventListener('change', function() {
  var executionType = this.value;
  document.querySelectorAll('.execution-options').forEach(function(el) {
    el.style.display = 'none';
  });
  document.getElementById(executionType + 'Execution').style.display = 'block';

  if (executionType === 'recurring') {
    document.getElementById('recurringInterval').dispatchEvent(new Event('change'));
  }
});

document.getElementById('recurringInterval').addEventListener('change', function() {
  var interval = this.value;
  if (interval === 'weekly') {
    document.getElementById('weeklyOptions').style.display = 'block';
  } else {
    document.getElementById('weeklyOptions').style.display = 'none';
  }
});

// Initialize the form
window.addEventListener('load', function() {
  document.getElementById('executionType').dispatchEvent(new Event('change'));
});
</script>
