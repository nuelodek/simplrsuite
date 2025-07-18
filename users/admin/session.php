<?php


session_start();

// header("");

if (!isset($_SESSION['user_id'])) {
    header("Location: unauthorized.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$company_name = $_SESSION['company_name'];
$industry = $_SESSION['industry'];
$company_id = $_SESSION['company_id'];
$status = $_SESSION['status'];
$companyId = $_SESSION['company_id'];


// echo json_encode(["user_id" => $user_id]);
?>
