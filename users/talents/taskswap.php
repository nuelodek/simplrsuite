<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskSwap</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
}

.container {
    width: 90%;
    margin: auto;
    overflow: hidden;
}

header {
    background: #333;
    color: #fff;
    padding: 1rem 0;
}

header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header .logo {
    font-size: 1.5rem;
}

header nav ul {
    list-style: none;
}

header nav ul li {
    display: inline;
    margin-left: 20px;
}

header nav ul li a {
    color: #fff;
    text-decoration: none;
}

header nav ul li a:hover {
    text-decoration: underline;
}

.hero {
    background: url('hero-bg.jpg') no-repeat center center/cover;
    color: #fff;
    height: 400px;
    display: flex;
    align-items: center;
    text-align: center;
}

.hero .container {
    max-width: 600px;
    margin: auto;
}

.hero h2 {
    font-size: 2.5rem;
}

.hero p {
    font-size: 1.2rem;
    margin: 1rem 0;
}

.hero .btn {
    background: #f4b400;
    color: #333;
    padding: 0.8rem 1.2rem;
    text-decoration: none;
    border-radius: 5px;
}

.hero .btn:hover {
    background: #e4a400;
}

.featured-tasks {
    padding: 2rem 0;
}

.featured-tasks .tasks-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.task-card {
    background: #f4f4f4;
    padding: 1rem;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.task-card h3 {
    margin-bottom: 10px;
}

.task-card p {
    margin-bottom: 10px;
}

.task-card .btn {
    display: inline-block;
    background: #333;
    color: #fff;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 5px;
}

.task-card .btn:hover {
    background: #555;
}

.how-it-works {
    background: #333;
    color: #fff;
    padding: 2rem 0;
    text-align: center;
}

.how-it-works .steps {
    display: flex;
    justify-content: space-between;
}

.how-it-works .step {
    width: 30%;
}

.testimonials {
    padding: 2rem 0;
    text-align: center;
}

.testimonials .testimonial-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.testimonial {
    background: #f4f4f4;
    padding: 1rem;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

footer {
    background: #333;
    color: #fff;
    text-align: center;
    padding: 1rem 0;
}

footer nav ul {
    list-style: none;
}

footer nav ul li {
    display: inline;
    margin-left: 20px;
}

footer nav ul li a {
    color: #fff;
    text-decoration: none;
}

footer nav ul li a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>TaskSwap</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="browse_tasks.php">Browse Tasks</a></li>
                    <li><a href="leaderboard.php">Leaderboard</a></li>
                    <li><a href="community.php">Community</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Welcome to TaskSwap</h2>
                <p>Exchange tasks, earn points, and unlock rewards!</p>
                <a href="register.php" class="btn">Get Started</a>
            </div>
        </section>

        <section class="featured-tasks">
            <div class="container">
                <h2>Featured Tasks</h2>
                <div class="tasks-grid">
                    <!-- Example Task -->
                    <div class="task-card">
                        <h3>Task Title</h3>
                        <p>Task description...</p>
                        <p><strong>Points:</strong> 50 XP</p>
                        <a href="task_detail.php?id=1" class="btn">View Task</a>
                    </div>
                    <!-- Add PHP loop to fetch and display tasks from the database -->
                    <?php
                    // Assuming a connection to the database has been established
                    $query = "SELECT * FROM tasks WHERE archived_at IS NULL ORDER BY created_at DESC LIMIT 4";
                    $result = mysqli_query($conn, $query);
                    while($task = mysqli_fetch_assoc($result)) {
                        echo "<div class='task-card'>";
                        echo "<h3>" . htmlspecialchars($task['title']) . "</h3>";
                        echo "<p>" . htmlspecialchars($task['description']) . "</p>";
                        echo "<p><strong>Points:</strong> " . htmlspecialchars($task['points']) . " XP</p>";
                        echo "<a href='task_detail.php?id=" . htmlspecialchars($task['id']) . "' class='btn'>View Task</a>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </section>

        <section class="how-it-works">
            <div class="container">
                <h2>How It Works</h2>
                <div class="steps">
                    <div class="step">
                        <h3>1. Post a Task</h3>
                        <p>Describe the task you need help with and set a reward.</p>
                    </div>
                    <div class="step">
                        <h3>2. Choose a Task</h3>
                        <p>Browse tasks posted by others and choose one to complete.</p>
                    </div>
                    <div class="step">
                        <h3>3. Earn Points</h3>
                        <p>Complete tasks to earn points and unlock rewards.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <div class="container">
                <h2>What Our Users Say</h2>
                <div class="testimonial-grid">
                    <div class="testimonial">
                        <p>"TaskSwap has made my life so much easier! I love earning points while getting things done."</p>
                        <h4>- User Name</h4>
                    </div>
                    <div class="testimonial">
                        <p>"The community is amazing and I enjoy helping others with their tasks."</p>
                        <h4>- User Name</h4>
                    </div>
                    <!-- Additional testimonials can be added here -->
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 TaskSwap. All rights reserved.</p>
            <nav>
                <ul>
                    <li><a href="/public/privacypolicy.php">Privacy Policy</a></li>
                    <li><a href="terms_of_service.php">Terms of Service</a></li>
                    <li><a href="contact_us.php">Contact Us</a></li>
                </ul>
            </nav>
        </div>
    </footer>
</body>
</html>
