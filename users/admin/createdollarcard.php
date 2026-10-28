<?php

// transfer tp environment variables
$apiBaseUrl = "https://sandbox-api.marqeta.com/v3";
$apiUsername = "";
$apiPassword = "";

// Create a user
$createUserUrl = $apiBaseUrl . "/users";
$userData = json_encode([
    "first_name" => "Marqeta",
    "last_name"  => "User"
]);

$ch = curl_init($createUserUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_USERPWD, "$apiUsername:$apiPassword");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $userData);

$response = curl_exec($ch);
curl_close($ch);

$userResponse = json_decode($response, true);
$userToken = $userResponse['token'];

echo "User Token: " . $userToken;

// Create a card product
$createCardProductUrl = $apiBaseUrl . "/cardproducts";
$cardProductData = json_encode([
    "start_date" => "2023-01-01",
    "name" => "Example Card Product",
    "config" => [
        "fulfillment" => [
            "payment_instrument" => "VIRTUAL_PAN"
        ],
        "poi" => [
            "ecommerce" => true
        ],
        "card_life_cycle" => [
            "activate_upon_issue" => true
        ]
    ]
]);

$ch = curl_init($createCardProductUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_USERPWD, "$apiUsername:$apiPassword");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $cardProductData);

$response = curl_exec($ch);
curl_close($ch);

$cardProductResponse = json_decode($response, true);
$cardProductToken = $cardProductResponse['token'];

echo "Card Product Token: " . $cardProductToken;

// Create a card for the user
$createCardUrl = $apiBaseUrl . "/cards";
$cardData = json_encode([
    "user_token" => $userToken,
    "card_product_token" => $cardProductToken
]);

$ch = curl_init($createCardUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_USERPWD, "$apiUsername:$apiPassword");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $cardData);

$response = curl_exec($ch);
curl_close($ch);

$cardResponse = json_decode($response, true);
$cardToken = $cardResponse['token'];

echo "Card Token: " . $cardToken;

// Simulate a transaction
$simulateTransactionUrl = $apiBaseUrl . "/simulations/cardtransactions/authorization";
$transactionData = json_encode([
    "amount" => "10",
    "card_token" => $cardToken,
    "card_acceptor" => [
        "mid" => "123456890"
    ],
    "network" => "VISA"
]);

$ch = curl_init($simulateTransactionUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_USERPWD, "$apiUsername:$apiPassword");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $transactionData);

$response = curl_exec($ch);
curl_close($ch);

$transactionResponse = json_decode($response, true);

echo "Transaction Response: ";
print_r($transactionResponse);
