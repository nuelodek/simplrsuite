<?php 
include '../../sql_queries/db.php';
?>

<form id="createNeuronForm" method="POST" class="customizeforms">
  <div class="input-container">
    <input type="text" id="neuronName" name="neuronName" required placeholder=" ">
    <label for="neuronName">Neuron Name:</label>
  </div>

  <div class="input-container">
    <select name="neuronType" id="neuronType" required>
      <option value="analyzeAIAnalytics">Analyze AI Analytics</option>
      <option value="gatherEmployeeData">Gather Employee Data</option>
      <option value="financialForcast">Financial Forecast</option>  
      <option value="projectAnalysis">Project Analysis</option>
      <option value="companySonar">CompanySonar</option>
      <option value="calcuateFinancialRatio">Calculate Financial Ratios</option>
      <option value="productAdoption">Product Adoption</option>
      <option value="employeeTurnover">Employee Turnover</option>
      <option value="cyberThreatThreshold">Cyber Threat Threshold</option>
      <option value="employeeAttendance">Employee Attendance</option>
      <option value="employeeContribution">Employee Contribution</option>
      <option value="cashflowAnalysis">Cashflow Analysis</option>
      <option value="toolPerformance">Tool Performance</option>
      <option value="marketTrendAnalysis">Market Trend Analysis</option>
      <option value="customerSentimentAnalysis">Customer Sentiment Analysis</option>
      <option value="supplyChainOptimization">Supply Chain Optimization</option>
      <option value="riskAssessment">Risk Assessment</option>
      <option value="competitorIntelligence">Competitor Intelligence</option>
      <option value="productInventoryManagement">Product Inventory Management</option>
      <option value="salesForecast">Sales Forecast</option>
      <option value="customerChurnPrediction">Customer Churn Prediction</option>
      <option value="fraudDetection">Fraud Detection</option>
      <option value="qualityControlAnalysis">Quality Control Analysis</option>
      <option value="predictiveMaintenance">Predictive Maintenance</option>
      <option value="demandForecasting">Demand Forecasting</option>
      <option value="priceOptimization">Price Optimization</option>
      <option value="customerSegmentation">Customer Segmentation</option>
      <option value="resourceAllocation">Resource Allocation</option>
      <option value="brandSentimentAnalysis">Brand Sentiment Analysis</option>
      <option value="productRecommendation">Product Recommendation</option>
      <option value="creditRiskAssessment">Credit Risk Assessment</option>
      <option value="anomalyDetection">Anomaly Detection</option>
      <option value="marketBasketAnalysis">Market Basket Analysis</option>
      <option value="energyConsumptionOptimization">Energy Consumption Optimization</option>
      <option value="talentAcquisitionAnalysis">Talent Acquisition Analysis</option>
      <option value="customerLifetimeValuePrediction">Customer Lifetime Value Prediction</option>
      <option value="socialMediaAnalytics">Social Media Analytics</option>
      <option value="warehouseOptimization">Warehouse Optimization</option>
      <option value="processOptimization">Process Optimization</option>
      <option value="customerFeedbackAnalysis">Customer Feedback Analysis</option>
      <option value="employeePerformanceAnalysis">Employee Performance Analysis</option>
      <option value="marketShareAnalysis">Market Share Analysis</option>
      <option value="productLifecycleManagement">Product Lifecycle Management</option>
      <option value="revenueOptimization">Revenue Optimization</option>
      <option value="customerRetentionStrategy">Customer Retention Strategy</option>
      <option value="supplierPerformanceAnalysis">Supplier Performance Analysis</option>
      <option value="businessProcessAutomation">Business Process Automation</option>
      <option value="realTimeDecisionSupport">Real-Time Decision Support</option>    
      
      
      
    </select>
    <label for="neuronType">Neuron Type:</label>
  </div>

  <div class="input-container">
    <textarea id="neuronDescription" name="neuronDescription" rows="3" placeholder="Describe the neuron's function"></textarea>
  </div>

  <div class="input-container">
    <select id="executionType" name="executionType" required>
      <option value="once">Run Once</option>
      <option value="recurring">Recurring</option>
      <option value="arbitrary">Arbitrary</option>
    </select>
    <label for="executionType">Execution Type:</label>
  </div>

  <div id="onceExecution" class="execution-options" style="display:none;">
    <div class="input-container">
      <input type="datetime-local" id="onceDateTime" name="onceDateTime">
      <label for="onceDateTime">Date and Time:</label>
    </div>
  </div>

  <div id="recurringExecution" class="execution-options" style="display:none;">
    <div class="input-container">
      <select id="recurringInterval" name="recurringInterval">
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
      </select>
      <label for="recurringInterval">Interval:</label>
    </div>
    <div class="input-container">
      <input type="time" id="recurringTime" name="recurringTime">
      <label for="recurringTime">Time:</label>
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
        <label for="weekDay">Day(s) of the week:</label>
      </div>
    </div>
  </div>

  <div id="arbitraryExecution" class="execution-options" style="display:none;">
    <div class="input-container">
      <textarea id="arbitrarySchedule" name="arbitrarySchedule" rows="3" placeholder="Describe the arbitrary schedule"></textarea>
      <label for="arbitrarySchedule">Arbitrary Schedule:</label>
    </div>
  </div>

  <div class="input-container">
    <input type="hidden" id="company_id" name="company_id" value="<?php echo htmlspecialchars($companyId); ?>" required>
  </div>

  <input type="submit" value="Create Neuron">
</form>
<script>
var xhr = new XMLHttpRequest();
document.getElementById('createNeuronForm').addEventListener('submit', function(e) {
  e.preventDefault();

  var createNeuronData = new FormData(this);

  xhr.open('POST', 'createneuron.php', true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      alert(xhr.responseText);
      console.log(xhr.responseText);
      document.getElementById('createNeuronForm').reset();
    } else {
      console.error('Error:', xhr.status);
    }
  };
  xhr.onerror = function() {
    console.error('Error:', xhr.status);
  };
  xhr.send(createNeuronData);
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
