<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Chat</title>
    <style>
        /* Add your CSS styling here */
    </style>
    <script>
        let ws;

        function connectWebSocket() {
            ws = new WebSocket('ws://localhost:3000');

            ws.onopen = function() {
                console.log('Connected to WebSocket server');
            };

            ws.onmessage = function(event) {
                const messageData = JSON.parse(event.data);
                displayMessage(messageData.sender_id, messageData.message);
            };

            ws.onclose = function() {
                console.log('Disconnected from WebSocket server');
            };
        }

        function fetchEmployees() {
            fetch('fetch_employees.php')
                .then(response => response.json())
                .then(data => {
                    let employeeSelect = document.getElementById('employeeSelect');
                    employeeSelect.innerHTML = '';
                    data.forEach(employee => {
                        let option = document.createElement('option');
                        option.value = employee.id;
                        option.textContent = employee.name;
                        employeeSelect.appendChild(option);
                    });
                });
        }

        function sendMessage() {
            const receiverId = document.getElementById('employeeSelect').value;
            const message = document.getElementById('message').value;

            const messageData = {
                sender_id: <?php echo $_SESSION['user_id']; ?>,
                receiver_id: receiverId,
                message: message
            };

            ws.send(JSON.stringify(messageData));
            displayMessage(<?php echo $_SESSION['user_id']; ?>, message);
        }

        function displayMessage(senderId, message) {
            const chatBox = document.getElementById('chatBox');
            const messageDiv = document.createElement('div');
            messageDiv.textContent = `User ${senderId}: ${message}`;
            chatBox.appendChild(messageDiv);
        }

        document.addEventListener('DOMContentLoaded', () => {
            connectWebSocket();
            fetchEmployees();
        });
    </script>
</head>
<body>
    <h1>Admin Chat</h1>
    <select id="employeeSelect" onchange="fetchMessages(this.value)">
        <!-- Options will be populated by fetchEmployees() -->
    </select>
    <div id="chatBox">
        <!-- Chat messages will be displayed here -->
    </div>
    <textarea id="message" placeholder="Type your message here..."></textarea>
    <button onclick="sendMessage()">Send</button>
</body>
</html>
