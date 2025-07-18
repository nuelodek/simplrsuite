<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DuploPay API Configuration
$base_url = 'https://api.duplopg.com/api/v2';
$secret_key = 'sk_test_w3dgzflef8b7prtnz6rbwob3x9yqg0lq86xlofo';
$public_key = 'pk_test_sc8f7xgps3k5azfsmaybfjwbm1izpk4o1p97hr';

// Function to make API requests
function makeApiRequest($endpoint, $method = 'GET', $data = null) {
    global $base_url, $secret_key;

    $url = $base_url . $endpoint;
    $headers = [
        'Authorization: Bearer ' . $secret_key,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    
    // Check if a request was performed
    if ($result !== null) {
        echo "A request was performed to: " . $url . "\n";
    }

    return $result;
}

// Function to initialize a transaction
function initializeTransaction($email, $amount, $currency = 'NGN', $reference = null, $callback_url = null, $webhook_url = null) {
    $data = [
        'email' => $email,
        'amount' => (string)$amount,
        'currency' => $currency
    ];
    
    if ($reference !== null) {
        $data['reference'] = $reference;
    }
    
    if ($callback_url !== null) {
        $data['callback_url'] = $callback_url;
    }
    
    if ($webhook_url !== null) {
        $data['webhook_url'] = $webhook_url;
    }
    
    return makeApiRequest('/transaction/initialize', 'POST', $data);
}

// Usage example
$initializeTransactionResult = initializeTransaction(
    'nuelodekunle@gmail.com',
    1000,
    'NGN',
    null,
    'http://localhost/simplr24/users/admin/simplrduplo/callback.php',
    'https://yourwebsite.com/webhook'
);

print_r($initializeTransactionResult);

?>
