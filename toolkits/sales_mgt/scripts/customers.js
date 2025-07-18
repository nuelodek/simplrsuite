console.log("Customers loaded");

document.addEventListener('DOMContentLoaded', () => {
    // Function to fetch customer data
    async function fetchCustomerData() {
        try {
            const response = await fetch('fetch_customers.php');
            if (!response.ok) {
                throw new Error('Failed to fetch customer data');
            }
            const data = await response.json();
            createTotalCustomersChart(data);
        } catch (error) {
            console.error('Error fetching customer data:', error);
        }
    }
    // Call the fetch function
    fetchCustomerData();
});

function toggleAddCustomerModal() {
    const addCustomerModal = document.getElementById("addCustomerModal");
    addCustomerModal.style.display = addCustomerModal.style.display === "block" ? "none" : "block";
}

// Flip card function
function flipCard(cardInner) {
    console.log("Flip button clicked:", cardInner);
    cardInner.classList.toggle('is-flipped');

    // Update button icons
    const flipButton = cardInner.querySelector('.flip-button');
    const isFlipped = cardInner.classList.contains('is-flipped');

    if (isFlipped) {
        flipButton.innerHTML = `<span class="icon-graph">📈</span>`;
    } else {
        flipButton.innerHTML = `<span class="icon-metric">📊</span>`;
    }
}

// Function to create Total Customers Over Time chart
function createTotalCustomersChart(data) {
    const dates = data.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);
    const counts = data.map(item => item.count);

    const ctx = document.getElementById('totalCustomersChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar', // Changed to bar chart
        data: {
            labels: dates,
            datasets: [{
                label: 'Total Customers Over Time',
                data: counts,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                borderRadius: 3
            }]
        },
        options: {
            responsive: true,
            // maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Customers'
                    }
                }
            }
        }
    });
}