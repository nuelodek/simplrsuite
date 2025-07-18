<?php
// Define the tool ID (you might get this from a form submission or database query)
$tool_id = range(1, 10); // Array of numbers from 1 to 10

// Array of tool information
$tools = [
    "1" => ["name" => "Payroll & Accounting", "file" => "payroll_accounting.php"],
    "2" => ["name" => "Recruiting & Human Resources", "file" => "recruiting_hr.php"],
    "3" => ["name" => "Customer Relationship Management", "file" => "crm.php"],
    "4" => ["name" => "Cybersecurity Console", "file" => "cybersecurity.php"],
    "5" => ["name" => "Project Management", "file" => "project_management.php"],
    "6" => ["name" => "Business Strategy", "file" => "business_strategy.php"],
    "7" => ["name" => "Risk Management & Compliance", "file" => "risk_compliance.php"],
    "8" => ["name" => "Sales & Marketing", "file" => "sales_marketing.php"],
    "9" => ["name" => "Product Development", "file" => "product_development.php"],
    "10" => ["name" => "Office Management & Tasking", "file" => "office_management.php"]
];

// Read the contents of the template file
$template_content = file_get_contents('recruiting.php');

// Create files for each tool
foreach ($tools as $id => $tool) {
    $filename = $tool['file'];
    
    // Append the template content
    $content .= "\n" . $template_content . "\n";
    

    file_put_contents($filename, $content);
}

echo "All tool files have been created successfully.";

// Check if the tool ID exists in our array
if ($tool_id !== null && array_key_exists($tool_id, $tools)) {
    // Include the appropriate file
    include $tools[$tool_id]['file'];
} else {
    // Default message for unknown tool
    echo "You haven't been assigned a tool, check back later";
}
?>
