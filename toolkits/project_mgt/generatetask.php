<?php
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$project_id = $_POST['project_id'];
	
	// Get project details
	$project_query = "SELECT projectName, projectDescription FROM projects WHERE id = ?";
	$project_stmt = $conn->prepare($project_query);
	$project_stmt->bind_param('i', $project_id);
	$project_stmt->execute();
	$project_result = $project_stmt->get_result();
	$project_data = $project_result->fetch_assoc();
	
	$prompt = "Based on the following project: " . $project_data['projectName'] . " - " . $project_data['projectDescription'] . ", generate a new task title and description for the ongoing project.";
	
	$data = [
		'messages' => [
			[
				'role' => 'user',
				'content' => $prompt
			]
		],
		'system_prompt' => '',
		'temperature' => 0.7,
		'top_k' => 5,
		'top_p' => 0.9,
		'max_tokens' => 256,
		'web_access' => null
	];

	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://chatgpt-42.p.rapidapi.com/conversationgpt4-2",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-rapidapi-host: chatgpt-42.p.rapidapi.com",
			"x-rapidapi-key: 18bedc1cebmsh3b384795340dd86p125cc0jsn03c7fa812fea"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	$task_title = '';
	$task_description = '';

	if (!$err) {
		$response_data = json_decode($response, true);
		
		if ($response_data !== null && isset($response_data['result'])) {
			$generated_task = $response_data['result'];
			
			// Split the generated task into title and description
			$task_parts = explode("\n", $generated_task, 2);
			
			// Remove any instance of "Task Title:" and "Description:"
			$task_title = trim(preg_replace('/^(Task Title:|Title:)\s*/i', '', $task_parts[0]));
			$task_description = isset($task_parts[1]) ? trim(preg_replace('/^(Description:)\s*/i', '', $task_parts[1])) : '';
		}
	}

	// Return JSON response
	header('Content-Type: application/json');
	echo json_encode([
		'task_title' => $task_title,
		'task_description' => $task_description
	]);
	exit;
}
