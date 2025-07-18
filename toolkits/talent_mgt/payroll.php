<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accounting & Inventory Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Global Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    :root {
      --primary: #2563eb;
      --secondary: #64748b;
      --success: #22c55e;
      --danger: #ef4444;
      --warning: #f59e0b;
    }

    /* Top Navbar */
    .topbar {
      background-color: #fff;
      padding: 1rem 2rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 100;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 1rem;
      color: var(--primary);
      font-size: 1.5rem;
      font-weight: 600;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .avatar {
      width: 40px;
      height: 40px;
      background: var(--primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }

    /* Main Content */
    .main-content {
      margin-top: 80px;
      padding: 2rem;
      background-color: #f8fafc;
      min-height: calc(100vh - 80px);
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .metric-card {
      background-color: #fff;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .metric-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      color: var(--secondary);
    }

    .metric-value {
      font-size: 2rem;
      font-weight: 600;
      color: #1f2937;
    }

    .chart-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 1.5rem;
    }

    .chart-container {
      background-color: #fff;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
      .topbar {
        padding: 1rem;
      }

      .main-content {
        margin-top: 60px;
        padding: 1rem;
      }

      .dashboard-grid,
      .chart-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="logo">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
        <line x1="12" y1="7" x2="12" y2="12" />
        <line x1="12" y1="12" x2="16" y2="16" />
      </svg>
      Accounting & Inventory
    </div>
    <div class="user-info">
      <div class="avatar">AD</div>
    </div>
  </div>

  <div class="main-content">
    <div class="dashboard-grid">
      <div class="metric-card">
        <div class="metric-header">
          <span>Total Revenue</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
            <polyline points="17 6 23 6 23 12" />
          </svg>
        </div>
        <div class="metric-value">$456,789</div>
      </div>
      <div class="metric-card">
        <div class="metric-header">
          <span>Total Expenses</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </div>
        <div class="metric-value">$321,456</div>
      </div>
      <div class="metric-card">
        <div class="metric-header">
          <span>Net Profit</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5z" />
            <path d="M2 17l10 5 10-5M2 12l10 5 10-5" />
          </svg>
        </div>
        <div class="metric-value">$135,333</div>
      </div>
      <div class="metric-card">
        <div class="metric-header">
          <span>Inventory Value</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
            <line x1="7" y1="7" x2="7.01" y2="7" />
          </svg>
        </div>
        <div class="metric-value">$78,214</div>
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-container">
        <h3>Revenue vs Expenses</h3>
        <canvas id="revenueExpensesChart"></canvas>
      </div>
      <div class="chart-container">
        <h3>Inventory Levels</h3>
        <canvas id="inventoryChart"></canvas>
      </div>
      <div class="chart-container">
        <h3>Ledger Overview</h3>
        <canvas id="ledgerChart"></canvas>
      </div>
      <div class="chart-container">
        <h3>Payroll Summary</h3>
        <canvas id="payrollChart"></canvas>
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
            tension: 0.3
          },
          {
            label: 'Expenses',
            data: [35000, 38000, 41000, 44000, 47000, 50000],
            borderColor: '#ef4444',
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
            beginAtZero: true
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