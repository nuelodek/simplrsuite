<?php
include "../../sql_queries/db.php";
session_start();

header("Content-Type: application/json");

// generate a unique ID for the contact
function generateUniqueContactID() {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = 'contact_';
    for ($i = 0; $i < 10; $i++) {
        $randomString .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unique_id = generateUniqueContactID();
    $contact_name = $_POST['contactName'];
    $email = $_POST['contactEmail'];
    $phone = $_POST['contactPhone'];
    $company_id = $_SESSION['company_id'];

    $stmt = $conn->prepare("INSERT INTO contacts (contact_name, contact_email, contact_phone, unique_id, company_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $contact_name, $email, $phone, $unique_id, $company_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $conn->close();
        echo json_encode("Contact added successfully");
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode("Error adding contact");
    }
}
?>