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
        /*body {*/
        /*    font-family: 'Roboto', sans-serif;*/
        /*    background-color: #f4f4f4;*/
        /*    margin: 0;*/
        /*    padding: 0;*/
        /*}*/
        .talentcontainer {
            width: 100%;
            max-width: 1200px;
            margin:  auto;
            /*padding: 30px;*/
            /*background-color: #fff;*/
            /*box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);*/
            /*border-radius: 12px;*/
            font-size: 11px;
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
            /*box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);*/
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
    <div class="talentcontainer">
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th> Talent ID</th>
                        <th>Name</th>
                        <th>Preffered Role</th>
                        <th>Experience</th>
                        <th>Preffered Location(s)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['talent_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['preferred_role']); ?></td>
                            <td><?php echo htmlspecialchars($row['experience']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td>
                                <a href="/talents/viewprofile.php?id=<?php echo htmlspecialchars($row['id']); ?>">View Profile</a>
                                <a href="/talents/downloadresume.php?id=<?php echo htmlspecialchars($row['id']); ?>">Download Resume</a>
                                <a href="/talents/sendemail.php?id=<?php echo htmlspecialchars($row['id']); ?>">Send Email</a>
                                <a href="/talents/scheduleinterview.php?id=<?php echo htmlspecialchars($row['id']); ?>">Schedule Interview</a>
<a href="#"
<?php
    // Define the unique IDs
    $recruiterUniqueId = 'rec' . $_SESSION['user_id'];
    $talentUniqueId = 'tal' . $row['talent_id'];

    // Create the chat URL (without created_at since it will be generated later)
    $chatUrl = "talentmessages.php?unique_id1=" . urlencode($recruiterUniqueId) . "&unique_id2=" . urlencode($talentUniqueId);
?>
onclick="sendDataAndOpenModal(
    '<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>',
    '<?php echo htmlspecialchars($chatUrl, ENT_QUOTES, 'UTF-8'); ?>',
    '<?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name'], ENT_QUOTES, 'UTF-8'); ?>',
    '<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>',
    '<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>',
    '<?php echo htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8'); ?>',
    new Date().toISOString(),   // Assign 'created_at' dynamically as the current timestamp
    '<?php echo htmlspecialchars($recruiterUniqueId, ENT_QUOTES, 'UTF-8'); ?>',
    '<?php echo htmlspecialchars($talentUniqueId, ENT_QUOTES, 'UTF-8'); ?>'); return false;">Chat</a>

<script>
function sendDataAndOpenModal(id, chatUrl, recruiterName, talentName, talentEmail, recruiterEmail, createdAt, recruiterUniqueId, talentUniqueId) {
    // Prepare data in URL encoded format
    const data = new URLSearchParams({
        'id': id,
        'chat_url': chatUrl + "&created_at=" + encodeURIComponent(createdAt),  
        'recruiter_name': recruiterName,
        'talent_name': talentName,
        'talent_email': talentEmail,
        'recruiter_email': recruiterEmail,
        'created_at': createdAt,
        'recruiter_unique_id': recruiterUniqueId,
        'talent_unique_id': talentUniqueId
    });

    // Send data to chatbutton.php using fetch
    fetch('chatbutton.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: data.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(chatUrl);
            openMessageModal(chatUrl); // Pass the chatUrl to openMessageModal
        } else {
            console.error('Error:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
function openMessageModal(chatUrl) {
    // Create a modal container
    const modal = document.createElement('div');
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.7)'; // Darker background for better focus
    modal.style.display = 'flex';
    modal.style.justifyContent = 'center';
    modal.style.alignItems = 'center';
    modal.style.zIndex = '9999'; // Ensure it stays on top

    // Create an iframe
    const iframe = document.createElement('iframe');
    iframe.src = chatUrl;
    iframe.style.width = '70%'; // Adjust size for a better look
    iframe.style.height = '70%';
    iframe.style.backgroundColor = '#ffffff';
    iframe.style.border = '2px solid #ddd'; // Light border for distinction
    iframe.style.borderRadius = '10px'; // Rounded corners for smoother visuals
    iframe.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.3)'; // Add shadow for depth

    // Add close button
    const closeButton = document.createElement('button');
    closeButton.textContent = '×';
    closeButton.style.position = 'absolute';
    closeButton.style.top = '15px';
    closeButton.style.right = '15px';
    closeButton.style.fontSize = '24px'; // Larger button for ease of use
    closeButton.style.background = 'transparent';
    closeButton.style.border = 'none';
    closeButton.style.color = '#fff';
    closeButton.style.cursor = 'pointer';
    closeButton.onclick = () => document.body.removeChild(modal);

    // Append elements
    modal.appendChild(iframe);
    modal.appendChild(closeButton);

    // Add modal to the body
    document.body.appendChild(modal);
}


</script>


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
    
        <!-- Modal -->
        <div id="messageModal123" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">×</span>
            </div>
        </div>

       
        <style>
        .modal {
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        </style>

</body>
</html>
