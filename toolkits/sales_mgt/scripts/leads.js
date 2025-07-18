function updateStatus(leadId, newStatus) {
    // Create a new FormData object
    const formData = new FormData();
    formData.append('lead_id', leadId);
    formData.append('new_status', newStatus);

    // Send the request using fetch
    fetch('updateLeadStatus.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Status updated successfully');
            alert(data.message)
        } else {
            console.error('Error updating status:', data.message);
            alert('Failed to update status');
        }
    })
    .catch(error => {
        console.error('Request error:', error);
        alert('An error occurred while updating status');
    });
}

// Access and parse status data
const statusDataElement = document.getElementById('statusDistributionChart');
const statusData = JSON.parse(statusDataElement.getAttribute('data-status'));

const ctxStatus = statusDataElement.getContext('2d');
const statusLabels = statusData.map(data => data.status);
const statusCounts = statusData.map(data => data.count);

new Chart(ctxStatus, {
    type: 'pie',
    data: {
        labels: statusLabels,
        datasets: [{
            label: 'Lead Status Distribution',
            data: statusCounts,
            backgroundColor: ['#4CAF50', '#FF9800', '#F44336', '#2196F3']
        }]
    },
    options: {
        responsive: true
    }
});

// Access and parse source data
const sourceDataElement = document.getElementById('sourceDistributionChart');
const sourceData = JSON.parse(sourceDataElement.getAttribute('data-source'));

// Prepare data for chart
const sourceLabels = sourceData.map(data => data.lead_source);
const sourceCounts = sourceData.map(data => data.count);

const ctxSource = sourceDataElement.getContext('2d');
new Chart(ctxSource, {
    type: 'bar',
    data: {
        labels: sourceLabels,
        datasets: [{
            label: 'Lead Source Analysis',
            data: sourceCounts,
            backgroundColor: ['#FF9800', '#2196F3', '#4CAF50', '#F44336']
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
