function toggleModal() {
    const modal = document.getElementById("add-task-modal");
    modal.style.display = modal.style.display === "none" ? "flex" : "none";
}

// Handle form submission with Fetch API
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent the default form submission

    // Collect form data
    const formData = new FormData(this);

    // Send the form data via AJAX
    fetch('../../toolkits/project_mgt/add_task.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Parse the JSON response
    .then(data => {
        // Check the response status
        if (data.status === 'success') {
            alert(data.message); // Show success message
            // Optionally, close the modal here or refresh task list
            // Example to close modal: document.getElementById('addTaskModal').style.display = 'none';
        } else {
            alert('Error: ' + data.message); // Show error message if there's an issue
        }
    })
    .catch(error => {
        console.error('Error:', error); // Log any network errors
        alert('A network error occurred. Please try again later.');
    });
});

function updateAssignee(taskId, newAssigneeId) {
    fetch('../../toolkits/project_mgt/update_assignee.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ taskId, newAssigneeId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message); // Optionally, show a success message
        } else {
            alert('Error: ' + data.message); // Display error message if update fails
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateStatus(taskId, newStatus) {
    fetch('../../toolkits/project_mgt/update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ taskId, newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message); // Optionally, show a success message
        } else {
            alert('Error: ' + data.message); // Display error message if update fails
        }
    })
    .catch(error => console.error('Error:', error));
}


