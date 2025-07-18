document.addEventListener('DOMContentLoaded', function() {
    // Fetch initial data (this would normally come from your backend)
    fetchDashboardData();

    // function fetchDashboardData() {
    //     // Simulated data fetching
    //     const data = {
    //         totalRevenue: 50000,
    //         totalExpenses: 20000,
    //         netProfit: 30000
    //     };

    //     updateDashboard(data);
    // }

    function updateDashboard(data) {
        document.getElementById('total-revenue').textContent = `$${data.totalRevenue.toFixed(2)}`;
        document.getElementById('total-expenses').textContent = `$${data.totalExpenses.toFixed(2)}`;
        document.getElementById('net-profit').textContent = `$${data.netProfit.toFixed(2)}`;
    }
});
