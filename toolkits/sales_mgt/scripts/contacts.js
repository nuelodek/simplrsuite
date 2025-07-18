let selectedContactId = null;

function toggleAddContactModal(){
    const addContactModal = document.getElementById("addContactModal");
    addContactModal.style.display = addContactModal.style.display === "block" ? "none" : "block";
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
  }
}

const noContactsContainer = document.getElementById("noContactsContainer");
const contactsContainer = document.getElementById("contactsContainer");

if (noContactsContainer) {
  noContactsContainer.style.display = "block";
  contactsContainer.style.display = "none";
}else{
  noContactsContainer.style.display = "none";
  contactsContainer.style.display = "block";
}



function openLinkModal(contactId) {
  selectedContactId = contactId;
  const contactModal = document.getElementById("linkContactModal");
  contactModal.style.display = "block";
}

// Populate the dropdown based on selection
function populateDropdown(type) {
    const dropdown = document.getElementById("linkDropdown");
    dropdown.style.display = "block";

    // Fetch data dynamically
    fetch(`fetch_${type}s.php`)
        .then((response) => response.json())
        .then((data) => {
            dropdown.innerHTML = ""; // Clear existing options
            data.forEach((item) => {
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = item.name; // Assume the object has 'id' and 'name' properties
                dropdown.appendChild(option);
            });
        })
        .catch((error) => console.error("Error fetching data:", error));
}

// Submit the link
function submitLink() {
    const selectedLinkId = document.getElementById("linkDropdown").value;
    const linkType = document.querySelector('input[name="linkType"]:checked').value;

    // Perform an AJAX request to save the link
    fetch("link_contact.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            contactId: selectedContactId,
            linkId: selectedLinkId,
            type: linkType,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            alert(data.message);
            closeModal("linkContactModal");
        })
        .catch((error) => console.error("Error linking contact:", error));
}