<?php
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // 
    $client_id = 'Ov23lisGRPsNohh2HiYi';
    $client_secret = '66b440e6a9f9294fb7c95d27b83bc9f771b4304a';
    $redirect_uri = 'https://simplr.com.ng/gitcallback.php';

    // Exchange the code for an access token
    $token_url = "https://github.com/login/oauth/access_token";
    $post_fields = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result, true);
    $accessToken = $response['access_token'];

    // You can now use $accessToken to access the GitHub API
    // Store this token securely if you need to use it later
    // For now, we'll just display it
    echo "Access Token: " . $accessToken;
}