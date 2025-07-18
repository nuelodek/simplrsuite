<?php
error_reporting(E_ALL);
set_time_limit(0);
ini_set('memory_limit', '-1');
session_start();

// Use an absolute path for the upload directory
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/employees/work/uploads/';

// Check if both unique IDs are set
if (isset($_GET['unique_id1']) && isset($_GET['unique_id2'])) {
    $unique_id1 = $_GET['unique_id1'];
    $unique_id2 = $_GET['unique_id2'];

    // Create a single chat file based on both unique_id1 (recruiter) and unique_id2 (talent)
    $chatFile = 'chat_' . $unique_id1 . '_' . $unique_id2 . '.txt';

    // Create the chat file if it doesn't exist
    if (!file_exists($chatFile)) {
        touch($chatFile);
        chmod($chatFile, 0666); // Set file permissions
    }

    // Ensure the upload directory has the correct permissions
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            die('Failed to create upload directory: ' . $uploadDir);
        }
        chmod($uploadDir, 0777); // Set permissions for the directory
    }

    // Handle new message submission (message or file)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['message']) || isset($_FILES['file']))) {
        $message = isset($_POST['message']) ? $_POST['message'] : '';  // Default to empty if no message provided
        $userId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['talent_id']) ? $_SESSION['talent_id'] : null);

        // Determine if the sender is a recruiter or talent
        $userType = isset($_SESSION['employee_id']) ? 'recruiter' : (isset($_SESSION['talent_id']) ? 'talent' : 'unknown');

        if (isset($userId)) {
            // Format the message with metadata (even if the message is empty)
            $formattedMessage = '[' . date('Y-m-d H:i:s') . '] ' . $userType . ': ' . $userId . ': ' . $message . "\n";

            // Check if a file is present
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $fileDestination = $uploadDir . basename($file['name']);

                // Move the uploaded file to the desired location
                if (move_uploaded_file($file['tmp_name'], $fileDestination)) {
                    // Append the file link to the message
                    $formattedMessage .= '[' . date('Y-m-d H:i:s') . '] ' . $userType . ': ' . $userId . ': [File: ' . $file['name'] . ']' . "\n";
                } else {
                    // Handle file upload error
                    echo 'Error: Failed to move uploaded file.';
                }
            }

            // Write the message (and any file link) to the chat file
            file_put_contents($chatFile, $formattedMessage, FILE_APPEND);
        }
    }

    // Retrieve chat history
    $chatHistory = [];
    if (file_exists($chatFile)) {
        $chatHistory = file($chatFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    $formattedMessages = [];

    foreach ($chatHistory as $message) {
        // Parse message to extract user type and content
        if (preg_match('/\[(.+)\] (\w+): (\d+): (.+)/', $message, $matches)) {
            $timestamp = $matches[1];
            $userType = $matches[2];
            $userId = $matches[3];
            $messageContent = $matches[4];

            $formattedMessage = [
                'timestamp' => $timestamp,
                'user_type' => $userType,
                'user_id' => $userId,
                'text' => $messageContent,
                'file' => null
            ];

            // Check if the message contains a file
            if (preg_match('/\[File: (.+)\]/', $messageContent, $fileMatches)) {
                $filename = $fileMatches[1];
                $formattedMessage['file'] = [
                    'name' => $filename,
                    'type' => mime_content_type($uploadDir . $filename)
                ];
            }

            $formattedMessages[] = $formattedMessage;
        }
    }

    // Sort the messages based on timestamp
    usort($formattedMessages, function ($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });

    // Output the formattedMessages array as JSON
    header('Content-Type: application/json');
    echo json_encode($formattedMessages);

    exit();
}
?>