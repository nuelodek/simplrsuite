<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accounting & Inventory Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
  <style>
    /* Custom Gradients */
    .bg-gradient-primary {
      background-image: linear-gradient(to right, #1d4ed8, #3b82f6);
    }
    .text-gradient-primary {
      background-image: linear-gradient(to right, #1d4ed8, #3b82f6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .hover-scale {
      transition: transform 0.3s ease;
    }
    .hover-scale:hover {
      transform: scale(1.05);
    }
  </style>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-700">
  <div class="flex flex-col min-h-screen">
    <!-- Navbar -->
    <header class="bg-white shadow-md">
      <div class="container mx-auto flex items-center justify-between px-6 py-4">
        <div class="flex items-center">
          <svg class="h-8 w-8 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
            <line x1="12" y1="7" x2="12" y2="12" />
            <line x1="12" y1="12" x2="16" y2="16" />
          </svg>
          <span class="ml-2 text-xl font-semibold text-gray-800">Accounting & Inventory</span>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <svg class="h-6 w-6 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <div class="absolute -top-2 -right-2 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">3</div>
          </div>
          <div class="bg-blue-600 rounded-full h-10 w-10 flex items-center justify-center text-white font-medium">
            AD
          </div>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="flex-1">
      <div class="container mx-auto px-6 py-8">
        <!-- Navigation Links -->
        <nav class="mb-8">
          <ul class="flex space-x-6 text-gray-500 font-medium">
            <li class="hover:text-gray-800 cursor-pointer">Dashboard</li>
            <li class="hover:text-gray-800 cursor-pointer">Accounting</li>
            <li class="hover:text-gray-800 cursor-pointer">Inventory</li>
            <li class="hover:text-gray-800 cursor-pointer">Payroll</li>
          </ul>
        </nav>

        <!-- Metrics -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Total Revenue</span>
              <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                <polyline points="17 6 23 6 23 12" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$456,789</div>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Total Expenses</span>
              <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$321,456</div>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Net Profit</span>
              <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z" />
                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$135,333</div>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Inventory Value</span>
              <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                <line x1="7" y1="7" x2="7.01" y2="7" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$78,214</div>
          </div>
        </section>

        <!-- Charts -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <h3 class="text-gray-800 font-semibold mb-4">Revenue vs Expenses</h3>
            <canvas id="revenueExpensesChart"></canvas>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <h3 class="text-gray-800 font-semibold mb-4">Inventory Levels</h3>
            <canvas id="inventoryChart"></canvas>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <h3 class="text-gray-800 font-semibold mb-4">Ledger Overview</h3>
            <canvas id="ledgerChart"></canvas>
          </div>
          <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
            <h3 class="text-gray-800 font-semibold mb-4">Payroll Summary</h3>
            <canvas id="payrollChart"></canvas>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Chart.js Script -->
  <script>
    const chartOptions = {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: { y: { beginAtZero: true, ticks: { callback: value => '$' + value.toLocaleString() } } }
    };

    new Chart(document.getElementById('revenueExpensesChart'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
          { label: 'Revenue', data: [45000, 50000, 55000, 60000, 65000, 70000], borderColor: '#2563eb', backgroundColor: 'rgba(37, 99, 235, 0.2)', tension: 0.3 },
          { label: 'Expenses', data: [30000, 35000, 40000, 45000, 50000, 55000], borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.2)', tension: 0.3 }
        ]
      },
      options: chartOptions
    });

    new Chart(document.getElementById('inventoryChart'), {
      type: 'bar',
      data: {
        labels: ['Product A', 'Product B', 'Product C', 'Product D'],
        datasets: [{ label: 'Inventory', data: [80, 50, 60, 90], backgroundColor: '#fde047' }]
      },
      options: chartOptions
    });

    new Chart(document.getElementById('ledgerChart'), {
      type: 'doughnut',
      data: {
        labels: ['Accounts Receivable', 'Accounts Payable', 'Other'],
        datasets: [{ data: [30000, 20000, 5000], backgroundColor: ['#60a5fa', '#f87171', '#fbbf24'] }]
      },
      options: chartOptions
    });

    new Chart(document.getElementById('payrollChart'), {
      type: 'pie',
      data: {
        labels: ['Salaries', 'Bonuses', 'Other Benefits'],
        datasets: [{ data: [20000, 5000, 3000], backgroundColor: ['#4ade80', '#a78bfa', '#facc15'] }]
      },
      options: chartOptions
    });
  </script>
</body>
</html>
