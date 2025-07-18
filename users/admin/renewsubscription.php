
<?php
// Include necessary files
require './session.php';
require '../../sql_queries/db.php';

// Check if subscription ID and tool are provided
if (!isset($_GET['id']) || !isset($_GET['tool'])) {
    die("Error: Missing subscription ID or tool name.");
}

$subscriptionId = $_GET['id'];
$tool = $_GET['tool'];

// Fetch current subscription details
$sql = "SELECT * FROM toolkit_subscriptions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subscriptionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Subscription not found.");
}

$subscription = $result->fetch_assoc();

// HTML for the renewal form
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Renew <?php echo ucfirst($tool); ?> Subscription</title>
    <script src="https://checkout.flutterwave.com/v3.js"></script>
    <style>
        body {
            background-color: rgb(165, 163, 163);
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #c7c7c7;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: black;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Renew <?php echo ucfirst($tool); ?> Subscription</h1>
        <form id="renewal-form" onsubmit="event.preventDefault(); submitRenewal();">
            <input type="hidden" id="subscription-id" value="<?php echo $subscriptionId; ?>">
            <input type="hidden" id="tool" value="<?php echo $tool; ?>">
            <div class="form-group">
                <label for="months">Number of months to renew:</label>
                <input type="number" id="months" name="months" min="1" max="12" required>
            </div>
            <div class="form-group">
                <label for="currency">Currency:</label>
                <select id="currency" name="currency">
                    <option value="NGN">NGN (₦)</option>
                    <option value="USD">USD ($)</option>
                </select>
            </div>
            <button type="submit">Renew Subscription</button>
        </form>
    </div>

    <script>
        function submitRenewal() {
            const subscriptionId = document.getElementById('subscription-id').value;
            const tool = document.getElementById('tool').value;
            const months = document.getElementById('months').value;
            const currency = document.getElementById('currency').value;

            // Calculate cost (you may want to fetch this from the server based on the tool and months)
            const costPerMonth = 3000; // Example cost, adjust as needed
            const amount = costPerMonth * months;

            FlutterwaveCheckout({
                public_key: "FLWPUBK_TEST-acc539ebd2b1244fcf32835b66301422-X",
                tx_ref: "renewal_" + Date.now(),
                amount: amount,
                currency: currency,
                payment_options: "card, banktransfer, ussd",
                customer: {
                    email: "<?php echo $email; ?>",
                    name: "<?php echo $company_name; ?>",
                },
                callback: function(response) {
                    if (response.status === "successful") {
                        // Send renewal data to the server
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'process_renewal.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                alert('Subscription renewed successfully!');
                                window.location.href = 'subtable.php';
                            } else {
                                alert('Error processing renewal. Please try again.');
                            }
                        };
                        xhr.send(`subscription_id=${subscriptionId}&tool=${tool}&months=${months}¤cy=${currency}&amount=${amount}`);
                    } else {
                        alert('Payment failed. Please try again.');
                    }
                },
                onclose: function() {
                    // Handle modal close
                },
            });
        }
    </script>
</body>
</html>
