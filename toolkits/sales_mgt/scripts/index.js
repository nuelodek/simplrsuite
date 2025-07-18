document.addEventListener("DOMContentLoaded", () => {
  console.log("Dashboard loaded");
  /// Load selected components from localStorage
  loadSelectedComponents();
  // Event listener for checkboxes to handle selection and deselection
  document.querySelectorAll('input[name="components"]').forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      updateLocalStorage(this);
    });
  });

  // fetch customer metrics from the server
  fetch('../sales_mgt/customer_metrics.php')
        .then(response => response.json())
        .then(data => {
            // Assume `data` now contains the metrics: { totalCustomers: X, newCustomers: Y }

            // Update your chart with the data
            updateCustomerOverviewChart(data.totalCustomers, data.newCustomers);
        })
        .catch(error => console.error("Error fetching customer metrics:", error));
});

// Function to add or remove component from localStorage based on checkbox status
function updateLocalStorage(checkbox) {
  const componentName = checkbox.value;
  let selectedComponents =
    JSON.parse(localStorage.getItem("selectedComponents")) || [];

  if (checkbox.checked) {
    // Add component if it’s checked and not already in the list
    if (!selectedComponents.includes(componentName)) {
      selectedComponents.push(componentName);
    }
  } else {
    // Remove component if it’s unchecked
    selectedComponents = selectedComponents.filter(
      (component) => component !== componentName
    );
    window.location.reload();
  }

  // Save updated list to localStorage
  localStorage.setItem(
    "selectedComponents",
    JSON.stringify(selectedComponents)
  );
  showComponentBox(componentName, checkbox.checked);
}

// Load components from localStorage and display them as checked
function loadSelectedComponents() {
  const selectedComponents =
    JSON.parse(localStorage.getItem("selectedComponents")) || [];

  selectedComponents.forEach((componentName) => {
    const checkbox = document.querySelector(`input[value="${componentName}"]`);
    if (checkbox) {
      checkbox.checked = true; // Mark as checked
      showComponentBox(componentName, true); // Display component
    }
  });
}

// Toggle dropdown menu visibility
function toggleDropdown() {
    const dropdownMenu = document.getElementById("dropdownMenu");
  document.getElementById("dropdownMenu").classList.toggle("show");

  // Add event listener to close dropdown if clicked outside
  if (dropdownMenu.classList.contains("show")) {
    document.addEventListener("click", closeAddDropdownOnClickOutside);
  } else {
    document.removeEventListener("click", closeAddDropdownOnClickOutside);
  }
}

// Close dropdown if clicked outside
function closeAddDropdownOnClickOutside(event) {
  const dropdownMenu = document.getElementById("dropdownMenu");

  // Check if the click target is outside the dropdown menu
  if (
    !dropdownMenu.contains(event.target) &&
    !event.target.matches(".dropdown-btn")
  ) {
    dropdownMenu.classList.remove("show");
    document.removeEventListener("click", closeAddDropdownOnClickOutside); // Remove listener after closing
  }
}

// Add a new component to the grid
function addComponent() {
  const modal = document.getElementById("addModal");
  const dropdownMenu = document.getElementById("dropdownMenu");

  // Open modal when "Add" button is clicked
  modal.style.display = "block";
  dropdownMenu.classList.remove("show");

  // Close modal

  window.onclick = (event) => {
    if (event.target == modal) modal.style.display = "none";
  };
}

document
  .getElementById("add-components-btn")
  .addEventListener("click", function () {
    const selectedComponents = Array.from(
      document.querySelectorAll('input[name="components"]:checked')
    ).map((checkbox) => checkbox.value);

    // store selected components in sessionStorage
    localStorage.setItem(
      "selectedComponents",
      JSON.stringify(selectedComponents)
    );

    selectedComponents.forEach((component) => {
      showComponentBox(component);
    });

    closeModal(); // Optional: Close the modal after adding components
  });

function showComponentBox(component) {
  const modal = document.getElementById("addModal");
  let boxId;

  // Map component names to corresponding box IDs
  switch (component) {
    case "Sales Pipeline":
      boxId = "salesPipelineBox";
      break;
    case "Upcoming Meetings":
      boxId = "upcomingMeetingsBox";
      break;
    case "Customer Overview":
      boxId = "customerOverviewBox";
      break;
    case "Revenue Trend":
      boxId = "revenueTrendBox";
      break;
    case "Lead Sources":
      boxId = "leadSourcesBox";
      break;
    case "Deals by Stage":
      boxId = "dealsByStageBox";
      break;
  }

  // Set display to block if the component is selected
  if (boxId) {
    const boxElement = document.getElementById(boxId);
    boxElement.style.display = "block";
  }

  // close modal after adding component
  modal.style.display = "none";
}

// Reload the page
function reloadPage() {
  window.location.reload();
}


function toggleAdd() {
  const dropdownMenu = document.getElementById("addDropdown");
  const isHidden = window.getComputedStyle(dropdownMenu).display === "none";
  dropdownMenu.style.display = isHidden ? "block" : "none";
  // Event listeners to close dropdown on click outside
  document.addEventListener("click", closeDropdownOnClickOutside);
}

// Function to close dropdown if user clicks outside of it
function closeDropdownOnClickOutside(event) {
  const addButton = document.getElementById("addBtn");
  const dropdownMenu = document.getElementById("addDropdown");

  // Check if click was outside the dropdown and button
  if (
    !addButton.contains(event.target) &&
    !dropdownMenu.contains(event.target)
  ) {
    dropdownMenu.style.display = "none";
  }
}

// Function to open a specific modal
// Open the modal based on the selected option
document.querySelectorAll(".dropdown-item").forEach((item) => {
  item.addEventListener("click", (event) => {
    event.preventDefault();
    const modalId = item.getAttribute("data-modal");
    openModal(modalId);
  });
});

// Function to open a modal
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  const dropdownMenu = document.getElementById("addDropdown");
  if (modal) {
    modal.style.display = "block";
    dropdownMenu.style.display = "none";
  }
}

// Function to close a modal
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
  }
}

// Close modal when clicking outside of modal content
window.onclick = function (event) {
  const modals = document.querySelectorAll(".modal");
  modals.forEach((modal) => {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });
};

// Handle location change
function handleLocationChange() {
  const location = document.getElementById("meetingLocation").value;
  const onlineOptions = document.getElementById("onlineOptions");
  const inPersonAddress = document.getElementById("inPersonAddress");

  if (location === "online") {
    onlineOptions.style.display = "block";
    inPersonAddress.style.display = "none";
  } else if (location === "in-person") {
    onlineOptions.style.display = "none";
    inPersonAddress.style.display = "block";
  } else {
    onlineOptions.style.display = "none";
    inPersonAddress.style.display = "none";
  }
}

const salesPipelineCtx = document
  .getElementById("salesPipelineChart")
  .getContext("2d");
const salesPipelineChart = new Chart(salesPipelineCtx, {
  type: "bar",
  data: {
    labels: ["Leads", "Prospects", "Qualified Leads", "Closed Deals"],
    datasets: [
      {
        label: "Sales Pipeline",
        data: [500, 350, 200, 120], // Example data
        backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"],
        borderWidth: 1,
      },
    ],
  },
  options: {
    indexAxis: "y",
    responsive: true,
    plugins: {
      legend: { display: false },
    },
  },
});

function updateCustomerOverviewChart(total, newCustomers) {
    const customerOverviewCtx = document.getElementById("customerOverviewChart").getContext("2d");
    const customerOverviewChart = new Chart(customerOverviewCtx, {
        type: "bar",
        data: {
            labels: ["Total Customers", "New Customers"],
            datasets: [
                {
                    label: "Customer Metrics",
                    data: [total, newCustomers],
                    backgroundColor: ["#4CAF50", "#FF9800"],
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    },
                },
            },
        },
    });
}

const revenueTrendCtx = document
  .getElementById("revenueTrendChart")
  .getContext("2d");
const revenueTrendChart = new Chart(revenueTrendCtx, {
  type: "line",
  data: {
    labels: ["January", "February", "March", "April"], // Example months
    datasets: [
      {
        label: "Revenue",
        data: [3000, 5000, 4000, 7000], // Example revenue data
        borderColor: "#42A5F5",
        backgroundColor: "rgba(66, 165, 245, 0.2)",
        fill: true,
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: true },
    },
    scales: {
      y: { beginAtZero: true },
    },
  },
});

const leadSourcesCtx = document
  .getElementById("leadSourcesChart")
  .getContext("2d");
const leadSourcesChart = new Chart(leadSourcesCtx, {
  type: "pie",
  data: {
    labels: ["Social Media", "Email", "Referrals"],
    datasets: [
      {
        label: "Lead Sources",
        data: [40, 30, 30], // Example lead sources data
        backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56"],
      },
    ],
  },
  options: {
    responsive: true,
  },
});

const dealsByStageCtx = document
  .getElementById("dealsByStageChart")
  .getContext("2d");
const dealsByStageChart = new Chart(dealsByStageCtx, {
  type: "bar",
  data: {
    labels: ["Stage 1", "Stage 2", "Stage 3"],
    datasets: [
      {
        label: "Won",
        data: [15, 10, 5],
        backgroundColor: "#4BC0C0",
      },
      {
        label: "In Progress",
        data: [20, 25, 10],
        backgroundColor: "#FFCE56",
      },
      {
        label: "Lost",
        data: [5, 10, 15],
        backgroundColor: "#FF6384",
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: "top" },
    },
    scales: {
      x: { stacked: true },
      y: { stacked: true },
    },
  },
});

function handleSourceChange() {
  const selectedSource = document.getElementById("leadSource").value;
  const additionalFieldsContainer = document.getElementById("additionalFieldsContainer");

  additionalFieldsContainer.innerHTML = ""; // Clear previous content

  if (selectedSource === "Website") {
      additionalFieldsContainer.innerHTML = `
          <label for="websiteURL">Website URL:</label>
          <input type="url" id="websiteURL" name="websiteURL">
      `;
  } else if (selectedSource === "Social-Media") {
      additionalFieldsContainer.innerHTML = `
          <label for="socialMediaPlatform">Platform:</label>
          <input type="text" id="socialMediaPlatform" name="socialMediaPlatform">
      `;
  } else if (selectedSource === "Email") {
      additionalFieldsContainer.innerHTML = `
          <label for="emailCampaign">Email Campaign:</label>
          <input type="text" id="emailCampaign" name="emailCampaign">
      `;
  }
}

function openSearchBar(){
  const searchBar = document.getElementById("searchBar");
  if(searchBar.classList.contains("active")){
    searchBar.classList.remove("active");
  }else{
    searchBar.classList.add("active");
  }
}


function openCalender(){
  let currentDate = new Date();
  const calender = document.getElementById("calender");
  if(calender.classList.contains("active")){
    calender.classList.remove("active");
  }else{
    calender.classList.add("active");
    // Initialize the calendar
    populateCalendar(currentDate);
  }
}




// Populate the calendar
function populateCalendar(date) {
    const currentDate = new Date();
    const daysContainer = document.getElementById('days');
    const monthYear = document.getElementById('monthYear');

    // Get month and year
    const year = date.getFullYear();
    const month = date.getMonth();

    // Update the header
    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    monthYear.textContent = `${monthNames[month]} ${year}`;

    // Clear previous days
    daysContainer.innerHTML = '';

    // Calculate the first day and total days in the month
    const firstDay = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();

    // Add empty slots for days of the previous month
    for (let i = 0; i < firstDay; i++) {
        const emptySlot = document.createElement('span');
        emptySlot.classList.add('day', 'empty');
        daysContainer.appendChild(emptySlot);
    }

    // Add days of the current month
    for (let day = 1; day <= totalDays; day++) {
        const dayElement = document.createElement('span');
        dayElement.classList.add('day');
        dayElement.textContent = day;

        // Highlight today's date
        if (
            day === currentDate.getDate() &&
            month === currentDate.getMonth() &&
            year === currentDate.getFullYear()
        ) {
            dayElement.classList.add('today');
        }

        daysContainer.appendChild(dayElement);
    }
}

// Navigate between months
function navigateMonth(direction) {
    const currentDate = new Date();
    currentDate.setMonth(currentDate.getMonth() + direction);
    populateCalendar(currentDate);
}




