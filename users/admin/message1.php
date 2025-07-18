<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Layout</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
        }

        .modal-content {
            background-color: #ffffff;
            margin: 5% auto;
            padding: 25px;
            width: 95%;
            max-width: none;
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close {
            cursor: pointer;
            font-size: 28px;
            color: #777;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #333;
        }

        .modal-body {
            display: flex;
            justify-content: space-between;
            height: 400px;
        }

        /* Left Panel */
        .left-panel {
            width: 20%;
            padding: 15px;
            border-radius: 8px;
            overflow-y: auto;
            max-height: 400px;
            font-size: 0.8em;
        }

        .left-panel h3 {
            margin-top: 0;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .left-panel h3 .fa-plus {
            cursor: pointer;
            color: #007bff;
        }

        .left-panel ul {
            list-style-type: none;
            padding: 0;
        }

        .left-panel ul li {
            margin: 12px 0;
        }

        .left-panel ul li a {
            text-decoration: none;
            color: #4a4a4a;
            transition: color 0.3s ease;
        }

        .left-panel ul li a:hover {
            color: #007bff;
        }

        .left-panel .category {
            margin-top: 20px;
        }

        .left-panel .category h4 {
            margin-bottom: 10px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .left-panel .category h4 .fa-plus {
            cursor: pointer;
            color: #007bff;
        }

        /* Middle Section */
        .message-section {
            width: 50%;
            font-size: 0.9em;
            display: flex;
            flex-direction: column;
            padding: 15px;
            overflow-y: auto;
            max-height: 500px;
        }

        .message-section h3 {
            margin-top: 0;
            color: #333;
        }

        #messages {
            flex-grow: 1;
            padding: 15px;
            margin-bottom: 15px;
            overflow-y: auto;
            max-height: 250px;
            border-radius: 8px;
            background-color: #fff;
            color: #808080;
        }

        #messageInput {
            padding: 12px;
            width: calc(100% - 24px);
            margin-bottom: 15px;
            resize: vertical;
            max-height: 100px;
            border: none;
            outline: none;
        }

        .message-icons {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 4px;
            background-color: transparent;
        }

        .message-icons i {
            font-size: 20px;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s ease;
            margin-right: 15px;
        }

        .message-icons i:hover {
            color: #007bff;
        }

        .message-icons .fa-paper-plane {
            margin-left: auto;
            color: #007bff;
        }

        /* Right Panel */
        .right-panel {
            width: 20%;
            padding: 15px;
            border-radius: 8px;
            overflow-y: auto;
            max-height: 400px;
            font-size: 0.9em;
        }

        .right-panel h3 {
            margin-top: 0;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .right-panel h3 .fa-plus {
            cursor: pointer;
            color: #007bff;
        }

        .right-panel ul {
            list-style-type: none;
            padding: 0;
        }

        .right-panel ul li {
            margin: 12px 0;
            color: #4a4a4a;
        }

        #openModalBtn {
            margin: 20px;
            font-size: 16px;
        }

        .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .profile-section {
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9em;
        }

        .profile-section h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #333;
            font-size: 1.1em;
        }

        .profile-section ul {
            list-style-type: none;
            padding: 0;
        }

        .profile-section ul li {
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .profile-section .tag {
            display: inline-block;
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75em;
            color: #495057;
        }

        .profile-picture {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .profile-tabs {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .profile-tabs a {
            text-decoration: none;
            color: #333;
            padding: 3px 6px;
            border-radius: 5px;
            font-size: 0.8em;
        }

        .profile-tabs a:hover {
            background-color: #f0f0f0;
        }

        .profile-tabs a.active {
            background-color: #007bff;
            color: #fff;
        }

        /* Transparent scrollbar styles */
        .left-panel, .message-section, .right-panel, #messages {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        }

        .left-panel::-webkit-scrollbar,
        .message-section::-webkit-scrollbar,
        .right-panel::-webkit-scrollbar,
        #messages::-webkit-scrollbar {
            width: 6px;
        }

        .left-panel::-webkit-scrollbar-track,
        .message-section::-webkit-scrollbar-track,
        .right-panel::-webkit-scrollbar-track,
        #messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .left-panel::-webkit-scrollbar-thumb,
        .message-section::-webkit-scrollbar-thumb,
        .right-panel::-webkit-scrollbar-thumb,
        #messages::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Messages</h2>
                <span class="close" onclick="closeModal()">×</span>
            </div>
            <div class="modal-body">
                <!-- Left Panel for Navigation -->
                <div class="left-panel">
                    <h3>Panels <i class="fas fa-plus"></i></h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-rss icon"></i>Feed</a></li>
                        <li><a href="#"><i class="fas fa-play icon"></i>Start Here</a></li>
                        <li><a href="#"><i class="fas fa-bell icon"></i>Unread Messages</a></li>
                        <li><a href="#"><i class="fas fa-bell icon"></i>#Mentions</a></li>
                        
                        <li><a href="#"><i class="fas fa-book icon"></i>Resources</a></li>
                        <li><a href="#"><i class="fas fa-book icon"></i>Ongoing Projects</a></li>

                    </ul>
                    <div class="category">
                        <h4>Teams <i class="fas fa-plus"></i></h4>
                        <ul>
                            <li><a href="#"><i class="fas fa-users icon"></i>Marketing</a></li>
                            <li><a href="#"><i class="fas fa-users icon"></i>Development</a></li>
                        </ul>
                    </div>
                    <div class="category">
                        <h4>Managers <i class="fas fa-plus"></i></h4>
                        <ul>
                            <li><a href="#"><i class="fas fa-user-tie icon"></i>John Doe</a></li>
                            <li><a href="#"><i class="fas fa-user-tie icon"></i>Jane Smith</a></li>
                        </ul>
                    </div>
                    <div class="category">
                        <h4>Employees <i class="fas fa-plus"></i></h4>
                        <ul>
                            <li><a href="#"><i class="fas fa-user icon"></i>Alice Johnson</a></li>
                            <li><a href="#"><i class="fas fa-user icon"></i>Bob Williams</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Middle Section for Messages -->
                <div class="message-section">
                        <div id="messages">
                        <!-- Example Messages -->
                        <p><strong>User1:</strong> Hi there!</p>
                        <p><strong>User2:</strong> Hello, how's it going?</p>
                    </div>
                    <div class="message region" style="border: 1px solid #ccc; padding: 10px; border-radius: 10px; margin-bottom:-8px;"  >
                    <textarea id="messageInput" placeholder="Type a message..."></textarea>
                    <div class="message-icons">
                        <i class="fas fa-smile"></i>
                        <i class="fas fa-paperclip"></i>
                        <i class="fas fa-image"></i>
                        <i class="fas fa-video"></i>
                        <i class="fas fa-microphone"></i>
                        <i class="fas fa-paper-plane" onclick="sendMessage()"></i>
                    </div>
                </div>
                </div>
                
                <!-- Right Panel for Profile -->
                <div class="right-panel">
                    <div class="profile-section">
                        <h3>Profile</h3>
                        <img src="https://cdn.jsdelivr.net/gh/alohe/avatars@master/png/bluey_3.png" alt="Profile Picture" class="profile-picture">
                        <h2 style="font-size: 1.2em;">kinghenry50</h2>
                        <div class="profile-tabs">
                            <a href="#" class="active">About</a>
                            <a href="#">Posts</a>
                            <a href="#">Comments</a>
                            <a href="#">Spaces</a>
                        </div>
                        <ul>
                            <li>Member since January 18, 2024</li>
                            <li>Last seen 1 week ago</li>
                            <li><span class="tag">NEW EMPLOYEE</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button id="openModalBtn" onclick="openModal()">Open Modal</button>

    <script>
        function openModal() {
            document.getElementById('modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const messageText = messageInput.value.trim();
            if (messageText) {
                const messageDiv = document.createElement('p');
                messageDiv.innerHTML = '<strong>You:</strong> ' + messageText;
                document.getElementById('messages').appendChild(messageDiv);
                messageInput.value = '';  // Clear the input field
                // Scroll to the bottom of the messages
                const messagesContainer = document.getElementById('messages');
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>