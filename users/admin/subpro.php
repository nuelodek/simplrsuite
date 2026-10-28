<?php

require './session.php';
require '../../sql_queries/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Toolkit Selection</title>
    <script src="https://checkout.flutterwave.com/v3.js"></script>

    <style>
        body {
            background-color: rgb(165, 163, 163);
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .title {
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 40px; /* Adjusted gap between columns */
        }
        .form-container div {
            display: flex;
            align-items: center;
        }
        .form-container label {
            flex: 1;
        }
        .form-container span {
            margin-right: 20px; /* Increased spacing between Toolkit and Month labels */
            /* font-weight: bold; */
        }
        .form-container input[type="number"] {
            width: 60px;
        }
        .form-container input[type="checkbox"] {
            margin-right: 10px;
        }
        .form-container button {
            grid-column: span 2;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #c7c7c7;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .form-container button:hover {
            background-color: black;
            color: white;
        }
        .result {
            grid-column: span 2;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
        .hidden-date {
            display: none;
        }

        .labels {
            gap: 240px;
        }
    </style>
</head>
<body> 
    <div class="container">
        <h1 class="title">Toolkit Selection for <?php echo $company_name; ?>s</h1>
        <p class="subtitle">Control all your business processes from one powerful dashboard</p>

        <form class="form-container" id="subscription-form" method="POST" onsubmit="event.preventDefault(); submitForm();">
            
              <label style="font-weight: bold;"> Select Currency</label>
                  <br>
                  <select id="currency-selector" onchange="updateCurrency()" class="currencyselect" style="width: 50%; padding: 5px;" >
                    <option value="NGN" selected>NGN (₦)</option>
                    <option value="USD">USD ($)</option>
                </select>
               
               <div class="nothing"></div>
               
               
               
               
            <div class="labels">
                <span>Toolkit</span>
                <span>Month</span>
            </div>
            <div class="labels">
                <span>Toolkit</span>
                <span>Month</span>
            </div>
            <div>
                
                 
  <input type="number" name="company_id" id="companyid" value="<?php echo $company_id; ?>" hidden tabindex="-1"> 
                  <input type="email" name="email" id="buyeremail" value="<?php echo $email; ?>" hidden tabindex="-1"> 
                  <input type="text" value="Being payment for tools" id="purchaseDescription" hidden tabindex="-1">
                  <input type="text" name="mycompany" id="companyname" value="Simplr Business Suite" hidden tabindex="-1">
                  <input type="name" id="customername" value="<?php echo $company_name;?>" hidden tabindex="-1">

              
                <input type="checkbox" id="payroll" name="payroll" onclick="toggleToolkit('payroll')">
                <label>Payroll & Accounting</label>
                <input type="number" id="payroll-months" name="payroll-months" min="1" max="12" step="1" onchange="updateCost('payroll')" disabled>
                <input type="date" id="payroll-startdate" name="payroll-startdate" class="hidden-date">
                <input type="date" id="payroll-expireddate" name="payroll-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="recruiting" name="recruiting" onclick="toggleToolkit('recruiting')">
                <label>Recruiting & Human Resources</label>
                <input type="number" id="recruiting-months" name="recruiting-months" min="1" max="12" step="1" onchange="updateCost('recruiting')" disabled>
                <input type="date" id="recruiting-startdate" name="recruiting-startdate" class="hidden-date">
                <input type="date" id="recruiting-expireddate" name="recruiting-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="crm" name="crm" onclick="toggleToolkit('crm')">
                <label>Customer Relationship Management</label>
                <input type="number" id="crm-months" name="crm-months" min="1" max="12" step="1" onchange="updateCost('crm')" disabled>
                <input type="date" id="crm-startdate" name="crm-startdate" class="hidden-date">
                <input type="date" id="crm-expireddate" name="crm-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="cybersecurity" name="cybersecurity" onclick="toggleToolkit('cybersecurity')">
                <label>Cybersecurity Console</label>
                <input type="number" id="cybersecurity-months" name="cybersecurity-months" min="1" max="12" step="1" onchange="updateCost('cybersecurity')" disabled>
                <input type="date" id="cybersecurity-startdate" name="cybersecurity-startdate" class="hidden-date">
                <input type="date" id="cybersecurity-expireddate" name="cybersecurity-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="project" name="project" onclick="toggleToolkit('project')">
                <label>Project Management</label>
                <input type="number" id="project-months" name="project-months" min="1" max="12" step="1" onchange="updateCost('project')" disabled>
                <input type="date" id="project-startdate" name="project-startdate" class="hidden-date">
                <input type="date" id="project-expireddate" name="project-expireddate" class="hidden-date">
            </div>

            
            <div>
                <input type="checkbox" id="strategy" name="strategy" onclick="toggleToolkit('strategy')">
                <label>Business Strategy</label>
                <input type="number" id="strategy-months" name="strategy-months" min="1" max="12" step="1" onchange="updateCost('strategy')" disabled>
                <input type="date" id="strategy-startdate" name="strategy-startdate" class="hidden-date">
                <input type="date" id="strategy-expireddate" name="strategy-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="risk" name="risk"  onclick="toggleToolkit('risk')">
                <label>Risk Management & Compliance</label>
                <input type="number" id="risk-months" name="risk-months" min="1" max="12" step="1" onchange="updateCost('risk')" disabled>
                <input type="date" id="risk-startdate" name="risk-startdate" class="hidden-date">
                <input type="date" id="risk-expireddate" name="risk-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="sales" name="sales" onclick="toggleToolkit('sales')">
                <label>Sales & Marketing</label>
                <input type="number" id="sales-months" name="sales-months" min="1" max="12" step="1" onchange="updateCost('sales')" disabled>
                <input type="date" id="sales-startdate" name="sales-startdate" class="hidden-date">
                <input type="date" id="sales-expireddate" name="sales-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="product" name="product" onclick="toggleToolkit('product')">
                <label>Product Development</label>
                <input type="number" id="product-months" name="product-months" min="1" max="12" step="1" onchange="updateCost('product')" disabled>
                <input type="date" id="product-startdate" name="product-startdate" class="hidden-date">
                <input type="date" id="product-expireddate" name="product-expireddate" class="hidden-date">
            </div>

            <div>
                <input type="checkbox" id="office" name="office" onclick="toggleToolkit('office')">
                <label>Office Management & Tasking</label>
                <input type="number" id="office-months" name="office-months" min="1" max="12" step="1" onchange="updateCost('office')" disabled>
                <input type="date" id="office-startdate" name="office-startdate" class="hidden-date">
                <input type="date" id="office-expireddate" name="office-expireddate" class="hidden-date">
            </div>


               <div style="display:none">
                <input type="number" class="amountprice" id="amountprice" step=0.01  tabindex="-1">
               </div>
                
            <div class="result" id="amountprice">Please select at least one toolkit.</div>

 
               

            <button type="submit">Submit & Pay</button>

            <!--<input type="submit" value="submit">-->


        </form>
    </div>


<script>
    const toolkitCostsNGN = {
        payroll: 3000,
        recruiting: 2500,
        crm: 4000,
        cybersecurity: 4000,
        project: 3500,
        strategy: 3500,
        risk: 3000,
        sales: 3000,
        product: 4000,
        office: 3500
    };

    let conversionRates = {
        NGN: { value: 1 }, // Default fallback rate for NGN
        USD: { value: 0.00063 }  // Default fallback rate for USD
    };
    const selectedToolkits = new Set();
    let selectedCurrency = 'NGN'; // Default currency

    function fetchCurrencyRates() {
        const apiKey = '';
        fetch(`https://api.currencyapi.com/v3/latest?apikey=${apiKey}&base=USD`)
            .then(response => response.json())
            .then(data => {
                if (data && data.data) {
                    conversionRates = data.data;
                    console.log('Conversion rates fetched successfully:', conversionRates);
                } else {
                    console.error('Invalid data received from CurrencyAPI:', data);
                }
                calculateCost(); // Recalculate cost with updated or fallback rates
            })
            .catch(error => {
                console.error('Error fetching currency rates:', error);
                calculateCost(); // Recalculate cost with fallback rates
            });
    }

    function toggleToolkit(toolkit) {
        const checkbox = document.getElementById(toolkit);
        const monthsInput = document.getElementById(`${toolkit}-months`);

        if (checkbox.checked) {
            selectedToolkits.add(toolkit);
            monthsInput.disabled = false;
            monthsInput.value = 1;
        } else {
            selectedToolkits.delete(toolkit);
            monthsInput.disabled = true;
            monthsInput.value = '';
        }

        updateDates(toolkit);
        calculateCost();
    }

    function updateDates(toolkit) {
        const monthsInput = document.getElementById(`${toolkit}-months`);
        const startDateInput = document.getElementById(`${toolkit}-startdate`);
        const expireDateInput = document.getElementById(`${toolkit}-expireddate`);

        const months = parseInt(monthsInput.value);
        if (months && months > 0) {
            const startDate = new Date();
            const expireDate = new Date();
            expireDate.setMonth(expireDate.getMonth() + months);

            startDateInput.valueAsDate = startDate;
            expireDateInput.valueAsDate = expireDate;
        }
    }

    function updateCost(toolkit) {
        updateDates(toolkit);
        calculateCost();
    }

    function calculateCost() {
        let totalCostUSD = 0;

        selectedToolkits.forEach(toolkit => {
            const months = parseInt(document.getElementById(`${toolkit}-months`).value);
            const costUSD = (toolkitCostsNGN[toolkit] / conversionRates.NGN.value) * months;
            totalCostUSD += costUSD;
        });

        const selectedCurrencyRate = conversionRates[selectedCurrency]?.value || 1;
        const convertedCost = totalCostUSD * selectedCurrencyRate;

        document.querySelector('.result').textContent = `Total Cost: ${convertedCost.toFixed(2)} ${selectedCurrency}`;

        const amountPrice = document.getElementById('amountprice');
        amountPrice.value = convertedCost.toFixed(2);
    }

    function updateCurrency() {
        selectedCurrency = document.getElementById('currency-selector').value;
        calculateCost();
    }

    function submitForm() {
        if (selectedToolkits.size === 0) {
            document.querySelector('.result').textContent = 'Please select at least one toolkit.';
            return;
        }
        payEscrow();
    }

    document.addEventListener('DOMContentLoaded', fetchCurrencyRates);
</script>

<script>
    function payEscrow() {
        let currency = document.getElementById("currency-selector").value;
        let amount = document.getElementById("amountprice").value;
        const paymentOptions = "card, banktransfer, ussd";
        const metaSource = "docs-inline-test";
        const customerEmails = document.getElementById("buyeremail").value;
        const customerName = document.getElementById("customername").value;
        const title = document.getElementById("companyname").value;
        const descriptions = document.getElementById("purchaseDescription").value;
        const logo = "https://growsocial.com.ng/black_white_grow_logo__1_-removebg-preview.png";

        FlutterwaveCheckout({
            public_key: "",
            tx_ref: generateReference(),
            amount: amount,
            currency: currency,
            payment_options: paymentOptions,
            meta: {
                source: metaSource,
            },
            customer: {
                email: customerEmails,
                name: customerName,
            },
            customizations: {
                title: title,
                description: descriptions,
                logo: logo,
            },
            callback: function (response) {
                console.log("Full response object:", response);

                if (response.status === "successful") {
                    console.log("Payment successful");
                    sendPayEscrowFormData(currency);
                } else {
                    console.log("Payment failed with response:", response);
                }
            },
        });
    }

    function generateReference() {
        return 'tx_' + Math.random().toString(36).substr(2, 9);
    }

    function sendPayEscrowFormData(currency) {
        const formData = new FormData(document.getElementById('subscription-form'));
        const data = {};

        // Convert formData to a regular object
        formData.forEach((value, key) => {
            data[key] = value;
        });

        data['currency'] = currency;

        // Send data to processtools.php
        const xhr1 = new XMLHttpRequest();
        xhr1.open('POST', 'processtools.php', true);
        xhr1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr1.onreadystatechange = function () {
            if (xhr1.readyState === 4 && xhr1.status === 200) {
                console.log('Processtool response:', xhr1.responseText);
            } else if (xhr1.readyState === 4) {
                console.error('Error posting to processtool:', xhr1.statusText);
            }
        };
        xhr1.send(Object.keys(data).map(key => `${encodeURIComponent(key)}=${encodeURIComponent(data[key])}`).join('&'));

        // Send data to recordpayment.php
        const xhr2 = new XMLHttpRequest();
        xhr2.open('POST', 'recordpayment.php', true);
        xhr2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr2.onreadystatechange = function () {
            if (xhr2.readyState === 4 && xhr2.status === 200) {
                console.log('Recordpayment response:', xhr2.responseText);
            } else if (xhr2.readyState === 4) {
                console.error('Error posting to recordpayment:', xhr2.statusText);
            }
        };
        xhr2.send(
            `customername=${encodeURIComponent(document.getElementById('customername').value)}&` +
            `companyid=${encodeURIComponent(document.getElementById('companyid').value)}&` +
            `customeremail=${encodeURIComponent(document.getElementById('buyeremail').value)}&` +
            `amount=${encodeURIComponent(document.getElementById('amountprice').value)}&` +
            `currency=${encodeURIComponent(currency)}`
        );
    }
</script>

</body>
</html>