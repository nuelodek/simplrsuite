console.log("Script loaded");

function appendMessage(sender, message, timestamp = null) {
  const chatContainer = document.querySelector(".all-chats");
    const messageElement = document.createElement("p");
    const time = timestamp
      ? ` (${new Date(timestamp).toLocaleTimeString()})`
      : "";
    messageElement.textContent = `${sender}: ${message}${time}`;
    chatContainer.appendChild(messageElement);
    chatContainer.scrollTop = chatContainer.scrollHeight;
  }

let userId; // Declare a global variable

// Fetch the session and store the userId
fetch("sesssion.php")
  .then((response) => response.json())
  .then((data) => {
    userId = data.user_id; // Assign the value to the global variable
    console.log("Logged in user ID:", userId);

    // Call a function or perform further actions with userId here
    initializeChat();
  })
  .catch((error) => {
    console.error("Error fetching user ID:", error);
  });

// Other parts of the script

function initializeChat() {
  const attachButton = document.getElementById("attachButton");
  const attModal = document.getElementById("attModal");
  const closeBtn = document.getElementById("closeBtn");
  const messages_Modal = document.getElementById("messages");
  

  // Toggle the emoji picker visibility
  const emojiButton = document.getElementById("emojiButton");
  const emojiPicker = document.getElementById("emojiPicker");
  const smileysList = document.getElementById("smileysList");
  const animalsList = document.getElementById("animalsList");

  // Toggle attach modal
  attachButton.addEventListener("click", () => {
    attModal.classList.toggle("active");
  });

  // Close modal on close button click
  closeBtn.addEventListener("click", () => {
    console.log("close button clicked");
    messages_Modal.style.display = "none";
  });

  emojiButton.addEventListener("click", () => {
    emojiPicker.classList.toggle("active");
  });

  // Toggle emoji categories
  document.getElementById("smileys").addEventListener("click", () => {
    smileysList.classList.toggle("active");
  });

  document.getElementById("animals").addEventListener("click", () => {
    animalsList.classList.toggle("active");
  });

  // Insert emoji into chat box
  document.querySelectorAll(".emoji-list span").forEach((emoji) => {
    emoji.addEventListener("click", () => {
      const chatBox = document.getElementById("chat-box");
      chatBox.value += emoji.textContent;
      emojiPicker.classList.remove("active"); // Close the emoji picker after selection
    });
  });

  // Assuming the employee's name is dynamically fetched or passed into the chat
  const employeeName = "John Doe"; // Replace this with the dynamic name

  // Set the chat title and profile name dynamically
  document.getElementById("chatUserName").innerText = employeeName;

  // Connect to WebSocket server
  const socket = io("http://localhost:3009");

  const chatContainer = document.querySelector(".all-chats");

  socket.on("receiveMessage", (data) => {
    const { sender_id, message } = data;
    const sender = sender_id === userId ? "You" : "User";
    appendMessage(sender, message);
  });

  chatContainer.innerHTML = "<p>select a user to chat</p>";

  
}


// Handle employee button clicks
function handleEmployeeButtonClick(employeeId) {
  // Ensure employeeId is valid before proceeding
  if (!employeeId) {
    console.error("Invalid employee ID");
    return;
  }

  const socket = io("http://localhost:3009");
  const chatContainer = document.querySelector(".all-chats");
  
  console.log(`Employee ID: ${employeeId}`);

  const allButtons = document.querySelectorAll(".messages_tablinks");
  allButtons.forEach((button) => {
    button.classList.remove("active");
  });

  const clickedButton = document.querySelector(
    `button[data-employee-id="${employeeId}"]`
  );

  // Ensure the clicked button exists before proceeding
  if (!clickedButton) {
    console.error("Employee button not found");
    return;
  }

  clickedButton.classList.add("active");

  chatContainer.innerHTML = "<p>Loading...</p>";
  socket.emit("fetchMessages", {
    user_id: userId, // Use the global userId here
    recipient_id: employeeId,
  });

  socket.on("messageHistory", (messages) => {
    chatContainer.innerHTML = ""; // Clear previous messages
    messages.forEach((msg) => {
      const sender = msg.sender_id === userId ? "You" : "User";
      appendMessage(sender, msg.message, msg.timestamp);
    });
  });
  
}

function sendMessageToRecipient(recipientId) {
  const socket = io("http://localhost:3009");
  const chatBox = document.getElementById("chat-box");

  const message = chatBox.value;
  if (message.trim()) {
    socket.emit("sendMessage", {
      sender_id: userId, // Use the global userId here
      recipient_id: recipientId,
      message: message,
    });

    appendMessage("You", message);
    chatBox.value = "";
  }
}

// Function to toggle the profile section visibility
function toggleProfileSection() {
    const profileSection = document.getElementById("profile-section");
  
    // Toggle profile section visibility
    if (
      profileSection.style.display === "none" ||
      profileSection.style.display === ""
    ) {
      profileSection.style.display = "block";
    } else {
      profileSection.style.display = "none";
    }
  }
