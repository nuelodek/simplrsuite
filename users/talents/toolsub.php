<?php

session_start();

include 'db.php';

$sql = "SELECT * FROM toolkit_subscriptions where company_id='$companyId'";
$result = $conn->query($sql);

// Declare variables to store the fetched data
$subscriptions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subscriptions[] = [
            'id' => $row['id'],
            'email' => $row['email'],
            'payroll_months' => $row['payroll_months'],
            'payroll_startdate' => $row['payroll_startdate'],
            'payroll_expireddate' => $row['payroll_expireddate'],
            'recruiting_months' => $row['recruiting_months'],
            'recruiting_startdate' => $row['recruiting_startdate'],
            'recruiting_expireddate' => $row['recruiting_expireddate'],
            'crm_months' => $row['crm_months'],
            'crm_startdate' => $row['crm_startdate'],
            'crm_expireddate' => $row['crm_expireddate'],
            'cybersecurity_months' => $row['cybersecurity_months'],
            'cybersecurity_startdate' => $row['cybersecurity_startdate'],
            'cybersecurity_expireddate' => $row['cybersecurity_expireddate'],
            'project_months' => $row['project_months'],
            'project_startdate' => $row['project_startdate'],
            'project_expireddate' => $row['project_expireddate'],
            'strategy_months' => $row['strategy_months'],
            'strategy_startdate' => $row['strategy_startdate'],
            'strategy_expireddate' => $row['strategy_expireddate'],
            'risk_months' => $row['risk_months'],
            'risk_startdate' => $row['risk_startdate'],
            'risk_expireddate' => $row['risk_expireddate'],
            'sales_months' => $row['sales_months'],
            'sales_startdate' => $row['sales_startdate'],
            'sales_expireddate' => $row['sales_expireddate'],
            'product_months' => $row['product_months'],
            'product_startdate' => $row['product_startdate'],
            'product_expireddate' => $row['product_expireddate'],
            'office_months' => $row['office_months'],
            'office_startdate' => $row['office_startdate'],
            'office_expireddate' => $row['office_expireddate'],
            'created_at' => $row['created_at']
        ];
    }
} else {
    echo "0";
}

$conn->close();

// Variables are now accessible outside the while loop
foreach ($subscriptions as $subscription) {
    echo "ID: " . $subscription['id'] . "<br>";
    echo "Email: " . $subscription['email'] . "<br>";
    echo "Payroll Months: " . $subscription['payroll_months'] . "<br>";
    echo "Payroll Start Date: " . $subscription['payroll_startdate'] . "<br>";
    echo "Payroll Expired Date: " . $subscription['payroll_expireddate'] . "<br>";
    // ... (output other fields similarly)
    echo "Created At: " . $subscription['created_at'] . "<br><br>";
}
?>
