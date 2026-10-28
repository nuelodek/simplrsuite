<?php
// Webhook setup for Simplr
$webhookUrl = "https://your-simplr-webhook-endpoint.com";
$webhookUsername = "";
$webhookPassword = "";

// Webhook payload
$webhookData = json_encode([
    "event_type" => "transaction_authorized",
    "card_token" => $cardToken,
    "transaction_amount" => "10",
    "merchant_id" => "123456890",
    "network" => "VISA"
]);

// Set up cURL for webhook
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_USERPWD, "$webhookUsername:$webhookPassword");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $webhookData);

// Send webhook
$webhookResponse = curl_exec($ch);
curl_close($ch);

echo "Webhook Response: ";
print_r(json_decode($webhookResponse, true));
?>
