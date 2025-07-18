<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <title>Messages Interface</title>
    <style>
        /* Main container */
        .messagesModal {
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            margin: 1% auto;
            border: 1px solid #888;
            width: 100%;
            max-width: 1150px;
            border-radius: 10px;
            height: 95%;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            padding: 0px 0px 20px 0px;
            position: relative;
        }

        /* Header section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1.6px solid #e7e7e7;
            background-color: #f9f9f9;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .header h4 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .close-btn {
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            padding: 5px 10px;
            color: #888;
            background-color: transparent;
            border: none;
        }

        .close-btn:hover {
            color: #000;
        }

        /* Sidebar (Panels section) */
        .direcMessages {
            width: 20%;
            border-right: 1px solid #e7e7e7;
            padding: 10px;
            overflow-y: auto;
        }

        .direcMessages h4 {
            font-weight: bold;
            font-size: 18px;
        }

        .messages_tab {
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            border-bottom: 1px solid #e7e7e7;
            margin-bottom: 20px;
        }

        .messages_tab button:hover {
            background-color: #f1f1f1;
        }

        .messages_tab button:focus {
            border-bottom: 2px solid #4CAF50;
            font-weight: bolder;
            transition-duration: .5s;
        }

        .messages_tablinks {
            background-color: #ffffff;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 11px 3px;
            transition: 0.3s;
            font-size: 11px;
            border-bottom: 2px solid transparent;
            font-weight: normal;
            text-align: left;
        }

        .messages_tablinks.active{
            border-bottom: 2px solid #4CAF50;
            font-weight: bolder;
            transition-duration: .5s;
        }

        /* Chat section */
        .chat {
            width: 60%;
            padding: 10px;
        }

        .chat-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .all-chats {
            border: 1px solid #e7e7e7;
            height: 300px;
            overflow-y: auto;
            padding: 10px;
            background-color: #f9f9f9;
            margin-bottom: 10px;
        }

        .text-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80%;
            width: 100%;
            max-height: 4rem;
            margin-top: 20px;
        }

        .chat-box {
            width: 100%;
            height: 60%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1.5px solid #e7e7e7;
            border-radius: 0.5rem;
        }

        .chat-box:focus {
            border-color: #4CAF50;
        }

        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            margin-left: 8px;
            color: #666;
        }

        .icon-button:hover {
            color: #4CAF50;
            transition-duration: .5s;
        }

        .profile-btn {
            background-color: transparent;
            color: #4CAF50;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .profile-btn:hover {
            background-color: #f1f1f1;
        }

        /* Modal for attachment options */
        .att-modal {
            display: none;
            position: absolute;
            bottom: 70px;
            right: 200px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 100;
            padding: 10px;
            width: 150px;
            opacity: 0;
            transform: translateY(100%);
            /* Start from below the button */
            transition: all 0.3s ease-in-out;
            /* Smooth transition */
        }

        .att-modal.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .att-modal button {
            width: 100%;
            border: none;
            background: none;
            text-align: left;
            padding: 10px;
            cursor: pointer;
        }

        .att-modal button:hover {
            background-color: #f1f1f1;
        }

        /* Profile section */
        .profile {
            display: none;
            width: 20%;
            padding: 10px;
            border-left: 1px solid #e7e7e7;
        }

        .profile h4 {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .profile-details {
            text-align: center;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #f1f1f1;
            margin: 0 auto 10px auto;
        }

        .profile-info h5 {
            font-weight: bold;
        }

        .profile-info p {
            color: #666;
            font-size: 12px;
        }

        .badge {
            background-color: #4CAF50;
            color: white;
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 10px;
        }

        /* Modal for emoji picker */
        .emoji-picker {
            display: none;
            position: absolute;
            bottom: 70px;
            right: 200px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 100;
            padding: 10px;
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            opacity: 0;
            transform: translateY(100%);
            transition: all 0.3s ease-in-out;
        }

        .emoji-picker.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .emoji-category {
            padding: 10px 0;
        }

        .emoji-category h4 {
            cursor: pointer;
            margin-bottom: 5px;
        }

        .emoji-list {
            display: none;
            flex-wrap: wrap;
        }

        .emoji-list span {
            font-size: 24px;
            margin: 5px;
            cursor: pointer;
        }

        .emoji-list.active {
            display: flex;
        }

        /* Main content after header */
        .main_content {
            display: inline-flex;
            flex: 1;
            height: 90%;
        }
    </style>
</head>

<body>

    <div class="messagesModal">
        <!-- Header -->
        <div class="header">
            <h4>Messages</h4>
            <button id="closeBtn" class="close-btn">X</button>
        </div>

        <!-- Main content container -->
        <div class="main_content">
            <!-- Sidebar (Panels) -->
            <div class="direcMessages">
                <h4>Panels</h4>
                <div class="messages_tab">
                    <button class="messages_tablinks">Feed</button>
                    <button class="messages_tablinks">Start Here</button>
                    <button class="messages_tablinks">What's New</button>
                    <button class="messages_tablinks">Resources</button>
                </div>
                <h4>Teams</h4>
                <div class="messages_tab">
                    <button class="messages_tablinks">Marketing</button>
                    <button class="messages_tablinks">Development</button>
                </div>
                <h4>Managers</h4>
                <div class="messages_tab">
                    <button class="messages_tablinks">John Doe</button>
                    <button class="messages_tablinks">Jane Smith</button>
                </div>
                <h4>Employees</h4>
                <?php
                include '../../sql_queries/db.php';
                $companyId = $_SESSION['company_id'];
                $sql = "SELECT * FROM employees WHERE company_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $companyId);
                $stmt->execute();
                $result = $stmt->get_result();

                $employees = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $employees[] = $row;
                    }
                }

                $conn->close();
                $employees_list = $employees;
                echo '<div class="messages_tab">';
                foreach ($employees_list as $employee) {
                    echo '<button class="messages_tablinks" id="emp_btn" data-employee-id="' . $employee['unique_id'] . '" onclick="handleEmployeeButtonClick(\'' . $employee['unique_id'] . '\')">' . $employee['first_name'] . ' ' . $employee['last_name'] . '</button>';
                }
                echo '</div>';

                ?>
            </div>

            <!-- Chat section -->
            <div class="chat">
                <button class="profile-btn" onclick="toggleProfileSection()">
                    <h4 class="chat-title" id="chatUserName"></h4>
                </button>
                <div class="all-chats">
                    <!-- <p>User1: Hi there!</p>
                    <p>User2: Hello, how's it going?</p> -->
                </div>
                <div class="text-box">
                    <?php
                        include '../../sql_queries/db.php';
                        $companyId = $_SESSION['company_id'];
                        $sql = "SELECT * FROM employees WHERE company_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $companyId);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $employees = [];
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $employees[] = $row;
                            }
                        }
                        $conn->close();
                        $employees_list = $employees;

                    echo     '<input name="chat-box" id="chat-box" class="chat-box" placeholder="Type your message here...">';
                    echo '<button id="emojiButton" class="icon-button">';
                    echo    '<i class="fas fa-smile"></i>';
                    echo'</button>';

                    echo'<button id="micButton" class="icon-button">';
                    echo    '<i class="fas fa-microphone"></i>';
                    echo'</button>';

                    echo'<button id="attachButton" class="icon-button">';
                    echo    '<i class="fas fa-paperclip"></i>';
                    echo'</button>';
                    echo'<button id="sendButton" onclick="sendMessageToRecipient(\'' . $employee['unique_id'] . '\')" class="icon-button">';
                    echo    '<i class="fas fa-paper-plane"></i>';
                    echo'</button>';
                    ?>
                    
                </div>


                <!-- Modal for attachments -->
                <div id="attModal" class="att-modal">
                    <button>Image</button>
                    <button>File</button>
                    <button>Document</button>
                </div>

                <!-- Emoji Picker Modal -->
                <div id="emojiPicker" class="emoji-picker">
                    <div class="emoji-category">
                        <h4 id="smileys">Smileys & People</h4>
                        <div class="emoji-list" id="smileysList">
                            <span>😊</span><span>😂</span><span>😍</span><span>😎</span>
                        </div>
                    </div>
                    <div class="emoji-category">
                        <h4 id="animals">Animals & Nature</h4>
                        <div class="emoji-list" id="animalsList">
                            <span>🐶</span><span>🐱</span><span>🐭</span><span>🦄</span>
                        </div>
                    </div>
                    <!-- Add more categories here -->
                </div>
            </div>

            <!-- Profile section -->
            <div id="profile-section" class="profile">
                <h4>Profile</h4>
                <div class="profile-details">
                    <div class="profile-img"></div>
                    <div class="profile-info">
                        <h5 id="profile-name">John Doe</h5>
                        <p>Manager</p>
                        <span class="badge">Active</span>
                    </div>
                </div>
            </div>
        </div>



</body>
<script src="../../scripts/messages.js">

</script>
</html>