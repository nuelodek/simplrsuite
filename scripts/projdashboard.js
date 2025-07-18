// Function to display the selected panel and set active class
function displayPanel(panelId, element) {
    // Hide all content panels
    document.querySelectorAll('.content-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    // Show the selected content panel
    document.getElementById(panelId).classList.add('active');

    // Remove active class from all menu items
    document.querySelectorAll('.menu li').forEach(item => {
        item.classList.remove('active');
    });
    // Add active class to the selected menu item
    element.classList.add('active');
}

// Logout function
function logout() {
    window.location.href = 'logout.php';
}

function toggleModal() {
    const modal = document.getElementById("add-task-modal");
    modal.style.display = modal.style.display === "none" ? "flex" : "none";
}

function toggleProjectModal() {
    const modal = document.getElementById("add-project-modal");
    modal.style.display = modal.style.display === "none" ? "flex" : "none";
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById("add-project-modal");
    if (event.target === modal) {
        toggleProjectModal();
    }
};

document.getElementById("add-project-form").onsubmit = function(event) {
    event.preventDefault();
    
    const formData = new FormData(this);
    fetch("../../toolkits/project_mgt/createproject.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            toggleProjectModal();
            location.reload(); // Refreshes the page to show the new project in the list
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
};
