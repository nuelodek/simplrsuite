<?php
include '../../sql_queries/db.php';
session_start();

header("Content-Type: application/json");

// generate unique lead id
function generateRandomId() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = 'lead_';
    for ($i = 0; $i < 10; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$lead_id = generateRandomId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_SESSION['company_id'];
    $leadName = $_POST['leadName'];
    $email = $_POST['leadEmail'];
    $phone = $_POST['leadPhone'];
    $leadSource = $_POST['leadSource'];

    // Default values for additional fields
    $socialMediaPlatform = null;
    $websiteURL = null;
    $emailCampaign = null;

    // Assign additional fields based on lead source
    if ($leadSource === "Social-Media") {
        $socialMediaPlatform = $_POST['socialMediaPlatform'] ?? null;
    } elseif ($leadSource === "Website") {
        $websiteURL = $_POST['websiteURL'] ?? null;
    } elseif ($leadSource === "Email") {
        $emailCampaign = $_POST['emailCampaign'] ?? null;
    }

    // Prepare SQL statement with placeholders
    $stmt = $conn->prepare("INSERT INTO leads 
        (lead_name, lead_email, lead_phone, lead_source, social_media_platform, website_url, email_campaign, unique_id, company_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $stmt->bind_param(
        "ssssssssi",
        $leadName,
        $email,
        $phone,
        $leadSource,
        $socialMediaPlatform,
        $websiteURL,
        $emailCampaign,
        $lead_id,
        $company_id
    );

    // Execute the statement and check for success
    if ($stmt->execute()) {
        echo json_encode(["message" => "Lead added successfully"]);
    } else {
        echo json_encode(["error" => "Error adding lead"]);
    }

    $stmt->close();
    $conn->close();
}
?>
