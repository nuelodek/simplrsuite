<?php
include "../../sql_queries/db.php";
session_start();

// check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    header("Location: login.html");
    exit();
}
$company_id = $_SESSION['company_id'];

// show a button that adds a new contact if there are no contacts
$contacts = [];
$sql = "SELECT contacts.*, 
        CASE 
            WHEN contacts.lead_id IS NOT NULL AND contacts.lead_id != '' THEN CONCAT('linked to lead: ', leads.lead_name) 
            WHEN contacts.customer_id IS NOT NULL AND contacts.customer_id != '' THEN CONCAT('linked to customer: ', customers.business_name) 
            ELSE 'not linked' 
        END AS is_linked 
        FROM contacts 
        LEFT JOIN leads ON contacts.lead_id = leads.unique_id 
        LEFT JOIN customers ON contacts.customer_id = customers.customer_id 
        WHERE contacts.company_id = '$company_id'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
} else {
    echo "
    <div class='no-contacts-container' id='noContactsContainer'>
        <h3>there are no contacts yet</h3>
        <button class='add-contacts' onclick='toggleAddContactModal()'>add contact</button> 
    </div>
        ";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/contacts.css">
</head>

<body>
    <?php include 'header.php' ?>

    <!-- add contact modal -->
    <div class='add-contact-modal item-modal' id='addContactModal'>
        <div class='modal-content'>
            <span class='close' onclick='closeModal("addContactModal")'>&times;</span>
            <h2>Add Contact</h2>
            <form action='add_contact.php' method='POST'>
                <label for='contactName'>Name:</label>
                <input type='text' id='contactName' name='contactName' required>

                <label for='contactEmail'>Email:</label>
                <input type='email' id='contactEmail' name='contactEmail' required>

                <label for='contactPhone'>Phone:</label>
                <input type='tel' id='contactPhone' name='contactPhone' required>

                <button class='' type='submit'>Add Contact</button>
            </form>
        </div>
    </div>

    <div class="contacts-container" id="contactsContainer">
        <h3>Contacts</h3>
        <div class="contacts-grid">
            <?php foreach ($contacts as $contact) { ?>
                <div class="contact-card">
                    <h4><?php echo $contact['contact_name']; ?></h4>
                    <p>Email: <?php echo $contact['contact_email']; ?></p>
                    <p>Phone: <?php echo $contact['contact_phone']; ?></p>
                    <?php if ($contact['is_linked'] !== 'not linked') { ?>
                        <span class="linked-tag"><?php echo $contact['is_linked']; ?></span>
                    <?php } ?>
                    <button class="link-icon" onclick="openLinkModal('<?php echo $contact['unique_id']; ?>')">🔗</button>
                </div>
            <?php } ?>
        </div>

        <!-- Link Contact Modal -->
        <div class="link-contact-modal item-modal" id="linkContactModal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('linkContactModal')">&times;</span>
                <h2>Link Contact</h2>
                <p>Select the type of link:</p>
                <div>
                    <label>
                        <input type="radio" name="linkType" value="lead" onclick="populateDropdown('lead')">
                        Link to Lead
                    </label>
                    <label>
                        <input type="radio" name="linkType" value="customer" onclick="populateDropdown('customer')">
                        Link to Customer
                    </label>
                </div>
                <select id="linkDropdown" style="display:none;"></select>
                <button onclick="submitLink()">Link</button>
            </div>
        </div>

    </div>

    <script src="scripts/contacts.js"></script>
</body>

</html>