<?php
// Your secret (replace with your actual secret)
$secret = 'emmy';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get the payload and the signature from GitHub's request
    $payload = file_get_contents('php://input');
    if ($payload === false) {
        throw new Exception('Failed to read input');
    }

    if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
        throw new Exception('No signature provided');
    }
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];

    // Verify the payload with the secret
    $hash = 'sha1=' . hash_hmac('sha1', $payload, $secret);

    if (!hash_equals($hash, $signature)) {
        throw new Exception('Invalid signature');
    }

    // The payload is verified, proceed with processing
    $data = json_decode($payload, true);
    if ($data === null) {
        throw new Exception('Failed to parse JSON payload');
    }

    // Example: log the received data
    if (file_put_contents('git_payload_log.txt', print_r($data, true), FILE_APPEND) === false) {
        throw new Exception('Failed to write to log file');
    }

    // Handle specific events here
    if (isset($data['action']) && $data['action'] == 'purchased') {
        // Do something with the marketplace event
        // For example:
        // process_purchase($data);
    }

    // Respond to GitHub
    http_response_code(200);
    echo 'Payload received and verified.';
} catch (Exception $e) {
    // Log the error
    error_log('GitHub Webhook Error: ' . $e->getMessage());

    // Respond with an error
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
?>