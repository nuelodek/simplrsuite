<?php
include 'db.php';
      $userCategory = ''; 
      if (isset($_SESSION['employee_id'])) {
          $userCategory = 'recruiter';
      } elseif (isset($_SESSION['talent_id'])) {
          $userCategory = 'talent';
      }
      

try {
    // Create a connection to the MySQL database
    // $conn = new mysqli($host, $username, $password, $database);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Define your SQL query with placeholders
    $sql = "SELECT chat_url, recruiter_name, talent_name, talent_email, recruiter_email FROM talentchat";

    // Use prepared statements to add placeholders for user input
    $stmt = $conn->prepare($sql);

    // Check for SQL query preparation errors
    if (!$stmt) {
        die("SQL query preparation error: " . $conn->error);
    }

    // Execute the prepared statement
    if (!$stmt->execute()) {
        die("SQL query execution error: " . $stmt->error);
    }

    // Get the result set
    $result = $stmt->get_result();

    // Initialize variables outside the loop
    $talent_name = "";
    $recruiter_name = "";
    $talent_email = "";
    $recruiter_email = "";

    // Check if there are rows returned
    if ($result->num_rows > 0) {
        $talentUniqueId = isset($_GET['unique_id1']) ? $_GET['unique_id1'] : null;
        $recruiterUniqueId = isset($_GET['unique_id2']) ? $_GET['unique_id2'] : null;

        while ($row = $result->fetch_assoc()) {
            $chatUrlFromDB = urldecode($row["chat_url"]);
            $parts = parse_url($chatUrlFromDB);
            parse_str($parts['query'], $urlParams);

            if (
                isset($urlParams['unique_id1']) && $urlParams['unique_id1'] === $talentUniqueId
                && isset($urlParams['unique_id2']) && $urlParams['unique_id2'] === $recruiterUniqueId
            ) {
                // Handle the match here
                $recruiter_name = $row["recruiter_name"]; // Retrieve the recruiter's name from the database
                $talent_name = $row["talent_name"]; // Retrieve the talent's name from the database
                $talent_email = $row["talent_email"]; // Retrieve the talent's email from the database
                $recruiter_email = $row["recruiter_email"];

                break; // Exit the loop when a match is found
            }
        }
    } else {
        // No records found
        echo "No records found.";
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <link rel="shortcut icon" type="image/x-icon" href="vaicon.png">
    <title>Chat</title>
    <style>
        /* CSS styles from chat.php */

        .attachment-label {
            margin-right: 20px;
            margin-top: 0px;
        }
        .chat-body {
            flex: 1;
            padding: 20px;
            height: 50vh;
            overflow-y: auto;
            background: blue;
        }

        .chat-footer {
            display: flex;
            align-items: center;
            padding: 10px;
            width:auto;
            border-top: 1px solid #ddd;
        }

        .typemessage {
            padding: 10px;
            border-radius: 5px;
            width: 750px; /* Fixed width */
            outline: none;
            margin-right: 10px;
        }

        .chat-footer button {
            background: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
            margin-left: 10px;
        }

        .chat-footer button:hover {
            background: #0056b3;
        }

        .file-container {
            margin-bottom: 10px;
            width: auto;
            height: 20px;
            padding: 10px;
        }

        .progress-container {
            width: 100%;
            height: 20px;
            background-color: #f3f3f3;
            border: 1px solid #ccc;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background-color: #4a90e2;
            transition: width 0.3s ease;
        }
        
    .file-container {
      margin-bottom: 10px;
      /*background-color: pink;*/
      width: auto;
      height: 20px;
      padding: 10px;
    }
    .file-info {
      display: inline-block;
      margin-right: 10px;
      font-size: 14px;
    }
    .remove-btn {
      cursor: pointer;
      color: red;
      font-size: 12px;
    }
    .thumbnail {
      max-width: 20px;
      max-height: 20px;
      margin-top: 5px;
    }
  
.explorer img,  .adviser img{
    margin-top: 10px;
    width: 100%;
    border-radius: 10px;
}
.explorer {
    float: right;
    margin: 5px 0; /* Reduced margin */
    padding: 3px 12px; /* Reduced padding */
    border-radius: 5px;
    max-width: 60%;
    border:none;
    line-height: 1.5;
background: #d4f8e8; /* Very light green color */
    align-self: flex-start;
    word-wrap: break-word;
    clear: both;
    display: inline-block;
}

        
        
.explorer:last-of-type{
    margin-bottom: 20px; /* Add your desired margin-bottom value */
}



.adviser {
    float: left;
    margin: 5px 0; /* Reduced margin */
    padding: 3px 12px; /* Reduced padding */
    border-radius: 5px;
    max-width: 60%;
    line-height: 1.5;
    /*background: #4a90e2;*/
    background: #f5f5dc; 
    align-self: flex-start;
    clear: both;
    word-wrap: break-word;
   
}

.chat-footer {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-top: 10px;
    border-top: 1px solid #ddd;
    background: #f6f6f6;
}

#chat-form input[type="text"] {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    outline: none;
    margin-right: 10px;
}

.fa-paperclip {
    margin: 10px;

}

    </style>
</head>
    <body>
    <h2> Messages </h2> 
    
            <div class="file-container"></div>
                        <div class="chat-box chat-body" id="chatWindow"> </div>
                        <form id="chat-form" action="talentchat.php?unique_id1=<?php echo $_GET['unique_id1']; ?>&unique_id2=<?php echo $_GET['unique_id2']; ?>" method="POST">
                <div class="chat-footer">
                    <label for="file-input" class="attachment-label">
                        <i class="fa fa-paperclip"></i>
                        <input type="file" id="file-input" style="display:none;" multiple>
                    </label>
                    <input type="text" placeholder="Type a message" id="message-input" style="margin-top: 0px;" class="typemessage">
                    <button id="sendButton" type="submit"><i class="fa fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function formatChatMessage(message) {
        const chatMessage = document.createElement('div');
        chatMessage.className = `${message.user}`;

        const textElement = document.createElement('div');
        textElement.className = '';

        const fileInfoElement = document.createElement('div');
        fileInfoElement.className = 'file-info';
    
        // Remove the date part if present and get the message text
        const dateStartIndex = message.text.indexOf('[');
        const dateEndIndex = message.text.indexOf(']');
        let messageText = message.text;
        if (dateStartIndex !== -1 && dateEndIndex !== -1) {
            messageText = message.text.substring(dateEndIndex + 1).trim();
        }

        // Check if the message contains a file attachment
        const fileStartIndex = messageText.indexOf('[File:');
        if (fileStartIndex !== -1) {
            const fileEndIndex = messageText.indexOf(']', fileStartIndex);
            if (fileEndIndex !== -1) {
                const fileAttachmentInfo = messageText.substring(fileStartIndex + 6, fileEndIndex).split('.');
                const fileName = fileAttachmentInfo[0].trim();
                const fileExtension = fileAttachmentInfo[1].trim().toLowerCase();
                const baseUrl = "https://simplr.com.ng/employees/work";
                const fileUrl = `${baseUrl}/uploads/${fileName}.${fileExtension}`;

                const fileType = getFileType(fileExtension);

                // Check the file type and display accordingly
                if (fileType === 'image') {
                    const imgElement = document.createElement('img');
                    imgElement.src = fileUrl;
                    fileInfoElement.appendChild(imgElement);
                } else if (fileType === 'pdf') {
                    const pdfLink = document.createElement('a');
                    pdfLink.href = fileUrl;
                    pdfLink.textContent = 'View PDF';
                    fileInfoElement.appendChild(pdfLink);
                }

                textElement.textContent = messageText.substring(fileEndIndex + 1).trim(); // Trim the text after the file attachment
            }
        } else {
            // If it's a regular text message, display the message text as it is
            textElement.textContent = messageText;
        }

        const timeElement = document.createElement('div');
        timeElement.className = 'timeman';

        // Extract the timestamp from the message text and convert it to the desired format
        const timestampString = message.text.substring(1, 20);
        const timestamp = new Date(timestampString);
        const formattedTime = timestamp.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

        timeElement.textContent = formattedTime;

        chatMessage.appendChild(textElement);
        if (fileInfoElement.children.length > 0) {
            chatMessage.appendChild(fileInfoElement);
        }
        chatMessage.appendChild(timeElement);

        return chatMessage;
    }

    // Helper function to determine the file type based on the file extension
    function getFileType(extension) {
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            return 'image';
        } else if (extension === 'pdf') {
            return 'pdf';
        } else {
            return 'unknown';
        }
    }


    let userScrolled = false;
    let previousMessageCount = 0;

    function loadChatMessages() {
        const chatBox = document.querySelector('.chat-box');
        const lastMessageElement = document.querySelector('.lastmessage'); // Select the <small> element
        <script>

      
      const userCategory = '<?php echo $userCategory; ?>';

      </script>
        console.log("userCategory:", userCategory);
        const messagepending = document.getElementById('messagepending');
        let recruiterMessageCount = 0;
        let talentMessageCount = 0; 
    
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const formattedMessages = JSON.parse(xhr.responseText);
 
                chatBox.innerHTML = '';

                // Separate recruiter and talent messages into different arrays
                const recruiterMessages = formattedMessages.recruiter;
                const talentMessages = formattedMessages.talent;

                // Count messages
                recruiterMessageCount = recruiterMessages.length;
                talentMessageCount = talentMessages.length;

                // Determine which last message to display based on userCategory
                let lastMessageText = '';
                if (userCategory === 'talent') {
                    if (recruiterMessageCount > 0) {
                        const lastrecruiterMessage = recruiterMessages[recruiterMessageCount - 1];
                        const timestampEndIndex = lastrecruiterMessage.text.indexOf(']') + 1;
                        lastMessageText = lastrecruiterMessage.text.substring(timestampEndIndex).trim();
                    } else {
                        lastMessageText = 'No recruiter messages found';
                    }
                } else if (userCategory === 'recruiter') {
                    if (talentMessageCount > 0) {
                        const lasttalentMessage = talentMessages[talentMessageCount - 1];
                        const timestampEndIndex = lasttalentMessage.text.indexOf(']') + 1;
                        lastMessageText = lasttalentMessage.text.substring(timestampEndIndex).trim();
                    } else {
                        lastMessageText = 'No talent messages found';
                    }
                }

                // Display the last message text
                // lastMessageElement.textContent = lastMessageText;

                // Count unread messages
                const unreadrecruiterMessages = recruiterMessages.filter(message => !message.read);
                const unreadtalentMessages = talentMessages.filter(message => !message.read);
                const unreadMessagesCount = (userCategory === 'talent') ? unreadrecruiterMessages.length : unreadtalentMessages.length;
                // messagepending.textContent = unreadMessagesCount.toString();

                // Combine all messages for sorting and display
                const allMessages = [...recruiterMessages, ...talentMessages];

                // Sort all messages by timestamp
                allMessages.sort(function (a, b) {
                    return new Date(a.text.slice(1, 20)) - new Date(b.text.slice(1, 20));
                });

                // Process and append the sorted messages to the chat box
                allMessages.forEach(function (message) {
                    const chatMessage = formatChatMessage(message);
                    chatBox.appendChild(chatMessage);
                });

                // Scroll to the bottom if new messages are added or user hasn't scrolled manually
                if (!userScrolled || allMessages.length > previousMessageCount) {
                    scrollToBottom(chatBox);
                }

                // Update the previous message count
                previousMessageCount = allMessages.length;
            }
        };

        const urlParams = new URLSearchParams(window.location.search);
        const unique_id1 = urlParams.get('unique_id1');
        const unique_id2 = urlParams.get('unique_id2');
        xhr.open('GET', `talentchat.php?unique_id1=${unique_id1}&unique_id2=${unique_id2}`, true);
        xhr.send();
    }



    function submitMessage(event) {
        event.preventDefault(); // Prevents the default form submission behavior
  
        const fileContainer = document.querySelector('.file-container');
        fileContainer.innerHTML = ''; // Clear the file container after submission
        const messageInput = document.getElementById('message-input');
        const fileInput = document.getElementById('file-input');
        const formData = new FormData();

        formData.append('message', messageInput.value);
        formData.append('file', fileInput.files[0]);

        const xhr = new XMLHttpRequest();

        // Progress bar elements
        const progressBar = document.createElement('div');
        progressBar.classList.add('progress-bar');

        const progressContainer = document.createElement('div');
        progressContainer.classList.add('progress-container');
        progressContainer.appendChild(progressBar);

        const chatBox = document.querySelector('.chat-box');
        chatBox.appendChild(progressContainer);

        xhr.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                const percentComplete = (event.loaded / event.total) * 100;
                progressBar.style.width = percentComplete + '%';
            }
        };

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                progressContainer.remove(); // Remove progress bar after upload completes

                if (xhr.status === 200) {
                    messageInput.value = '';
                    fileInput.value = '';

                    // Update the chat box immediately after sending the message
                    loadChatMessages();
                } else {
                    // Handle error cases
                    console.error('Error uploading file:', xhr.status, xhr.statusText);
                }
            }
        };

        const urlParams = new URLSearchParams(window.location.search);
        const unique_id1 = urlParams.get('unique_id1');
        const unique_id2 = urlParams.get('unique_id2');
        xhr.open('POST', `talentchat.php?unique_id1=${unique_id1}&unique_id2=${unique_id2}`, true);
        xhr.send(formData);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const chatForm = document.getElementById('chat-form');
        chatForm.addEventListener('submit', submitMessage);

        // Load chat messages initially
        loadChatMessages();

        // Periodically refresh the chat messages every 1 second
        setInterval(loadChatMessages, 1000);

        // Track if the user has manually scrolled
        const chatBox = document.querySelector('.chat-box');
        chatBox.addEventListener('scroll', function () {
            userScrolled = chatBox.scrollTop + chatBox.clientHeight < chatBox.scrollHeight;
        });
    });

    function scrollToBottom(chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

        </script>
    
    <script>
    function displayFile(file) {
      const fileItem = document.createElement('div');
      fileItem.classList.add('file-item');

      const fileInfo = document.createElement('span');
      fileInfo.classList.add('file-info');
      fileInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;

      const thumbnail = document.createElement('img');
      thumbnail.classList.add('thumbnail');
  
      // Check if the file is an image
      if (file.type.startsWith('image')) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(event) {
          thumbnail.src = event.target.result;
        }
        fileItem.appendChild(thumbnail); 
      }

      const removeBtn = document.createElement('span');
      removeBtn.classList.add('remove-btn');
      removeBtn.textContent = 'x';
      removeBtn.addEventListener('click', () => {
        fileItem.remove(); // Remove the file container when remove button is clicked
        // Hide file container if there are no files left
        if (fileContainer.childElementCount === 0) {
          fileContainer.style.display = 'none';
        }
      });

      fileItem.appendChild(fileInfo);
      fileItem.appendChild(removeBtn);
      fileContainer.appendChild(fileItem);
    }

    </script>
    
    </body>
    </html>