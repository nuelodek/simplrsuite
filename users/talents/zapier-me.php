    <?php
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Start output buffering
    ob_start();

    // Get the API key from the request parameters
    $apiKey = $_GET['api_key'] ?? $_POST['api_key'] ?? ''; // Check both GET and POST

    // Define valid API keys
    $validApiKeys = ['api_key']; // Replace with your actual valid API keys in an array

    // Check if the API key is valid
    if (in_array($apiKey, $validApiKeys)) {
        // If API key is valid, return a success response
        $response = [
            'status' => 'success',
            'message' => 'API key is valid',
            'data' => [
                'user' => 'John Doe', // Example user data; replace with actual data
                'email' => 'john.doe@example.com'
            ]
        ];
    } else {
        // If API key is invalid, return an error response
        http_response_code(401); // Unauthorized
        $response = [
            'status' => 'error',
            'message' => 'Invalid API key'
        ];
    }

    // Set headers
    header('Content-Type: application/json');

    // Clear the output buffer and turn off output buffering
    ob_end_clean();

    // Output the response
    echo json_encode($response);
    ?>
