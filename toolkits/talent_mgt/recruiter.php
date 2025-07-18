<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }

        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --success: #22c55e;
            --warning: #f59e0b;
        }

        body {
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 2rem 1rem;
            box-shadow: 2px 0 4px rgba(0,0,0,0.05);
            overflow-y: auto;
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 2rem;
            padding: 0 1rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-item {
            margin-bottom: 0.5rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--secondary);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }

        .sidebar-link:hover {
            background: #eff6ff;
            color: var(--primary);
        }

        .sidebar-submenu {
            list-style: none;
            padding-left: 1rem;
            margin-top: 0.5rem;
        }

        .topnav {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: fixed;
            width: calc(100% - 250px);
            top: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-item {
            color: var(--secondary);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }

        .nav-item:hover {
            background: #eff6ff;
            color: var(--primary);
        }

        .main-content {
            margin-left: 250px;
            padding: 6rem 2rem 2rem;
            max-width: 1400px;
            width: 100%;
        }

        /* Rest of the existing styles remain the same */
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">TalentHub</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="#dashboard" class="sidebar-link">Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="#jobs" class="sidebar-link">Job Postings</a>
            </li>
            <li class="sidebar-item">
                <a href="#candidates" class="sidebar-link">Candidates</a>
                <ul class="sidebar-submenu">
                    <li><a href="#talent-pool" class="sidebar-link">Talent Pool</a></li>
                    <li><a href="#tracking" class="sidebar-link">Applicant Tracking</a></li>
                    <li><a href="#screening" class="sidebar-link">Resume Screening</a></li>
                    <li><a href="#scheduling" class="sidebar-link">Interview Scheduling</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#reports" class="sidebar-link">Reports</a>
                <ul class="sidebar-submenu">
                    <li><a href="#daily" class="sidebar-link">Daily Report</a></li>
                    <li><a href="#monthly" class="sidebar-link">Monthly Report</a></li>
                    <li><a href="#quarterly" class="sidebar-link">Quarterly Report</a></li>
                    <li><a href="#yearly" class="sidebar-link">Yearly Report</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#communication" class="sidebar-link">Communication</a>
                <ul class="sidebar-submenu">
                    <li><a href="#messages" class="sidebar-link">Messages</a></li>
                    <li><a href="#email" class="sidebar-link">Email</a></li>
                    <li><a href="#notifications" class="sidebar-link">Notifications</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#settings" class="sidebar-link">Settings</a>
            </li>
        </ul>
    </aside>

    <nav class="topnav">
        <div class="nav-container">
            <input type="text" class="search-bar" placeholder="Search candidates, jobs, or applications...">
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Dashboard Overview</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">48</div>
                <div class="stat-label">Active Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">256</div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">18</div>
                <div class="stat-label">Interviews This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">92%</div>
                <div class="stat-label">Response Rate</div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Hiring Pipeline Overview</h3>
                <div class="chart-container">
                    <canvas id="pipelineChart"></canvas>
                </div>
            </div>
            <div class="card">
                <h3>Application Trends</h3>
                <div class="chart-container">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Recent Applications</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>John Doe</td>
                        <td>Senior Developer</td>
                        <td>Engineering</td>
                        <td><span class="status status-scheduled">Interviewing</span></td>
                        <td>Nov 5, 2024</td>
                        <td><button class="nav-item">View Profile</button></td>
                    </tr>
                    <tr>
                        <td>Jane Smith</td>
                        <td>UX Designer</td>
                        <td>Design</td>
                        <td><span class="status status-pending">Screening</span></td>
                        <td>Nov 4, 2024</td>
                        <td><button class="nav-item">View Profile</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Pipeline Overview Chart
        const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
        new Chart(pipelineCtx, {
            type: 'bar',
            data: {
                labels: ['Applied', 'Screening', 'Interview', 'Assessment', 'Offer', 'Hired'],
                datasets: [{
                    label: 'Candidates',
                    data: [150, 120, 80, 40, 20, 15],
                    backgroundColor: [
                        '#93c5fd',
                        '#60a5fa',
                        '#3b82f6',
                        '#2563eb',
                        '#1d4ed8',
                        '#1e40af'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Application Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Applications',
                    data: [180, 220, 250, 300, 280],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>