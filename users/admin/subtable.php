<?php
// Include database connection
include '../../sql_queries/db.php';

// Fetch subscription data
$sql = "SELECT * FROM toolkit_subscriptions";
$result = $conn->query($sql);

// HTML for the subscription table
$table_html = '<table class="nice-table">
    <thead>
        <tr>
            <th>Tool</th>
            <th>Months</th>
            <th>Expiry Date</th>
            <th>Days Until Expiry</th>
            <th>Renew</th>
        </tr>
    </thead>
    <tbody>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Tools array with details
        $tools = [
            ['name' => 'Payroll', 'months' => $row['payroll_months'], 'expiry_date' => $row['payroll_expireddate']],
            ['name' => 'Recruiting', 'months' => $row['recruiting_months'], 'expiry_date' => $row['recruiting_expireddate']],
            ['name' => 'CRM', 'months' => $row['crm_months'], 'expiry_date' => $row['crm_expireddate']],
            ['name' => 'Cybersecurity', 'months' => $row['cybersecurity_months'], 'expiry_date' => $row['cybersecurity_expireddate']],
            ['name' => 'Project', 'months' => $row['project_months'], 'expiry_date' => $row['project_expireddate']],
            ['name' => 'Strategy', 'months' => $row['strategy_months'], 'expiry_date' => $row['strategy_expireddate']],
            ['name' => 'Risk', 'months' => $row['risk_months'], 'expiry_date' => $row['risk_expireddate']],
            ['name' => 'Sales', 'months' => $row['sales_months'], 'expiry_date' => $row['sales_expireddate']],
            ['name' => 'Product', 'months' => $row['product_months'], 'expiry_date' => $row['product_expireddate']],
            ['name' => 'Office', 'months' => $row['office_months'], 'expiry_date' => $row['office_expireddate']],
        ];

        // Build table rows for each tool in the subscription
        foreach ($tools as $tool) {
            $table_html .= '<tr>';
            // Tool name column
            $table_html .= '<td>' . $tool['name'] . '</td>';
            // Months column
            $table_html .= '<td>' . $tool['months'] . ' months</td>';
            // Expiry date column
            $table_html .= '<td>' . $tool['expiry_date'] . '</td>';
            // Calculate days until expiry
            $days_until_expiry = '-';
            if (!empty($tool['expiry_date'])) {
                $expiry_date = strtotime($tool['expiry_date']);
                $current_date = time();
                $days_until_expiry = floor(($expiry_date - $current_date) / (60 * 60 * 24));
            }
            // Days until expiry column
            $table_html .= '<td>' . $days_until_expiry . ' days</td>';
            // Renew button column
            $table_html .= '<td><button class="nice-table-button" onclick="renewSubscription(' . $row['id'] . ', \'' . strtolower($tool['name']) . '\')">Renew ' . $tool['name'] . '</button></td>';
            $table_html .= '</tr>';
        }
    }
} else {
    $table_html .= '<tr><td colspan="5">No subscriptions found.</td></tr>'; // Adjust colspan based on the number of columns
}

$table_html .= '</tbody></table>';

echo $table_html;

$conn->close();

?>

<script>
    function renewSubscription(subscriptionId, tool) {
        // Implement renew subscription functionality here, e.g., redirect to a renew page based on the tool
        console.log('Renew subscription clicked for ID: ' + subscriptionId + ' for ' + tool);
        // Example: Redirect to renew.php with subscription ID and tool name
        window.location.href = 'renewsubscription.php?id=' + subscriptionId + '&tool=' + tool;
    }
</script>
