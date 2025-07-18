<?php
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from database
$sql = "SELECT * FROM talents";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #3498db;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f5f5f5;
            transition: background-color 0.3s ease;
        }
        .no-data {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
            font-size: 18px;
            font-weight: 300;
        }
        a {
            color: #3498db;
            text-decoration: none;
            margin-right: 10px;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Talent Database</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Preferred Role</th>
                        <th>Email</th>
                        <th>Experience</th>
                        <th>Preffered Location</th>
                        <th>Actions</th>
                        
                        
id	
name	
email	
telephone	
preferred_role	
experience	
location	
gender	
profile_picture	
resume_file	
skills	
education	
certifications	
languages	
availability	
salary_expectation	
currency	
portfolio_url	
linkedin_profile	
contact_references	
other_information	
preferred_working_arrangement	
talent_id	
visasponsorship	
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['preferred_role']); ?></td>
                            <td><?php echo htmlspecialchars($row['experience']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td>
                                <a href="/talents/viewprofile.php?id=<?php echo htmlspecialchars($row['id']); ?>">View Profile</a>
                                <a href="/talents/downloadresume.php?id=<?php echo htmlspecialchars($row['id']); ?>">Download Resume</a>
                                <a href="/talents/sendemail.php?id=<?php echo htmlspecialchars($row['id']); ?>">Send Email</a>
                                <a href="/talents/scheduleinterview.php?id=<?php echo htmlspecialchars($row['id']); ?>">Schedule Interview</a>
                                <a href="/talents/talentchat.php?id=<?php echo htmlspecialchars($row['id']); ?>">Chat</a>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No talents found in the database.</p>
        <?php endif; ?>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
