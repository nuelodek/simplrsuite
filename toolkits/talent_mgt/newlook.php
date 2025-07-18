<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #4158D0;
            --secondary-color: #C850C0;
            --accent-color: #FFCC70;
            --success-color: #00c853;
            --danger-color: #ff3d00;
            --warning-color: #ffd600;
            --info-color: #00b0ff;
            --sidebar-width: 280px;
            --gradient-1: linear-gradient(43deg, #4158D0 0%, #C850C0 46%, #FFCC70 100%);
            --gradient-2: linear-gradient(160deg, #0093E9 0%, #80D0C7 100%);
            --gradient-3: linear-gradient(90deg, #FF9A8B 0%, #FF6A88 55%, #FF99AC 100%);
        }

        body {
            background: #f0f2f5;
            min-height: 100vh;
        }

        .container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Responsive Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .brand {
            background: var(--gradient-1);
            padding: 20px;
            color: white;
            text-align: center;
        }

        .brand img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
            margin-bottom: 10px;
        }

        .nav-links {
            list-style: none;
            padding: 20px 10px;
        }

        .nav-links li {
            margin-bottom: 5px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            background: var(--gradient-2);
            color: white;
            transform: translateX(5px);
        }

        .nav-links i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: auto;
            color: white;
            font-weight: bold;
        }

        .badge.warning { background: var(--warning-color); color: #333; }
        .badge.success { background: var(--success-color); }
        .badge.danger { background: var(--danger-color); }
        .badge.info { background: var(--info-color); }
        .badge.primary { background: var(--primary-color); }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Mobile Menu Button */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .top-nav {
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-bar {
            flex: 1;
            min-width: 200px;
            max-width: 400px;
            display: flex;
            align-items: center;
            background: #f0f2f5;
            border-radius: 25px;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .search-bar:focus-within {
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .search-bar input {
            border: none;
            outline: none;
            background: none;
            margin-left: 10px;
            width: 100%;
            font-size: 15px;
        }

        .trial-info {
            background: var(--gradient-3);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: var(--gradient-1);
            color: white;
        }

        .stat-info h3 {
            font-size: 28px;
            margin: 10px 0;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-info p {
            color: #666;
            font-size: 16px;
            font-weight: 500;
        }

        .alert {
            margin-top: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            background: #fff5f5;
            color: var(--danger-color);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Welcome Card */
        .welcome-card {
            background: var(--gradient-1);
            border-radius: 20px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .welcome-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.3);
        }

        .progress-bar {
            height: 10px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-bar .fill {
            height: 100%;
            background: white;
            width: 75%;
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu {
                display: block;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .welcome-card {
                text-align: center;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .top-nav {
                flex-direction: column;
                align-items: stretch;
            }
            .search-bar {
                max-width: none;
            }
            .welcome-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="brand">
                <img src="/api/placeholder/60/60" alt="Logo">
                <h2>The Virtual Adviser</h2>
                <p>Company ID: 714980</p>
            </div>
            
            <ul class="nav-links">
                <li>
                    <a href="#" class="active">
                        <i class="fas fa-chart-line"></i>Dashboard
                        <span class="badge warning">5 alerts</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-building"></i>Manage Company
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-bell"></i>Notifications
                        <span class="badge success">10 new</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-envelope"></i>Messages
                        <span class="badge info">100+ unread</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-headset"></i>Support
                        <span class="badge danger">1 ticket open</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-wallet"></i>Bank & Wallets
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-chart-bar"></i>Analytics
                        <span class="badge primary">12 new</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-nav">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Global Search...">
                </div>
                <div class="trial-info">
                    <i class="fas fa-clock"></i>
                    Trial expires in 48 hours
                </div>
            </div>

            <div class="welcome-card">
                <img src="/api/placeholder/80/80" alt="Admin">
                <div class="welcome-info">
                    <h2>Welcome back, Admin!</h2>
                    <p>Your company's profile is 75% complete</p>
                    <div class="progress-bar">
                        <div class="fill"></div>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>0</h3>
                            <p>Subscriptions</p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        Office tool has expired. Renew now
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>2</h3>
                            <p>Automations</p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div class="alert">
                        <i class="fas fa-info-circle"></i>
                        2 finance automations completed
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>34</h3>
                            <p>Workflows</p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                    <div class="alert">
                        <i class="fas fa-chart-line"></i>
                        2900 tasks completed in the last 7 days
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenu = document.querySelector('.mobile-menu');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        mobileMenu.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('expanded');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileMenu.contains(e.target)) {
                    sidebar.classList.remove('active');
                    mainContent.classList.add('expanded');
                }
            }
        });

        // Responsive handling
            
            // Responsive handling
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('expanded');
                }
            });
    
            // Search functionality with animation
            const searchBar = document.querySelector('.search-bar input');
            searchBar.addEventListener('focus', () => {
                searchBar.parentElement.style.boxShadow = '0 0 0 2px var(--primary-color)';
            });
    
            searchBar.addEventListener('blur', () => {
                searchBar.parentElement.style.boxShadow = 'none';
            });
    
            // Add smooth scroll for sidebar links
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelector('.nav-links a.active')?.classList.remove('active');
                    link.classList.add('active');
                    
                    // Add smooth scroll animation
                    const targetId = link.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
    
                    // Close sidebar on mobile after clicking
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('active');
                        mainContent.classList.add('expanded');
                    }
                });
            });
    
            // Add animation for stat cards
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px'
            };
    
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
    
            document.querySelectorAll('.stat-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                observer.observe(card);
            });
    
            // Add ripple effect to buttons
            function createRipple(event) {
                const button = event.currentTarget;
                const ripple = document.createElement('span');
                const rect = button.getBoundingClientRect();
                
                const size = Math.max(rect.width, rect.height);
                const x = event.clientX - rect.left - size / 2;
                const y = event.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                ripple.classList.add('ripple');
                
                button.appendChild(ripple);
                
                ripple.addEventListener('animationend', () => {
                    ripple.remove();
                });
            }
    
            // Add this CSS for ripple effect
            const style = document.createElement('style');
            style.textContent = `
                .ripple {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                }
    
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
    
                .stat-card {
                    position: relative;
                    overflow: hidden;
                    cursor: pointer;
                }
    
                /* Add hover effects for better interactivity */
                .nav-links a:hover {
                    transform: translateX(10px);
                }
    
                .stat-card:hover .stat-icon {
                    transform: scale(1.1) rotate(5deg);
                }
    
                .stat-icon {
                    transition: transform 0.3s ease;
                }
    
                /* Add dark mode support */
                @media (prefers-color-scheme: dark) {
                    body {
                        background: #1a1a1a;
                    }
    
                    .sidebar, .stat-card, .top-nav {
                        background: #2d2d2d;
                        color: #fff;
                    }
    
                    .nav-links a {
                        color: #fff;
                    }
    
                    .search-bar {
                        background: #1a1a1a;
                    }
    
                    .search-bar input {
                        color: #fff;
                    }
    
                    .stat-info p {
                        color: #ccc;
                    }
    
                    .alert {
                        background: rgba(255, 59, 48, 0.1);
                    }
                }
    
                /* Add loading animation */
                .loading {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 3px;
                    background: var(--gradient-1);
                    animation: loading 2s ease-in-out infinite;
                    z-index: 1000;
                }
    
                @keyframes loading {
                    0% {
                        transform: translateX(-100%);
                    }
                    50% {
                        transform: translateX(100%);
                    }
                    100% {
                        transform: translateX(-100%);
                    }
                }
            `;
            document.head.appendChild(style);
    
            // Add loading bar
            const loadingBar = document.createElement('div');
            loadingBar.classList.add('loading');
            document.body.appendChild(loadingBar);
    
            // Remove loading bar after content loads
            window.addEventListener('load', () => {
                loadingBar.style.display = 'none';
            });
    
            // Add ripple effect to all buttons and cards
            document.querySelectorAll('.stat-card, button').forEach(button => {
                button.addEventListener('click', createRipple);
            });
        </script>
    </body>
    </html>