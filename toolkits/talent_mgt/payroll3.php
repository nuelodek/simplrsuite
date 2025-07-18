<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Advanced Accounting Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
  <style>
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
          <span class="ml-2 text-xl font-semibold text-gray-800">Advanced Accounting</span>
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
            <div class="h-96">
              <LineChart width={500} height={400} data={[
                { month: 'Jan', revenue: 45000, expenses: 35000 },
                { month: 'Feb', revenue: 50000, expenses: 38000 },
                { month: 'Mar', revenue: 55000, expenses: 41000 },
                { month: 'Apr', revenue: 60000, expenses: 44000 },
                { month: 'May', revenue: 65000, expenses: 47000 },
                { month: 'Jun', revenue: 70000, expenses: 50000 }
              ]}>
                <XAxis dataKey="month" />
                <YAxis type="number" domain={[0, 'dataMax']} tickFormatter={(value) => `$${value.toLocaleString()}`} />
                <Tooltip />
                <Legend />
                <Line type="monotone" dataKey="revenue" stroke="#2563eb" />
                <Line type="monotone" dataKey="expenses" stroke="#ef4444" />
              </LineChart>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-800 font-semibold mb-4">Inventory Levels</h3>
            <div class="h-96">
              <BarChart width={500} height={400} data={[
                { product: 'Product A', level: 1200 },
                { product: 'Product B', level: 850 },
                { product: 'Product C', level: 1100 },
                { product: 'Product D', level: 950 },
                { product: 'Product E', level: 1300 }
              ]}>
                <XAxis dataKey="product" />
                <YAxis type="number" domain={[0, 'dataMax']} tickFormatter={(value) => value.toLocaleString()} />
                <Tooltip />
                <Legend />
                <Bar dataKey="level" fill="#2563eb" />
              </BarChart>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-800 font-semibold mb-4">Ledger Overview</h3>
            <div class="h-96">
              <PieChart width={500} height={400}>
                <Pie data={[
                  { name: 'Assets', value: 65000 },
                  { name: 'Liabilities', value: 35000 },
                  { name: 'Equity', value: 45000 }
                ]} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={100}>
                  <Tooltip />
                  <Legend />
                </Pie>
              </PieChart>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-800 font-semibold mb-4">Payroll Summary</h3>
            <div class="h-96">
              <PieChart width={500} height={400}>
                <Pie data={[
                  { name: 'Salaries', value: 75000 },
                  { name: 'Bonuses', value: 12000 },
                  { name: 'Taxes', value: 18000 },
                  { name: 'Benefits', value: 10000 }
                ]} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={100}>
                  <Tooltip />
                  <Legend />
                </Pie>
              </PieChart>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>