function openBankModal() {
  var bankWalletModal = document.getElementById("bankWalletModal");
  bankWalletModal.style.display = "block";
}

function openReportsModal() {
  var reportsModal = document.getElementById("reportsModal");
  reportsModal.style.display = "block";
}

function openTalentModal() {
  var talentModal = document.getElementById("talentModal");
  talentModal.style.display = "block";
}

function openChangeLanguageModal() {
  var changeLanguageModal = document.getElementById("changeLanguageModal");
  changeLanguageModal.style.display = "block";
}

function openCreateTaskModal() {
  var createTaskModal = document.getElementById("createTaskModal");
  createTaskModal.style.display = "block";
}

function openUpdatesModal() {
  var updatesModal = document.getElementById("updatesModal");
  updatesModal.style.display = "block";
  openUpdatesModal();

}

function openActivityLogsModal() {
  var activityLogsModal = document.getElementById("activityLogsModal");
  activityLogsModal.style.display = "block";
  populateLogs();

}

function openAlertsModal() {
  var alertsModal = document.getElementById("alertsModal");
  alertsModal.style.display = "block";
  loadAlerts();
}

function openKpiModal() {
  var kpiModal = document.getElementById("kpiModal");
  kpiModal.style.display = "block";
}

function openAppIntegrationModal() {
  var appIntegrationModal = document.getElementById("appIntegrationModal");
  appIntegrationModal.style.display = "block";
}

function openEmployeeModal() {
  var employeeModal = document.getElementById("employeeModal");
  employeeModal.style.display = "block";
}

function openProjectModal() {
    var projectModal = document.getElementById("createProjectModal")
      projectModal.style.display = "block";
}

function openVaultModal() {
    var vaultModal = document.getElementById("createVaultModal")
      vaultModal.style.display = "block";
}

function openWidgetModal() {
    var widgetModal = document.getElementById("customizeWidgetModal")
      widgetModal.style.display = "block";
}


function openSubscriptionsModal(){
    var subscriptionsModal= document.getElementById("subscriptionsModal");
    subscriptionsModal.style.display="block";
    
}

function openSettingsModal() {
  var settingsModal = document.getElementById("settingsModal");
  settingsModal.style.display = "block";
}

function openEventsModal() {
  eventsModal.style.display = "block";
}

function openActiveUsersModal() {
  activeUsersModal.style.display = "block";
}


function openAutomationsModal() {
  automationsModal.style.display = "block";
}

function openWorkflowsModal() {
  workflowsModal.style.display = "block";
}

function openMentionsModal() {
  mentionsModal.style.display = "block";
}

function openSIntelligenceModal() {
  sIntelligenceModal.style.display = "block";
}


function openInsightsModal() {
  insightsModal.style.display = "block";
}

function openTeamsModal() {
  teamsModal.style.display = "block";
}


function openCloudModal() {
  cloudModal.style.display = "block";
}

function openNotificationsModal() {
  notificationsModal.style.display = "block";
}

function openMessagesModal() {
  messagesModal.style.display = "block";
}

function openAnalyticsModal() {
  analyticsModal.style.display = "block";
}


// Get the modals
var bankWalletModal = document.getElementById("bankWalletModal");
var reportsModal = document.getElementById("reportsModal");
var talentModal = document.getElementById("talentModal");
var changeLanguageModal = document.getElementById("changeLanguageModal");
var createTaskModal = document.getElementById("createTaskModal");
var updatesModal = document.getElementById("updatesModal");
var activityLogsModal = document.getElementById("activityLogsModal");
var alertsModal = document.getElementById("alertsModal");
var kpiModal = document.getElementById("kpiModal");
var appIntegrationModal = document.getElementById("appIntegrationModal");
var employeeModal = document.getElementById("employeeModal");
var projectModal = document.getElementById("createProjectModal");
var vaultModal = document.getElementById("createVaultModal");
var widgetModal = document.getElementById("customizeWidgetModal");

var subscriptionsModal = document.getElementById("subscriptionsModal");
var eventsModal = document.getElementById("eventsModal");
var activeUsersModal = document.getElementById("activeUsersModal");
var automationsModal = document.getElementById("automationsModal");
var workflowsModal = document.getElementById("workflowsModal");
var mentionsModal = document.getElementById("mentionsModal");
var sIntelligenceModal = document.getElementById("sIntelligenceModal");
var insightsModal = document.getElementById("insightsModal");
var teamsModal = document.getElementById("teamsModal");
var settingsModal = document.getElementById("settingsModal");
var messagesModal = document.getElementById("messagesModal");
var cloudModal = document.getElementById("cloudModal");
var analyticsModal = document.getElementById("analyticsModal");
var notificationsModal = document.getElementById("notificationsModal");

//spans
var subscriptionsSpan = subscriptionsModal.getElementsByClassName("close")[0];
var eventsSpan = eventsModal.getElementsByClassName("close")[0];
var activeUsersSpan = activeUsersModal.getElementsByClassName("close")[0];
var automationsSpan = automationsModal.getElementsByClassName("close")[0];
var workflowsSpan = workflowsModal.getElementsByClassName("close")[0];
var mentionsSpan = mentionsModal.getElementsByClassName("close")[0];
var sIntelligenceSpan = sIntelligenceModal.getElementsByClassName("close")[0];
var insightsSpan = insightsModal.getElementsByClassName("close")[0];
var teamsSpan = teamsModal.getElementsByClassName("close")[0];

var bankWalletSpan = bankWalletModal.getElementsByClassName("close")[0];
var reportsSpan = reportsModal.getElementsByClassName("close")[0];
var talentSpan = talentModal.getElementsByClassName("close")[0];
var changeLanguageSpan = changeLanguageModal.getElementsByClassName("close")[0]; 
var createTaskSpan = createTaskModal.getElementsByClassName("close")[0];
var updatesSpan = updatesModal.getElementsByClassName("close")[0];
var activityLogsSpan = activityLogsModal.getElementsByClassName("close")[0];
var alertsSpan = alertsModal.getElementsByClassName("close")[0];
var kpiSpan = kpiModal.getElementsByClassName("close")[0];
var appIntegrationSpan = appIntegrationModal.getElementsByClassName("close")[0];
var employeeSpan = employeeModal.getElementsByClassName("close")[0];
var projectSpan = projectModal.getElementsByClassName("close")[0];
var vaultSpan = vaultModal.getElementsByClassName("close")[0];
var widgetSpan = widgetModal.getElementsByClassName("close")[0];
var settingsSpan = settingsModal.getElementsByClassName("close")[0];
var messagesSpan = messagesModal.getElementsByClassName("close")[0];
var cloudSpan = cloudModal.getElementsByClassName("close")[0];
var analyticsSpan = analyticsModal.getElementsByClassName("close")[0];
var notificationsSpan = notificationsModal.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
subscriptionsSpan.onclick = function() {
  subscriptionsModal.style.display = "none";
}

eventsSpan.onclick = function() {
  eventsModal.style.display = "none";
}

activeUsersSpan.onclick = function() {
  activeUsersModal.style.display = "none";
}

automationsSpan.onclick = function() {
  automationsModal.style.display = "none";
}

workflowsSpan.onclick = function() {
  workflowsModal.style.display = "none";
}

mentionsSpan.onclick = function() {
  mentionsModal.style.display = "none";
}


sIntelligenceSpan.onclick = function() {
  sIntelligenceModal.style.display = "none";
}

insightsSpan.onclick = function() {
  insightsModal.style.display = "none";
}

teamsSpan.onclick = function() {
  teamsModal.style.display = "none";
}

// When the user clicks on <span> (x), close the respective modal
bankWalletSpan.onclick = function() {
  bankWalletModal.style.display = "none";
}

reportsSpan.onclick = function() {
  reportsModal.style.display = "none";
}

talentSpan.onclick = function() {
  talentModal.style.display = "none";
}

changeLanguageSpan.onclick = function() {
  changeLanguageModal.style.display = "none";
}

createTaskSpan.onclick = function() {
  createTaskModal.style.display = "none";
}

updatesSpan.onclick = function() {
  updatesModal.style.display = "none";
}

activityLogsSpan.onclick = function() {
  activityLogsModal.style.display = "none";
}

alertsSpan.onclick = function() {
  alertsModal.style.display = "none";
}

kpiSpan.onclick = function() {
  kpiModal.style.display = "none";
}

appIntegrationSpan.onclick = function() {
  appIntegrationModal.style.display = "none";
}

employeeSpan.onclick = function() {
  employeeModal.style.display = "none";
}

projectSpan.onclick = function() {
  projectModal.style.display = "none";
}

vaultSpan.onclick = function() {
  vaultModal.style.display = "none";
}

widgetSpan.onclick = function() {
  widgetModal.style.display = "none";
}

settingsSpan.onclick = function() {
  settingsModal.style.display = "none";
}


messagesSpan.onclick = function() {
  messagesModal.style.display = "none";
}

cloudSpan.onclick = function() {
  cloudModal.style.display = "none";
}

analyticsSpan.onclick = function() {
  analyticsModal.style.display = "none";
}

notificationsSpan.onclick = function() {
  notificationsModal.style.display = "none";
}
// When the user clicks anywhere outside of the modals, close them
window.onclick = function(event) {
  if (event.target == bankWalletModal) {
    bankWalletModal.style.display = "none";
  }
  if (event.target == reportsModal) {
    reportsModal.style.display = "none";
  }
  if (event.target == talentModal) {
    talentModal.style.display = "none";
  }
  if (event.target == changeLanguageModal) {
    changeLanguageModal.style.display = "none";
  }
  if (event.target == createTaskModal) {
    createTaskModal.style.display = "none";
  }
  if (event.target == updatesModal) {
    updatesModal.style.display = "none";
  }
  if (event.target == activityLogsModal) {
    activityLogsModal.style.display = "none";
  }
  if (event.target == alertsModal) {
    alertsModal.style.display = "none";
  }
  if (event.target == kpiModal) {
    kpiModal.style.display = "none";
  }
  if (event.target == appIntegrationModal) {
    appIntegrationModal.style.display = "none";
  }
  if (event.target == employeeModal) {
    employeeModal.style.display = "none";
  }
  
  if (event.target == projectModal) {
    projectModal.style.display = "none";
  }
  
    if (event.target == vaultModal) {
    vaultModal.style.display = "none";
  }
   
  if (event.target == widgetModal) {
    widgetModal.style.display = "none";
  }
   
  if (event.target == subscriptionsModal) {
    subscriptionsModal.style.display = "none";
  }
  if (event.target == eventsModal) {
    eventsModal.style.display = "none";
  }
  if (event.target == activeUsersModal) {
    activeUsersModal.style.display = "none";
  }
  
  if (event.target == automationsModal) {
    automationsModal.style.display = "none";
  }
  if (event.target == workflowsModal) {
    workflowsModal.style.display = "none";
  }
  if (event.target == mentionsModal) {
    mentionsModal.style.display = "none";
  }
  
  if (event.target == sIntelligenceModal) {
    sIntelligenceModal.style.display = "none";
  }
  if (event.target == insightsModal) {
    insightsModal.style.display = "none";
  }
  if (event.target == teamsModal) {
    teamsModal.style.display = "none";
  }
  if (event.target == settingsModal) {
      settingsModal.style.display = "none";
  }

  if (event.target == messageModal) {
      messageModal.style.display = "none";
    }
    if (event.target == cloudModal) {
      cloudModal.style.display = "none";
    }
    if (event.target == analyticsModal) {
      analyticsModal.style.display = "none";
    }
    if (event.target == notificationsModal) {
      notificationsModal.style.display = "none";
    }
  
  
}


function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}

function openKpiTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}




function showIntegrationSteps(tool) {
  var integrationSteps = document.getElementById("integrationSteps");
  var integrationTitle = document.getElementById("integrationTitle");
  var integrationContent = document.getElementById("integrationContent");

  integrationTitle.textContent = tool + " Integration Steps";

  switch(tool) {
    case "Stripe":
      integrationContent.innerHTML = `
        <form id="stripeIntegrationForm" class="styled-form">
          <div class="form-group">
            <label for="stripeApiKey">Stripe API Key:</label>
            <input type="password" id="stripeApiKey" name="stripeApiKey" required>
          </div>
          <div class="form-group">
            <label for="stripeWebhookSecret">Webhook Secret:</label>
            <input type="password" id="stripeWebhookSecret" name="stripeWebhookSecret" required>
          </div>
          <button type="submit" class="btn btn-primary">Connect Stripe</button>
        </form>
      `;
      break;
    case "Plaid":
      integrationContent.innerHTML = `
        <form id="plaidIntegrationForm" class="styled-form">
          <div class="form-group">
            <label for="plaidClientId">Plaid Client ID:</label>
            <input type="text" id="plaidClientId" name="plaidClientId" required>
          </div>
          <div class="form-group">
            <label for="plaidSecret">Plaid Secret:</label>
            <input type="password" id="plaidSecret" name="plaidSecret" required>
          </div>
          <div class="form-group">
            <label for="plaidEnv">Environment:</label>
            <select id="plaidEnv" name="plaidEnv" required>
              <option value="sandbox">Sandbox</option>
              <option value="development">Development</option>
              <option value="production">Production</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Connect Plaid</button>
        </form>
      `;
      break;
    case "GitHub":
      integrationContent.innerHTML = `
        <form id="githubIntegrationForm" class="styled-form">
          <div class="form-group">
            <label for="githubToken">GitHub Access Token:</label>
            <input type="password" id="githubToken" name="githubToken" required>
          </div>
          <div class="form-group">
            <label for="githubRepo">Repository Name:</label>
            <input type="text" id="githubRepo" name="githubRepo" required>
          </div>
          <button type="submit" class="btn btn-primary">Connect GitHub</button>
        </form>
      `;
      break;
    case "Zapier":
      integrationContent.innerHTML = `
        <p>To integrate Zapier, follow these steps:</p>
        <ol>
          <li>Log in to your Zapier account</li>
          <li>Create a new Zap</li>
          <li>Choose our app as the trigger</li>
          <li>Follow the prompts to connect your account</li>
          <li>Test and turn on your Zap</li>
        </ol>
      `;
      break;
    case "RiskTools":
      integrationContent.innerHTML = `
        <p>To integrate Risk Management Tools, please contact our support team for a personalized setup process.</p>
        <button onclick="contactSupport()" class="btn btn-primary">Contact Support</button>
      `;
      break;
  }

  integrationSteps.style.display = "block";
}


function contactSupport() {
  // Implement contact support functionality
  alert("Support team will contact you shortly.");
}



  // Function to load alerts dynamically from the server
  function loadAlerts() {
    var alertsList = document.getElementById("alertsList");
    alertsList.innerHTML = ""; // Clear existing alerts

    // Fetch alerts from the server using Fetch API
    fetch('getalerts.php')  // Replace with the path to your PHP file
      .then(response => response.json()) // Parse the JSON response
      .then(alerts => {
        // Loop through the alerts and display them
        alerts.forEach(function(alert) {
          var alertItem = document.createElement("div");
          alertItem.className = "alert-item";
          alertItem.innerHTML = `
            <h3>${alert.title}</h3>
            <p>${alert.message}</p>
            <span class="alert-date">${alert.date}</span>
          `;
          alertsList.appendChild(alertItem);
        });
      })
      .catch(error => {
        console.error('Error fetching alerts:', error);
      });
  }

  // Function to open the alerts modal
  function openAlertsModal() {
    loadAlerts();  // Load alerts when the modal is opened
    document.getElementById("alertsModal").style.display = "block"; // Display the modal (you can customize this)
  }

  // Close the modal (this is optional)
  function closeAlertsModal() {
    document.getElementById("alertsModal").style.display = "none";
  }

  // Add click event to the alerts link
  document.querySelector(".alerts-link").addEventListener("click", function(e) {
    e.preventDefault();
    openAlertsModal();
  });





function filterLogs() {
  // Implement log filtering logic here
  console.log("Filtering logs...");
}

function previousPage() {
  // Implement previous page logic here
  console.log("Going to previous page...");
}

function nextPage() {
  // Implement next page logic here
  console.log("Going to next page...");
}

// Function to populate logs (example)
// Function to populate logs
function populateLogs() {
  var logTableBody = document.getElementById("logTableBody");
  // Clear existing logs
  logTableBody.innerHTML = "";
  
  // Fetch logs from get_alerts.php
  fetch('getlogs.php')
    .then(response => response.json())
    .then(logs => {
      logs.forEach(function(log) {
        var row = logTableBody.insertRow();
        row.insertCell(0).textContent = log.created_at;
        row.insertCell(1).textContent = log.type;
        row.insertCell(2).textContent = log.user;
        row.insertCell(3).textContent = log.action;
        row.insertCell(4).textContent = log.details;
      });
    })
    .catch(error => console.error('Error fetching logs:', error));
}



// // Function to handle talent suggestion form submission
// document.getElementById("suggestTalentForm").onsubmit = function(event) {
//   event.preventDefault();
//   // Here you would typically send this data to your backend
//   // For now, we'll just log it to the console
//   console.log("Talent suggested:", {
//     name: document.getElementById("talentName").value,
//     skills: document.getElementById("talentSkills").value,
//     experience: document.getElementById("talentExperience").value,
//     notes: document.getElementById("talentNotes").value,
//     recruiter: document.getElementById("recruiterName").value
//   });
//   alert("Talent suggestion sent to recruiter!");
//   this.reset();
// };



// Handle form submission
document.getElementById("createTaskForm").onsubmit = function(event) {
  event.preventDefault();
  // Add code here to handle the task creation
  console.log("Task created");
  createTaskModal.style.display = "none";
}



  // Get the button that opens the modal
  var changeLanguageBtn = document.querySelector(".change-language-button");

  // Get the <span> element that closes the modal
  var closeChangeLanguage = changeLanguageModal.querySelector(".close");

  // When the user clicks the button, open the modal 
  changeLanguageBtn.onclick = function() {
    changeLanguageModal.style.display = "block";
    // Set the current language as selected
    var currentLanguage = document.documentElement.lang || 'en';
    updateLanguageList(currentLanguage);
  }

  // Function to update the language list
  function updateLanguageList(currentLanguage) {
    var languageList = document.getElementById("languageList");
    var buttons = languageList.querySelectorAll(".choose-language");
    buttons.forEach(function(button) {
      if (button.dataset.lang === currentLanguage) {
        button.style.display = "none";
      } else {
        button.style.display = "inline-block";
      }
    });
  }

  // Handle language selection
  document.getElementById("languageList").addEventListener("click", function(event) {
    if (event.target.classList.contains("choose-language")) {
      var selectedLanguage = event.target.dataset.lang;
      var previousLanguage = document.documentElement.lang || 'en';
      console.log("Language changed from: " + previousLanguage + " to: " + selectedLanguage);
      document.documentElement.lang = selectedLanguage;
      changeLanguageModal.style.display = "none";
      updateLanguageList(selectedLanguage);
    }
  });
  

// Function to create wallet items (example data)
function createWalletItems() {
  const walletList = document.getElementById("walletList");
  const currencies = ["Naira", "USD", "Euro", "GBP"];
  
  currencies.forEach(currency => {
    const walletItem = document.createElement("div");
    walletItem.className = "wallet-item";
    walletItem.innerHTML = `
      <h4>${currency} Wallet</h4>
      <p class="balance">Balance: ${getRandomBalance(currency)}</p>
      <p>Debits: ${getRandomAmount(currency)}</p>
      <p>Credits: ${getRandomAmount(currency)}</p>
      <p class="percentage">In-Out: ${getRandomPercentage()}%</p>
    `;
    walletList.appendChild(walletItem);
  });
}

function getRandomBalance(currency) {
  const amount = Math.floor(Math.random() * 10000) + 1000;
  return formatCurrency(amount, currency);
}

function getRandomAmount(currency) {
  const amount = Math.floor(Math.random() * 5000) + 100;
  return formatCurrency(amount, currency);
}

function getRandomPercentage() {
  return (Math.random() * 10 + 1).toFixed(2);
}

function formatCurrency(amount, currency) {
  switch(currency) {
    case "Naira":
      return `₦${amount.toLocaleString()}`;
    case "USD":
      return `$${amount.toLocaleString()}`;
    case "Euro":
      return `€${amount.toLocaleString()}`;
    case "GBP":
      return `£${amount.toLocaleString()}`;
    default:
      return `${amount.toLocaleString()} ${currency}`;
  }
}

// Call this function when the modal is opened
createWalletItems();



    



  // Object to hold all CKEditor instances
let editorInstances = {};

// Create CKEditor for each textarea and store instance by ID
document.querySelectorAll('textarea').forEach(textarea => { 
  ClassicEditor
    .create(textarea)
    .then(editor => {
      // Store the editor instance in the object using the textarea's id as the key
      editorInstances[textarea.id] = editor;

      // Apply custom styles for font size and color after editor is created
      editor.editing.view.change(writer => {
        writer.setStyle('font-size', '14px', editor.editing.view.document.getRoot()); // Set font size
        writer.setStyle('color', 'black', editor.editing.view.document.getRoot());    // Set text color
      });
    })
    .catch(error => {
      console.error(error);
    });
});



const loaderOverlay = document.createElement('div');
loaderOverlay.id = 'loaderModal';
loaderOverlay.innerHTML = `
    <div class="modal-content-custom">
        <div id="loader-custom"></div>
        <div id="parseStatus-custom" style="display: none; text-align: center; margin-top: 10px;"></div>
    </div>
`;
document.body.appendChild(loaderOverlay);

// Create and add CSS for the modal and loader
const style = document.createElement('style');
style.textContent = `
    #loaderModal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content-custom {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    #loader-custom {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Show modal function
function showModal() {
    loaderOverlay.style.display = 'block';
}

function xologenerate() {
    showModal(); // Show the loading modal

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "generatetask.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            var response = JSON.parse(this.responseText);
            console.log(response);
            if (response.task_title && response.task_description) {
                document.getElementById("taskTitle").value = response.task_title;

                // Use the CKEditor instance for taskDescription to set the task description
                if (editorInstances['taskDescription']) {
                    editorInstances['taskDescription'].setData(response.task_description);
                } else {
                    console.error("Editor instance not found for taskDescription");
                }
            } else {
                alert("Failed to generate task. Please try again.");
            }
            loaderOverlay.style.display = 'none'; // Hide the loading modal when done
        }
    };
    var projectId = document.getElementById("project_id").value; // Assuming you have a hidden input with project ID
    xhr.send("project_id=" + encodeURIComponent(projectId));
}


