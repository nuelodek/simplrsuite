<?php
include "../../sql_queries/db.php";
session_start();

// check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    header("Location: login.html");
    exit();
}

$company_id = $_SESSION['company_id'];

$accounts = [];
$sql = "SELECT * FROM accounts WHERE company_id = '$company_id'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
}else{
    echo "
         <div class='no-accounts-container' id='noAccountsContainer'>
        <h3>there are no accounts yet</h3>
        <button class='add-account' onclick='toggleAddAccountModal()'>add account</button> 
    </div>
    ";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/accounts.css">
    <title>Accounts</title>
</head>
<body>
    <?php include 'header.php'?>

    <!-- add-account-modal -->
    <div id="addAccountModal" class="add-account-modal item-modal" id="addAccountModal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addAccountModal')">&times;</span>
                <h2>Add Account</h2>
                <form action="add_account.php" method="POST">
                    <label for="accountOwner">Owner:</label>
                    <select id="accountOwner" name="accountOwner" required>
                        <option value="">Select Owner</option>
                        <!-- Fetch and display account owners from the database -->
                        <?php
                        //fetch all cutomers from the database
                        $customer_sql = "SELECT * FROM customers WHERE company_id = $companyId";
                        $result = mysqli_query($conn, $customer_sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $customer_id = $row['customer_id'];
                            $business_name = $row['business_name'];
                            echo "<option value='$customer_id'>$business_name</option>";
                        }
                        if(mysqli_num_rows($result) == 0){
                            echo "<option value=''>No customers found</option>";
                        }
                        ?>
                    </select>

                    <label for="accountEmail">Email:</label>
                    <input type="email" id="accountEmail" name="accountEmail" required>

                    <label for="accountPhone">Phone:</label>
                    <input type="tel" id="accountPhone" name="accountPhone" required>

                    <button type="submit">Add Account</button>
                </form>
            </div>
        </div>

        <div class="accounts-container">

        </div>
    
</body>
</html>
