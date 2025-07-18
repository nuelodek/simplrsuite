<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accounting & Inventory Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
  <style>
    /* Additional Tailwind Utility Classes */
    .bg-gradient-primary {
      background-image: linear-gradient(to right, #2563eb, #3b82f6);
    }

    .text-gradient-primary {
      background-image: linear-gradient(to right, #2563eb, #3b82f6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  </style>
</head>
<body class="bg-gray-100">
  <div class="flex flex-col h-screen">
    <!-- Top Navbar -->
    <div class="bg-white shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
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
            <div class="absolute -top-2 -right-2 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">
              3
            </div>
          </div>
          <div class="bg-blue-600 rounded-full h-10 w-10 flex items-center justify-center text-white font-medium">
            AD
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Navigation -->
        <div class="mb-8">
          <ul class="flex space-x-4 text-gray-500 font-medium">
            <li class="cursor-pointer hover:text-gray-800 transition-colors">Dashboard</li>
            <li class="cursor-pointer hover:text-gray-800 transition-colors">Accounting</li>
            <li class="cursor-pointer hover:text-gray-800 transition-colors">Inventory</li>
            <li class="cursor-pointer hover:text-gray-800 transition-colors">Payroll</li>
          </ul>
        </div>

        <!-- Dashboard Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Total Revenue</span>
              <svg class="h-6 w-6 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                <polyline points="17 6 23 6 23 12" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$456,789</div>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Total Expenses</span>
              <svg class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$321,456</div>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Net Profit</span>
              <svg class="h-6 w-6 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z" />
                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$135,333</div>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
              <span class="text-gray-500 font-medium">Inventory Value</span>
              <svg class="h-6 w-6 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                <line x1="7" y1="7" x2="7.01" y2="7" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-gradient-primary mt-2">$78,214</div>
          </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-800 font-semibold mb-4">Revenue vs Expenses</h3>
            <canvas id="revenueExpensesChart"></canvas>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-800 font-semibold mb-4">Inventory Levels</h3>
            <canvas id="inventoryChart"></canvas>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-800 font-semibold mb-4">Ledger Overview</h3>
            <canvas id="ledgerChart"></canvas>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-800 font-semibold mb-4">Payroll Summary</h3>
            <canvas id="payrollChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Revenue vs Expenses Chart
    const revenueExpensesChart = new Chart(document.getElementById('revenueExpensesChart'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
          {
            label: 'Revenue',
            data: [45000, 50000, 55000, 60000, 65000, 70000],
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.2)',
            tension: 0.3
          },
          {
            label: 'Expenses',
            data: [35000, 38000, 41000, 44000, 47000, 50000],
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.2)',
            tension: 0.3
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '$' + value.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Inventory Levels Chart
    const inventoryChart = new Chart(document.getElementById('inventoryChart'), {
      type: 'bar',
      data: {
        labels: ['Product A', 'Product B', 'Product C', 'Product D', 'Product E'],
        datasets: [
          {
            label: 'Inventory Level',
            data: [1200, 850, 1100, 950, 1300],
            backgroundColor: '#2563eb'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Ledger Overview Chart
    const ledgerChart = new Chart(document.getElementById('ledgerChart'), {
      type: 'doughnut',
      data: {
        labels: ['Assets', 'Liabilities', 'Equity'],
        datasets: [
          {
            data: [65000, 35000, 45000],
            backgroundColor: [
              '#22c55e',
              '#ef4444',
              '#f59e0b'
            ]
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right'
          }
        }
      }
    });

    // Payroll Summary Chart
    const payrollChart = new Chart(document.getElementById('payrollChart'), {
      type: 'pie',
      data: {
        labels: ['Salaries', 'Bonuses', 'Taxes', 'Benefits'],
        datasets: [
          {
            data: [75000, 12000, 18000, 10000],
            backgroundColor: [
              '#2563eb',
              '#22c55e',
              '#f59e0b',
              '#64748b'
            ]
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right'
          }
        }
      }
    });
  </script>
</body>
</html>