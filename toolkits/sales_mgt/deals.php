<?php
include "../../sql_queries/db.php";
session_start();

// check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    header("Location: login.html");
    exit();
}

$company_id = $_SESSION['company_id'];

//retrieve all deals
$sql = "SELECT * FROM deals WHERE company_id = '$company_id'";
$result = $conn->query($sql);
$deals = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $deals[] = $row;
    }
}else{
    echo "
         <div class='no-deals-container' id='noDealsContainer'>
        <h3>there are no deals yet</h3>
        <button class='add-deal' onclick='toggleAddDealModal()'>add deal</button> 
    </div>
    ";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/deals.css">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Deals</title>
</head>
<body>
    <?php include 'header.php'?>

    <!-- add deal modal -->
    <div class='add-deal-modal item-modal' id='addDealModal'>
        <div class='modal-content'>
            <span class='close' onclick='closeModal("addDealModal")'>&times;</span>
            <h2>Add Deal</h2>
            <form action='add_deal.php' method='POST'>
                <label for='dealWith'>Deal With:</label>
                <select name='dealWith' id='dealWith' required>
                    <option value='lead'>Choose Lead</option>
                    <?php
                    $sql = "SELECT * FROM leads WHERE company_id = '$company_id'";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['lead_id'] . "'>" . $row['lead_name'] . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for='contactEmail'>Email:</label>
                <input type='email' id='contactEmail' name='contactEmail' required>

                <label for='contactPhone'>Phone:</label>
                <input type='tel' id='contactPhone' name='contactPhone' required>

                <button class='' type='submit'>Add Contact</button>
            </form>
        </div>
    </div>
    
    <div class="deals-container">
        
    </div>

    <script src="scripts/deals.js"></script>
</body>
</html>