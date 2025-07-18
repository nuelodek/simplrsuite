<?php



// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Check if companyId is set in the session
if (!isset($_SESSION['company_id'])) {
  // Redirect to login.html if companyId is not found
  header("Location: login.html");
  exit();
}

// print_r($_SESSION);
$userId = $_SESSION['user_id'];
$email = $_SESSION['email'];
$companyName = $_SESSION['company_name'];
$industry = $_SESSION['industry'];
$companyId = $_SESSION['company_id'];
$status = $_SESSION['status'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin: Simplr for Business </title>
  <link rel="icon" type="image/png" href="/simple-social-svgrepo-com.png">

  <script src="https://kit.fontawesome.com/a75a719a54.js" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>

  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap');

    .styled-form {
      max-width: 600px;
      margin: 0;

    }

    .styled-form .form-group {
      margin-bottom: 20px;
    }

    .styled-form label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      font-size: 13px;
      color: #555;
      /* Lighter color for a more modern feel */
    }

    .styled-form input[type="text"],
    input[type="date"],
    .styled-form textarea,
    .styled-form select {
      width: 100%;
      padding: 10px;
      /* Slightly more padding for a better feel */
      border: 1px solid #ccc;
      border-radius: 3px;
      font-size: 13px;
      background-color: #fff;
      /* Make the input background white */
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .styled-form input[type="text"]:focus,
    input[type="date"]:focus,
    .styled-form textarea:focus,
    .styled-form select:focus {
      border-color: #007bff;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
      /* Add focus effect for inputs */
      outline: none;
    }

    .styled-form textarea {
      resize: vertical;
      min-height: 150px;
      /* More space for the textarea */
    }

    .styled-form .btn-primary {
      display: inline-block;
      padding: 12px 24px;
      /* More padding for better button feel */
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .styled-form .btn-primary:hover {
      background-color: #0056b3;
      transform: translateY(-2px);
      /* Subtle hover effect */
    }

    .styled-form .btn-primary:active {
      background-color: #003f8a;
      /* Darker color on click */
      transform: translateY(0);
      /* Reset button position on click */
    }


    .widgets-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 5px;
      max-height: 407px;
      margin-right: 20px;
    }

    .widget {
      background-color: #f9f9f9;
      border-radius: 5px;
      padding: 15px 15px 0px 15px;
      display: flex;
      align-items: center;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .widget-content {
      display: flex;
      justify-content: space-between;
      width: 100%;
      margin-top: -40px;
      /*margin-bottom: 10px;*/
      align-items: center;
    }

    .widget-text {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      margin-left: 20px;

    }


    .widget-number {
      font-size: 20px;
      font-weight: bold;

    }

    .widget-title {
      font-weight: bold;
    }

    .widget-icon {
      width: 30px;
      height: 30px;
      margin-left: 10px;
      margin-top: 10px;
    }

    .widget:nth-child(1) .widget-number {
      /* Subscription */
      color: #4CAF50;
    }


    .widget:nth-child(2) .widget-number {
      /* Active Users */
      color: #F44336;
    }

    .widget:nth-child(3) .widget-number {
      /* Inactive Users */
      color: #2196F3;
    }

    .widget:nth-child(4) .widget-number {
      /* Task Overview */
      color: #FFC107;
    }

    .widget:nth-child(5) .widget-number {
      /* Notification */
      color: #009688;
    }

    .widget:nth-child(6) .widget-number {
      /* Highlights */
      color: #9C27B0;
    }

    .widget:nth-child(7) .widget-number {
      /* Goal Tracker */
      color: #3F51B5;
    }

    .widget:nth-child(8) .widget-number {
      /* AI-Powered Insights */
      color: #673AB7;
    }

    .widget:nth-child(9) .widget-number {
      /* Communication Hub */
      color: #795548;
    }

    .widget-icon1 {
      /* Subscription */
      background-color: rgba(76, 175, 80, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon1:hover {
      background-color: rgba(76, 175, 80, 0.5);
      transform: scale(1.05);
    }

    .widget-icon2 {
      /* Email */
      background-color: rgba(33, 150, 243, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon2:hover {
      background-color: rgba(33, 150, 243, 0.5);
      transform: scale(1.05);
    }

    .widget-icon3 {
      /* Notification */
      background-color: rgba(255, 152, 0, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon3:hover {
      background-color: rgba(255, 152, 0, 0.5);
      transform: scale(1.05);
    }

    .widget-icon4 {
      /* Settings */
      background-color: rgba(158, 158, 158, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon4:hover {
      background-color: rgba(158, 158, 158, 0.5);
      transform: scale(1.05);
    }

    .widget-icon5 {
      /* Profile */
      background-color: rgba(233, 30, 99, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon5:hover {
      background-color: rgba(233, 30, 99, 0.5);
      transform: scale(1.05);
    }

    .widget-icon6 {
      /* Messages */
      background-color: rgba(103, 58, 183, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon6:hover {
      background-color: rgba(103, 58, 183, 0.5);
      transform: scale(1.05);
    }

    .widget-icon7 {
      /* Calendar */
      background-color: rgba(0, 150, 136, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon7:hover {
      background-color: rgba(0, 150, 136, 0.5);
      transform: scale(1.05);
    }

    .widget-icon8 {
      /* Search */
      background-color: rgba(255, 87, 34, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon8:hover {
      background-color: rgba(255, 87, 34, 0.5);
      transform: scale(1.05);
    }

    .widget-icon9 {
      /* Help */
      background-color: rgba(63, 81, 181, 0.3);
      border-radius: 50%;
      padding: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .widget-icon9:hover {
      background-color: rgba(63, 81, 181, 0.5);
      transform: scale(1.05);
    }

    .onboarding-container {
      margin-bottom: 20px;
      height: calc(100% - 40px);
      min-height: 270px;
    }

    .onboarding-button {
      background-color: #4CAF50;
      color: white;
      border-radius: 10px;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
      margin-bottom: 10px;
    }


    .progress-bar {
      width: 100%;
      background-color: black;
      height: 25px;
      border-radius: 10px;
      position: relative;
      overflow: hidden;
    }

    .progress {
      width: 75%;
      height: 100%;
      background-color: darkgreen;
      border-radius: 10px;
      position: relative;
    }

    .progress-number {
      position: absolute;
      top: 50%;
      font-size: 12px;
      left: 10px;
      transform: translateY(-50%);
      color: #fff;
      white-space: nowrap;
      z-index: 1;
    }

    .customize-widget-link {

      text-decoration: none;
      color: #4CAF50;
      font-weight: bold;
    }


    .imageflag {
      border-radius: 5px;
      width: 10px;
      height: 10px;
    }


    .kpi-list {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
    }

    .kpi-item {
      width: 30%;
      margin-bottom: 20px;
      padding: 15px;
      background-color: #f9f9f9;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .kpi-item h4 {
      margin-top: 0;
      margin-bottom: 10px;
      font-size: 16px;
      color: #333;
    }

    .kpi-value {
      font-size: 24px;
      font-weight: bold;
      color: #007bff;
      margin-bottom: 5px;
    }

    .kpi-trend {
      font-size: 14px;
    }

    .kpi-trend.positive {
      color: #28a745;
    }

    .kpi-trend.negative {
      color: #dc3545;
    }

    #activityLogsModal .modal-body {
      padding: 20px;
    }

    #activityLogsModal .log-filters {
      margin-bottom: 20px;
    }

    #activityLogsModal .log-filters select,
    #activityLogsModal .log-filters input,
    #activityLogsModal .log-filters button {
      margin-right: 10px;
      padding: 5px;
    }

    #activityLogsModal .log-list {
      max-height: 400px;
      overflow-y: auto;
    }

    #activityLogsModal table {
      width: 100%;
      border-collapse: collapse;
    }

    #activityLogsModal th,
    #activityLogsModal td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    #activityLogsModal th {
      background-color: #f2f2f2;
    }

    #activityLogsModal .pagination {
      margin-top: 20px;
      text-align: center;
    }

    #activityLogsModal .pagination button {
      margin: 0 10px;
    }

    #talentSuggestionsTable {
      width: 100%;
      border-collapse: collapse;
    }

    #talentSuggestionsTable th,
    #talentSuggestionsTable td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    #talentSuggestionsTable th {
      background-color: #f2f2f2;
      font-weight: bold;
    }

    #talentSuggestionsTable tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    #talentSuggestionsTable tr:hover {
      background-color: #f5f5f5;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 99;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
      scrollbar-width: none;
    }

    .modal::-webkit-scrollbar {
      display: none;
    }

    .modal-content {
      /* background-color: #ff0000; */
      margin: 5% auto;
      padding: 0;
      border: 1px solid #888;
      width: 80%;
      max-width: 800px;
      max-height: fit-content;
      border-radius: 10px;
      box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);

    }

    .modal-header {
      padding: 10px 16px;
      background-color: #f8f8f8;
      border-bottom: 1px solid #e7e7e7;
      border-radius: 10px 10px 0 0;
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      line-height: 1;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    .modal-body {
      padding: 20px;
      max-height: 400px;
      overflow-y: auto;
      scrollbar-width: none;
    }

    .modal-body::-webkit-scrollbar {
      display: none;
    }

    .alert-item {
      background-color: #f8f8f8;
      border-left: 4px solid #ff0000;
      margin-bottom: 10px;
      padding: 10px;
      border-radius: 4px;
    }

    .alert-item h3 {
      margin-top: 0;
      color: #333;
    }

    .alert-item p {
      margin-bottom: 5px;
      color: #666;
    }

    .alert-item .alert-date {
      font-size: 0.8em;
      color: #999;
    }

    .updates-container {
      padding: 20px;
    }

    .update-item {
      margin-bottom: 20px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e7e7e7;
    }

    .update-item:last-child {
      border-bottom: none;
    }

    .update-item h3 {
      margin-top: 0;
      color: #333;
    }

    .update-item p {
      color: #666;
    }

    .update-date {
      font-size: 0.9em;
      color: #999;
    }

    .tab {
      overflow: hidden;
      background-color: #ffffff;
      border-bottom: 1px solid #e7e7e7;
    }

    .tab button {
      background-color: #ffffff;
      float: left;
      border: none;
      outline: none;
      cursor: pointer;
      padding: 14px 16px;
      transition: 0.3s;
      font-size: 16px;
      border-bottom: 2px solid transparent;
    }

    .tab button:hover {
      background-color: #f1f1f1;
    }

    .tab button.active {
      border-bottom: 2px solid #4CAF50;
      font-weight: bold;
    }

    .tabcontent {
      background-color: #ffffff;
      display: none;
      padding: 20px;
      height: 500px;
      max-height: fit-content;
      overflow-y: auto;
      /* opacity: .3; */
      scrollbar-width: none;
    }

    .tabcontent::-webkit-scrollbar{
      display: none;
    }

    .wallet-item {
      background-color: #f9f9f9;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
    }

    .wallet-item h4 {
      margin-top: 0;
      color: #333;
    }

    .wallet-item p {
      margin: 5px 0;
    }

    .wallet-item .balance {
      font-weight: bold;
      font-size: 18px;
      color: #007bff;
    }

    .wallet-item .percentage {
      font-style: italic;
      color: #28a745;
    }

    .sectionq {
      margin-left: 240px;
    }

    .waivers {
      width: 24px;
      height: 24px;
      vertical-align: middle;
      margin-left: 5px;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 20px;
      background-color: #ffffff;
      border-bottom: 1px solid #ddd;
    }

    .logo {
      /*font-weight: 500;*/
      display: flex;
      align-items: center;
      color: #333;
      margin-top: 0px;
    }

    .search-bar {
      flex-grow: 1;
      margin: 0 30px;
      display: flex;
      align-items: center;
    }

    .search-bar input {
      width: 100%;
      max-width: 400px;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
    }

    .menu-icons {
      display: flex;
      gap: 25px;
      align-items: center;
    }

    .menu-icons a {
      color: #333;
      text-decoration: none;
      font-size: 14px;
      transition: color 0.3s ease;
    }

    .menu-icons a:hover {
      color: #007BFF;
    }

    .menu-icons img {
      width: 20px;

      height: 20px;
    }

    .profile-pic {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      object-fit: cover;
      margin-left: 15px;
    }

    html,
    body {
      font-family: "DM Sans", sans-serif;
      margin: 0;
      padding: 0;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    body {
      background-color: pink;
    }

    .main-content {
      background-color: #f5f7f8;
      flex-grow: 1;
      height: max-height;
      box-sizing: border-box;
      border: 1px solid #bdc3c7;
    }

    .sidebar {
      width: 200px;
      background-color: #f8f8f8;
      padding: 20px;
      height: 100%;
      position: fixed;
      overflow-x: hidden;
      transition: 0.3s;
      border: 1px solid #ccc;
    }

    .container {
      margin-left: 270px;
    }

    .logo {
      margin-top: 20px;
      background-color: none;
    }

    .create-content-btn {
      width: 100%;
      border-radius: 5px;
      padding: 10px;
      text-align: center;
      background-color: #6200ea;
      color: #FFFFFF;
      border: none;
      cursor: pointer;
      margin-bottom: 20px;
      /* text-align: left; */
      font-size: 16px;
    }

    .menu {
      list-style: none;
      padding: 0;
      font-size: 12px;
    }

    .menu li {
      padding: 10px 0;
      cursor: pointer;
    }

    .trial-upgrade {
      margin-top: 20px;
      margin-bottom: -20px;
    }

    .upgrade-btn {
      width: 100%;
      border: 1px solid black;
      background-color: transparent;
      padding: 5px;
      color: black;
      cursor: pointer;
      text-align: center;
      border-radius: 5px;
      font-size: 13px;
    }

    .user-info {
      margin-top: 50px;
      /* text-align: center; */
      display: flex;
      gap: 10px;
    }

    .avatar {
      width: 30px;
      height: 30px;
      border-radius: 5px;
      background-color: #6200ea;
      color: #FFFFFF;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      margin-bottom: 10px;
    }


    .task-input {
      display: flex;
      /* justify-content: center; */
      /* align-items: center; */
      margin-top: 10px;
    }

    .task-input input {
      padding: 20px;
      width: 60%;
      margin-right: 10px;
      border: 1px solid #CCC;
      border-radius: 4px;
    }


    .task-input {
      position: relative;
      width: 100%;
      max-width: 400px;
      /* Adjust this as needed */
    }

    .task-input input[type="text"] {
      width: 100%;
      padding-right: 80px;
      /* Adjust this value based on button width */
      box-sizing: border-box;
    }

    .task-input .create-btn {
      position: absolute;
      right: 20px;
      top: 10px;
      height: 65%;
      border: none;
      border-radius: 5px;
      /* background-color: #007BFF; Button color */
      color: white;
      padding: 10px 10px;
      /* Adjust padding for button size */
      cursor: pointer;
    }

    .quick-tasks {
      display: flex;
      /* justify-content: space-between; */
      margin-top: 20px;
      margin-bottom: 20px;
    }

    .quick-tasks div {
      margin-right: 10px;
    }

    .task {
      background-color: #FFF;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      min-width: 170px;
      max-width: fit-content;
      height: 90px;
      /* text-align: center; */
      cursor: pointer;
    }

    .recent-content h2 {
      margin-bottom: 10px;
    }

    .recent-watched {
      background-color: #FFF;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .createclass {
      font-weight: 900;
      font-size: 2rem;
    }

    h1 {
      padding-top: 40px;
      margin-bottom: 5px;
      /* Adjust this value as needed */
    }

    p.createclass {
      margin-top: 0;
    }

    .styesl {
      margin-bottom: 40px;
    }

    .task span {
      font-weight: bold;
    }

    .task p small {

      background-color: yellow;
      padding: 5px 5px;
      border-radius: 5px;
      font-size: 0.5em;
    }

    .sidebar small {

      color: black;
      font-weight: bold;
      padding: 5px 5px;
      margin-left: 20px;
      border-radius: 5px;
      font-size: 0.7em;
    }


    .menu li img {
      width: 24px;
    }



    .menu li {
      display: flex;
      align-items: center;
    }

    .menu li img {
      margin-right: 10px;
    }


    .create-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 20px;
      font-size: 16px;
      background-color: #ffffff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .create-btn img {
      width: 20px;
      margin-left: 10px;
      /* background: white; */
    }

    .mayuassite {
      background-color: #FFFFFF;
      width: 10%;
      display: inline-flex;
      padding: 5px;
      float: right;
      font-size: 10px;
      border-radius: 5px;
      gap: 5px;
      margin-top: 10px;
      justify-content: center;
      margin-right: 20px;
    }

    .mayuassite span {
      margin-top: 3px;

    }


    .arrowbutton {
      width: 10px;
      height: 10px;
      margin-left: 60px;
    }

    .recent-watched {
      background-color: #f9f9f9;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .recent-content {
      display: flex;
      flex-direction: column;
    }

    .content-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }

    .content-item:last-child {
      border-bottom: none;
    }

    .content-info {
      display: flex;
      flex-direction: column;
    }

    .content-info h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
    }

    .content-info p {
      margin: 5px 0;
      font-size: 14px;
      color: #666;
    }

    .rating {
      display: flex;
      align-items: center;
    }

    .rating i {
      color: #f39c12;
      margin-right: 2px;
    }

    .content-actions {
      display: flex;
      align-items: center;
    }

    .content-actions button {
      background-color: #4CAF50;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    .content-actions button:hover {
      background-color: #45a049;
    }

    .task p.notza small {
      font-size: 10px;
      color: #555;
      /* Slightly darker than the background */
      background-color: #f0f0f0;
      padding: 2px 5px;
      border-radius: 3px;
      display: inline-block;
    }

    li img {
      color: grey;
    }
  </style>
</head>

<body>

  <div class="main-content">
    <div class="content">
      <div class="sidebar">
        <div class="logo" style="width:100px;"></div>
        <div class="menulist" style="font-size: 12px;">

          <ul class="menu">

            <div style="display: flex; align-items: center; margin-left: 0px; margin-top: 20px; margin-bottom: 10px; color: black;">
              <img src="simple-social-svgrepo-com.png" style="width: 25px; height: 25px; background-color: none; border-radius: 5px;">
              <div style="display: flex; flex-direction: column; margin-left: 10px;">
                <span style="color: ; font-weight: bold;"> <?php echo $companyName; ?></span>
                <span style="color: ; font-size: 8px;"> Company ID: <?php echo $companyId; ?> </span>
              </div>
            </div>
            <li onclick="toggleLearn()"> <img src="https://www.svgrepo.com/show/501525/dashboard.svg" alt="Learn Icon">Dashboard <span><small style="background-color: #FFD700;">5 alerts</small></span></li>
            <li onclick="openEmployeeModal()"><img src="https://www.svgrepo.com/show/532824/folder-plus.svg" alt="Categories Icon"> Manage Company
              <!--<img src="https://www.svgrepo.com/show/465697/next.svg" class="arrowbutton" alt="Next Arrow"></li>-->
            <li onclick="toggleNotifications()"><img src="https://www.svgrepo.com/show/460025/notification-alt.svg" alt="Notifications Icon">Notifications <span><small style="background-color: #90EE90;">10 new</small></span></li>
            <li onclick="toggleMessages()"> <img src="https://www.svgrepo.com/show/522602/messages.svg" alt="Message Icon"> Messages <span><small style="background-color: #87CEFA;">100+ unread</small></span></li>
            </li>
            <li onclick="toggleWallet()"><img src="https://www.svgrepo.com/show/503592/support.svg" alt="Wallet Icon">Support <span><small style="background-color: #FFA07A;">1 ticket open</small></span></li>
            <li onclick="openBankModal()"><img src="https://www.svgrepo.com/show/521497/bank.svg" alt="Support Icon">Bank & Wallets <small style="background-color: #DDA0DD;"> Credits - 2 <br> Debits - 12 </small></span></li>
            <li onclick="toggleSupport()"><img src="https://www.svgrepo.com/show/453406/reports.svg" alt="Support Icon">Analytics <small style="background-color: #20B2AA;"> 12 new </small></span></li>

            <li onclick="toggleSupport()"><img src="https://www.svgrepo.com/show/488643/options.svg" alt="Support Icon">More
              <img src="https://www.svgrepo.com/show/465697/next.svg" class="arrowbutton" alt="Next Arrow">
            </li>
          </ul>

        </div>
        <div style="margin-top: 20px;">
          <ul class="menu">
            <li onclick="toggleSupport()"><img src="https://www.svgrepo.com/show/524954/settings.svg" alt="Support Icon">Settings </li>
            <li onclick="window.location.href='logout.php'"><img src="https://www.svgrepo.com/show/470849/logout.svg" alt="Support Icon">Logout</li>

          </ul>
        </div>
      </div>

    </div>
    <div class="sectionq">

      <header>
        <!--<div class="logo menulog">-->


        <div class="menu-icons">
          <a href="#" title="Home" style="position: relative;">
            <img src="https://www.svgrepo.com/show/524649/home-1.svg">
            <span style="position: absolute; background-color: yellow; padding: 5px; border-radius: 3px; top: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; display: none;">Home</span>
          </a>
          <a href="#" class="language-switch change-language-button" title="Languages" style="position: relative;">
            <img class="imageflag" src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/79/Flag_of_Nigeria.svg/2000px-Flag_of_Nigeria.svg.png">
            <span style="position: absolute; background-color: yellow; padding: 5px; border-radius: 3px; top: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; display: none;">Languages</span>
          </a>
          <a href="#" title="Vault" style="position: relative;" onclick="openVaultModal()">
            <img src="https://www.svgrepo.com/show/310190/vault.svg">
            <span style="position: absolute; background-color: yellow; padding: 5px; border-radius: 3px; top: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; display: none;">Vault</span>
          </a>
        </div>
        <style>
          .menu-icons a:hover span {
            display: block;
          }
        </style>
        <div class="search-bar">

          <input type="text" placeholder="Global Search...">
        </div>
        <div class="menu-icons">
          <a href="#"><img src="https://www.svgrepo.com/show/458734/group-share.svg"></a>
          <a href="#"><img src="https://www.svgrepo.com/show/487479/invoice.svg"></a>
          <a href="#"><img src="https://www.svgrepo.com/show/533396/calendar-lines-pen.svg"></a>
          <a href="#"><img src="https://www.svgrepo.com/show/526839/calendar-search.svg"></a>
          <a href="#"><img src="https://www.svgrepo.com/show/330019/azurefunctions.svg" onclick="openProjectModal()"></a>
          <a href="#"><img src="https://www.svgrepo.com/show/442334/preferences-desktop-online-accounts-symbolic.svg"></a>

          <a href="#" style="background-color: black; border-radius: 5px; color: white; padding: 10px; font-size: 13px;">Trial expires in 48 hours</a>
        </div>
        <img src="simple-social-svgrepo-com.png" alt="Profile Picture" class="profile-pic">
      </header>
      <div class="idontknow" style="display: flex; justify-content: space-between; align-items: center; margin: 10px 20px; background-color: #f0f0f0; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="text-align: left;">
          <a href="#" onclick="openUpdatesModal()" class="updates-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Updates (2)</a>
          <a href="#" onclick="openReportsModal()" class="reports-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Reports (7)</a>
          <a href="#" onclick="openTalentModal()" class="employees-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Talents (2)</a>
          <a href="#" onclick="openAppIntegrationModal()" class="connect-apps-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Apps (10+)</a>
          <a href="#" onclick="openKpiModal()" class="kpi-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">KPIs</a>
          <a href="#" onclick="openActivityLogsModal()" class="activitylogs-link" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Logs</a>
          <a href="#" class="alerts-link" style="font-size: 14px; color: #ff0000; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Alerts (19+)</a>
        </div>
        <div style="text-align: right;">
          <a href="#" onclick="openCreateTaskModal()" class="create-task-button" style="font-size: 14px; color: #fff; background-color: #007bff; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-right: 15px;">Create Task +</a>
          <a href="#" class="customize-widget-link" onclick="openWidgetModal()" style="font-size: 14px; color: #333; margin-right: 15px; text-decoration: none; font-weight: bold; transition: color 0.3s ease;">Customize Widget <span style="font-size: 18px;">›</span></a>
        </div>
      </div>

      <div class="wedontknow" style="display: flex; justify-content: space-between; margin-top: 20px;">

        <div class="onboarding-container" style="width: 40%; background-color: #e6f2ff; padding: 20px; margin-right: 10px; margin-left: 20px; border-radius: 5px; display: flex; flex-direction: column; justify-content: center; align-items: center; font-family: Arial, sans-serif; font-size: 14px; border: 2px solid #4a90e2;">
          <img src="https://images.pexels.com/photos/697509/pexels-photo-697509.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Onboarding Image" style="width: 60px; height: 60px; margin-bottom: 15px; border-radius: 50%;">
          <p style="font-weight: 600; margin-bottom: 10px;"> Welcome, Admin </p>
          <div class="progress-bar">
            <div class="progress">
              <span class="progress-number">Your company's profile is 75% complete</span>
            </div>
          </div>
          <div class="additional-info" style="width: 100%; margin-top: 15px; color: #666;">
            <h3 style="font-size: 16px; margin-bottom: 10px; color: #666;">Next Steps</h3>
            <ul style="padding-left: 20px; margin: 0; color: #666;">
              <li style="margin-bottom: 5px;">Verify your company's email</li>
              <li style="margin-bottom: 5px;">Subscribe to your toolkits</li>
              <li style="margin-bottom: 5px;">Create various departments</li>
              <li style="margin-bottom: 5px;">Add managers and invite employees</li>
            </ul>
          </div>


          <button class="onboarding-button" style="background-color: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 12px; margin-top: 10px;"> Complete Your Onboarding</button>
          <div> <small> Chat with support <a href="https://wa.me/+2349151294786">here</a> or take a <a href="#">quick survey</a> </small> </div>

        </div>

        <div class=""></div>

        <div class="widgets-container">
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">


                  <?php
                  include '../../sql_queries/db.php';

                  $sql = "SELECT * FROM toolkit_subscriptions WHERE company_id='$companyId'";
                  $result = $conn->query($sql);

                  $active_subscriptions = 0;
                  $tools = ['payroll', 'recruiting', 'crm', 'cybersecurity', 'project', 'strategy', 'risk', 'sales', 'product', 'office'];

                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      foreach ($tools as $tool) {
                        $expiry_date = strtotime($row[$tool . '_expireddate']);
                        if ($expiry_date > time()) {
                          $active_subscriptions++;
                        }
                      }
                    }
                  }

                  echo "<span style=\"padding-left: 0px; text-indent: 0px;\">\n    ";
                  echo $active_subscriptions;
                  echo "\n\n</span>";

                  $conn->close();
                  ?>



                </span>




                <span class="widget-title">Subscriptions</span>
                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <?php
                  include '../../sql_queries/db.php';

                  $sql = "SELECT * FROM toolkit_subscriptions WHERE company_id = ?";
                  $stmt = $conn->prepare($sql);
                  $stmt->bind_param("i", $companyId);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  $subscriptions = [];

                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      $subscriptions[] = [
                        'id' => $row['id'],
                        'email' => $row['email'],
                        'payroll_months' => $row['payroll_months'],
                        'payroll_startdate' => $row['payroll_startdate'],
                        'payroll_expireddate' => $row['payroll_expireddate'],
                        'recruiting_months' => $row['recruiting_months'],
                        'recruiting_startdate' => $row['recruiting_startdate'],
                        'recruiting_expireddate' => $row['recruiting_expireddate'],
                        'crm_months' => $row['crm_months'],
                        'crm_startdate' => $row['crm_startdate'],
                        'crm_expireddate' => $row['crm_expireddate'],
                        'cybersecurity_months' => $row['cybersecurity_months'],
                        'cybersecurity_startdate' => $row['cybersecurity_startdate'],
                        'cybersecurity_expireddate' => $row['cybersecurity_expireddate'],
                        'project_months' => $row['project_months'],
                        'project_startdate' => $row['project_startdate'],
                        'project_expireddate' => $row['project_expireddate'],
                        'strategy_months' => $row['strategy_months'],
                        'strategy_startdate' => $row['strategy_startdate'],
                        'strategy_expireddate' => $row['strategy_expireddate'],
                        'risk_months' => $row['risk_months'],
                        'risk_startdate' => $row['risk_startdate'],
                        'risk_expireddate' => $row['risk_expireddate'],
                        'sales_months' => $row['sales_months'],
                        'sales_startdate' => $row['sales_startdate'],
                        'sales_expireddate' => $row['sales_expireddate'],
                        'product_months' => $row['product_months'],
                        'product_startdate' => $row['product_startdate'],
                        'product_expireddate' => $row['product_expireddate'],
                        'office_months' => $row['office_months'],
                        'office_startdate' => $row['office_startdate'],
                        'office_expireddate' => $row['office_expireddate'],
                        'created_at' => $row['created_at']
                      ];
                    }
                  } else {
                    echo "You have no subscriptions. Kindly subscribe.";
                    exit;
                  }

                  $conn->close();

                  $tools = ['payroll', 'recruiting', 'crm', 'cybersecurity', 'project', 'strategy', 'risk', 'sales', 'product', 'office'];
                  $expiring_tools = [];

                  foreach ($subscriptions as $subscription) {
                    foreach ($tools as $tool) {
                      $expiry_date = strtotime($subscription[$tool . '_expireddate']);
                      $days_until_expiry = ceil(($expiry_date - time()) / (60 * 60 * 24));

                      if ($days_until_expiry <= 0) {
                        $expiring_tools[] = ucfirst($tool) . " tool has expired. Renew now";
                      } elseif ($days_until_expiry <= 30) {
                        $expiring_tools[] = ucfirst($tool) . " tool due to expire in {$days_until_expiry} days. Renew now";
                      }
                    }
                  }

                  echo "<span style=\"padding-left: 0px; text-indent: 0px;\">\n    \n";
                  if (!empty($expiring_tools)) {
                    $random_expiring_tool = $expiring_tools[array_rand($expiring_tools)];
                    echo $random_expiring_tool . "\n";
                  } else {
                    $random_tool = $tools[array_rand($tools)];
                    $random_days = rand(1, 30);
                    echo ucfirst($random_tool) . " tool due to expire in {$random_days} days. Renew now\n";
                  }
                  echo "\n\n</span>";
                  ?>

                </small>



              </div>

              <img src="https://www.svgrepo.com/show/503466/paywall.svg" alt="Subscription Icon" class="widget-icon1 widget-icon" onclick="openSubscriptionsModal()">


            </div>

          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">16</span>
                <span class="widget-title"> Events </span>
                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;">Dev. Team meetings starting in 3 mins. Join now</span>
                </small>

              </div>
              <img src="https://www.svgrepo.com/show/446859/events.svg" alt="Active Users Icon" class="widget-icon2 widget-icon" onclick=openEventsModal()>
            </div>
          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <?php
                include '../../sql_queries/db.php';
                $companyId = $_SESSION['company_id'];
                $query = "SELECT COUNT(*) as employee_count FROM employees WHERE company_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $companyId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $employee_count = $row['employee_count'];
                $stmt->close();
                ?>
                <span class="widget-number"><?php echo number_format($employee_count); ?></span>
                <span class="widget-title">Employees</span>

                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;"> Last logout: Emmanuel O. by 5:30pm today</span>
                </small>
              </div>
              <img src="https://www.svgrepo.com/show/146754/user-list.svg" alt="Inactive Users Icon" class="widget-icon3 widget-icon" onclick="openActiveUsersModal()">
            </div>
          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">2</span>
                <span class="widget-title">Automations</span>

                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;"> 2 finance automations completed their task. View Results</span>
                </small>


              </div>


              <img src="https://www.svgrepo.com/show/390204/automation-marketing-launch-rocket-laptop.svg" alt="Task Overview Icon" class="widget-icon4 widget-icon" onclick="openAutomationsModal();">
            </div>

          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">34</span>
                <span class="widget-title">Workflows</span>
                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;"> 2900 tasks completed in the last 7 days, 10 still pending</span>
                </small>

              </div>
              <img src="https://www.svgrepo.com/show/360836/task-list-f.svg" alt="Notification Icon" class="widget-icon5 widget-icon" onclick="openWorkflowsModal();">
            </div>
          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">3,040</span>
                <span class="widget-title">Mentions</span>
                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;"> Emmanuel O. and Jeffery T. just mentioned you </span>
                </small>

              </div>
              <img src="https://www.svgrepo.com/show/310585/calendar-mention.svg" alt="Highlights Icon" class="widget-icon6 widget-icon" onclick="openMentionsModal();">
            </div>
          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">18</span>
                <span class="widget-title">Intelligence</span>

                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;"> 18 neurons deployed and gathering data on your company processes </span>
                </small>

              </div>
              <img src="https://www.svgrepo.com/show/482780/brain-3.svg" alt="Goal Tracker Icon" class="widget-icon7 widget-icon" onclick="openSIntelligenceModal();">
            </div>
          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">12</span>
                <span class="widget-title">Insights</span>

                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;"> Emmanuel O. suggested you integrate Github app and Simplr Project Mgt. tool </span>
                </small>
              </div>
              <img src="https://www.svgrepo.com/show/335413/insights-portal.svg" alt="AI-Powered Insights Icon" class="widget-icon8 widget-icon" onclick="openInsightsModal()">
            </div>
          </div>
          <div class="widget">
            <div class="widget-content">
              <div class="widget-text">
                <span class="widget-number">4</span>
                <span class="widget-title">Teams</span>

                <small class="widget-details" style="font-size: 8px; margin-top: 10px; margin-bottom: -30px; display: flex; align-items: flex-start;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-right: 5px; margin-top: 2.0px; flex-shrink: 0;"></span>
                  <span style="padding-left: 0px; text-indent: 0px;"> Your Product team and Operations team just hit a milestone. Celebrate them. </span>
                </small>

              </div>
              <img src="https://www.svgrepo.com/show/458732/group.svg" alt="Communication Hub Icon" class="widget-icon9 widget-icon" onclick="openTeamsModal();">
            </div>
          </div>
        </div>


      </div>

      <div id="employeeModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Manage Company</h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'CreateDepartments')">Create Departments</button>
            <button class="tablinks" onclick="openTab(event, 'CreateManagers')">Create Managers</button>
            <button class="tablinks" onclick="openTab(event, 'AddEmployees')">Add Employees</button>
            <button class="tablinks" onclick="openTab(event, 'ViewEmployees')">View Employees</button>
          </div>

          <div id="CreateDepartments" class="tabcontent">

            <form action="/mycompany/createdepartment.php" method="POST">
              <input type="hidden" name="company_id" value="<?php echo $companyId ?>" hidde>

              <div class="input-container">
                <input type="text" id="new_department" name="department_name" placeholder=" " required>
                <label for="new_department">Department Name:</label>
              </div>

              <div class="input-container">

                <input type="text" name="location" placeholder=" ">


                <label for="location">Location(optional):</label>
              </div>

              <input type="submit" value="Create Department">
            </form>


          </div>

          <div id="CreateManagers" class="tabcontent">
            <form action="/mycompany/createmanager.php" method="POST">
              <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">

              <div class="input-container">

                <input type="text" class="new_manager" name="manager_name" placeholder=" " required>
                <label for="new_manager">Manager Name:</label>


              </div>
              <div class="input-container">

                <input type="email" id="manager_email" name="manager_email" placeholder="" required>
                <label for="manager_email">Manager Email:</label>

              </div>

              <div class="input-container">

                <select name="department_id" required>
                  <option value="" disabled selected>Select a department</option>
                  <?php
                  // PHP code to fetch departments from the database and populate the select options
                  include '../../sql_queries/db.php';

                  $result = $conn->query("SELECT id, department_name FROM departments WHERE company_id = $companyId AND manager_id IS NULL");
                  while ($row = $result->fetch_assoc()) {
                    echo "<option value=\"" . $row['id'] . "\">" . $row['department_name'] . "</option>";
                  }
                  ?>
                </select>
                <label for="department_id">Department:</label>

              </div>
              <input type="hidden" id="role" name="role_name" value="Manager">
              <input type="hidden" name="description" value="Manager with access to managerial functions">
              <input type="submit" value="Create Manager">
            </form>
          </div>

          <div id="AddEmployees" class="tabcontent">
            <form id="inviteStaffForm" method="POST" action="invitestaff.php">

              <div class="input-container">

                <input type="text" id="first_name" name="first_name" placeholder="" required autocomplete="given-name">
                <label for="first_name">First Name:</label>

              </div>

              <div class="input-container">

                <input type="text" id="last_name" name="last_name" placeholder="" required autocomplete="given-name">
                <label for="last_name">Last Name:</label>


              </div>

              <div class="input-container">

                <input type="email" name="email" placeholder="" required autocomplete="given-name">
                <label for="email">Email:</label>

              </div>

              <div class="input-container">

                <input type="tel" id="staffphone" name="staffphone" placeholder="">
                <label for="phone">Staff Phone:</label>

              </div>

              <div class="input-container">

                <input type="date" id="hire_date" placeholder="" name="hire_date" required>
                <label for="hire_date">Hire Date:</label>

              </div>

              <div class="input-container">

                <input type="text" id="job_title" placeholder="" name="job_title">
                <label for="job_title">Job Title:</label>

              </div>

              <div class="input-container">

                <select id="employeedepartments" name="department_id" required onchange="setManagerId()">
                  <option value="" disabled selected>Select a department</option>
                  <?php
                  // PHP code to fetch departments with managers from the database
                  include '../../sql_queries/db.php';

                  $result = $conn->query("SELECT id, department_name, manager_id FROM departments WHERE manager_id IS NOT NULL AND company_id = $companyId");
                  while ($row = $result->fetch_assoc()) {
                    echo "<option value=\"" . $row['id'] . "\" data-manager-id=\"" . $row['manager_id'] . "\">" . $row['department_name'] . "</option>";
                  }
                  ?>
                </select>
                <label for="department">Department:</label>

              </div>
              <input type="hidden" id="employeemanager_id" name="manager_id" required>
              <input type="hidden" id="employeerole" name="role_name" value="Employee">
              <input type="hidden" name="description" value="Employee with access to staff functions">
              <input type="hidden" id="company_id" name="company_id" value="<?php echo $companyId; ?>" required>
              <input type="submit" value="Invite Staff">

            </form>
          </div>

          <div id="ViewEmployees" class="tabcontent">
            <!--<h3>View Employees</h3>-->
            <?php //include '../../users/admin/viewemployees.php'; 
            ?>
          </div>
        </div>
      </div>
      <div id="appIntegrationModal" class="modal" style="display:none;">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>App Integrations</h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'Finance')">Finance & Payroll (5)</button>
            <button class="tablinks" onclick="openTab(event, 'Development')">Product Dev. (3) <sup style="font-size: 0.5em; background-color: red; color: white; padding: 2px 4px; border-radius: 10px; margin-left: 5px;">+3 new</sup></button></button>
            <button class="tablinks" onclick="openTab(event, 'RiskMgt')">Risk Mgt (2)</button>
            <button class="tablinks" onclick="openTab(event, 'ProjectManagement')">Project Mgt (4)</button>
            <button class="tablinks" onclick="openTab(event, 'HumanResources')">Recruitment (3)</button>
            <button class="tablinks" onclick="openTab(event, 'CustomerRelations')">CRM (2)</button>
            <button class="tablinks" onclick="openTab(event, 'InventoryManagement')">Inventory Mgt (1)</button>
            <button class="tablinks" onclick="openTab(event, 'Marketing')">Marketing (3)</button>
            <button class="tablinks" onclick="openTab(event, 'Analytics')">Strategy & Modelling (4)</button>
            <button class="tablinks" onclick="openTab(event, 'Communication')">Office Mgt (2)</button>
            <button class="tablinks" onclick="openTab(event, 'Research')">Cyber Security (1)</button>
            <!--<button class="tablinks new-tool" onclick="openTab(event, 'NewTool')">New Tool (1) <sup style="font-size: 0.5em; background-color: red; color: white; padding: 2px 4px; border-radius: 10px; margin-left: 5px;">3 new</sup></button>-->
          </div>

          <style>
            /*.tool-logo {*/
            /*    width: 35px;*/
            /*    height: 35px;*/
            /*    border-radius: 5px;*/
            /*    margin-right: 10px;"*/
            /*}*/

            /*.btn {*/
            /*   padding: 5px 20px; font-size: 12px; background-color: #635BFF; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;"*/
            /*}*/

            /*.tool-item {*/
            /*    display: flex; */
            /*    align-items: center;*/
            /*    margin-bottom: 5px;*/
            /*}*/
            .tool-list {
              display: grid;
              grid-template-columns: repeat(4, 1fr);
              /* 4 columns */
              gap: 16px;
              /* Space between items */
              margin: 0;
              padding: 0;
              list-style: none;
            }

            .tool-item {
              display: flex;
              flex-direction: column;
              align-items: center;
              /* Center-aligns the items */
              text-align: center;
            }

            .tool-logo {
              width: 50px;
              /* Adjust this value as needed */
              height: 50px;
              /* Maintain aspect ratio */
              margin-bottom: 8px;
              border-radius: 10px;
              /*border: 1px solid pink;*/
              padding: 2px;
              box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
              /* Adds a subtle shadow */
              transition: transform 0.3s ease, box-shadow 0.3s ease;
              /* Smooth transition */
            }

            .tool-logo:hover {
              transform: scale(1.1);
              /* Slightly enlarges the element on hover */
              box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
              /* Darker shadow on hover */
            }


            .btn {
              padding: 8px 16px;
              /*display:none;*/
              font-size: 14px;
              text-decoration: none;
              border: none;
              cursor: pointer;
            }

            .btn-primary {
              background-color: grey;
              /* Bootstrap primary color */
              color: white;
              border-radius: 4px;
            }

            /* Responsive Design */
            @media (max-width: 1200px) {
              .tool-list {
                grid-template-columns: repeat(3, 1fr);
                /* 3 columns for medium screens */
              }
            }

            @media (max-width: 900px) {
              .tool-list {
                grid-template-columns: repeat(2, 1fr);
                /* 2 columns for small screens */
              }
            }

            @media (max-width: 600px) {
              .tool-list {
                grid-template-columns: 1fr;
                /* 1 column for extra-small screens */
              }
            }
          </style>

          <div id="Finance" class="tabcontent">
            <h3>Finance Tools</h3>
            <div class="tool-list">
              <div class="tool-item">
                <img src="integratedimg/image_processing20210904-7932-1eyochb.gif" alt="Stripe Logo" class="tool-logo stripelogo">
                <button onclick="showIntegrationSteps('Stripe')" class="btn btn-primary">Integrate Stripe</button>
              </div>
              <div class="tool-item">
                <img src="integratedimg/Plaid_(company)-Vertical-White-Dark-Background-Logo.wine.svg" alt="Plaid Logo" class="tool-logo plaidlogo">
                <button onclick="showIntegrationSteps('Plaid')" class="btn btn-primary">Integrate Plaid</button>
              </div>

              <div class="tool-item">
                <img src="integratedimg/quickbooks.png" alt="QuickBooks Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('QuickBooks')" class="btn btn-primary">Integrate QuickBooks</button>
              </div>
              <div class="tool-item">
                <img src="integratedimg/ZpfeV3LKHrpE6lY4bHEHuFEbQ6JKYIYxW1Ma38h9.png
" alt="Xero Logo" style="border: 1px solid grey" class="tool-logo">
                <button onclick="showIntegrationSteps('Xero')" class="btn btn-primary">Integrate Xero</button>
              </div>
              <div class="tool-item">
                <img src="integratedimg/square-logo-png-transparent.png" alt="Square Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Square')" class="btn btn-primary">Integrate Square</button>
              </div>
              <div class="tool-item">
                <img src="integratedimg/pngimg.com - paypal_PNG7.png
" alt="PayPal Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('PayPal')" class="btn btn-primary">Integrate PayPal</button>
              </div>
              <div class="tool-item">
                <img src="integratedimg/bf41e6ce97e3682ae318ab75b3295716_icon.png
" alt="FreshBooks Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('FreshBooks')" class="btn btn-primary">Integrate FreshBooks</button>
              </div>
              <div class="tool-item">
                <img src="https://cdn.prod.website-files.com/62446230dcb514b828a6e237/624dedebca225a69a155c7b6_wave-logo-black.svg" alt="Wave Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Wave')" class="btn btn-primary">Integrate Wave</button>
              </div>
              <div class="tool-item">
                <img src="https://arizent.brightspotcdn.com/a0/53/c4a6a0ae4961bc65ba9e26065d13/sage-logo14.png" alt="Sage Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Sage')" class="btn btn-primary">Integrate Sage</button>
              </div>
              <div class="tool-item">
                <img src="https://1000logos.net/wp-content/uploads/2021/04/ADP-logo.png" alt="ADP Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('ADP')" class="btn btn-primary">Integrate ADP</button>
              </div>
              <div class="tool-item">
                <img src="https://prod.gusto-assets.com/assets/svg/header/gusto-logo-bfed4fde4bd2b5dd364ab0e58f77fc90aeefa2f1ecdb7fff524279642e23f9c2.svg" alt="Gusto Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Gusto')" class="btn btn-primary">Integrate Gusto</button>
              </div>
              <div class="tool-item">
                <img src="https://www.merchantmaverick.com/wp-content/uploads/2019/02/Paychex_logo.png" alt="Paychex Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Paychex')" class="btn btn-primary">Integrate Paychex</button>
              </div>
              <div class="tool-item">
                <img src="https://cdn.prod.website-files.com/63e56114746188c54e2936e0/6488d4091595a41dd00d36b5_BILL.png" alt="Bill.com Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Bill.com')" class="btn btn-primary">Integrate Bill.com</button>
              </div>
              <div class="tool-item">
                <img src="https://new.expensify.com/og-preview-image.png" alt="Expensify Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Expensify')" class="btn btn-primary">Integrate Expensify</button>
              </div>
              <div class="tool-item">
                <img src="https://logosandtypes.com/wp-content/uploads/2020/07/Netsuite.png" alt="NetSuite Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('NetSuite')" class="btn btn-primary">Integrate NetSuite</button>
              </div>
              <div class="tool-item">
                <img src="https://avatars.slack-edge.com/2019-08-29/730880231907_caa71cf812305a064114_512.png" alt="Zoho Books Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Zoho Books')" class="btn btn-primary">Integrate Zoho Books</button>
              </div>
              <div class="tool-item">
                <img src="https://tipalti.com/wp-content/themes/Tipalti-GoTeam/assets/images/featured-image.webp" alt="Tipalti Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Tipalti')" class="btn btn-primary">Integrate Tipalti</button>
              </div>
              <div class="tool-item">
                <img src="https://download.logo.wine/logo/Workday%2C_Inc./Workday%2C_Inc.-Logo.wine.png" alt="Workday Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Workday')" class="btn btn-primary">Integrate Workday</button>
              </div>
              <div class="tool-item">
                <img src="https://logosandtypes.com/wp-content/uploads/2020/08/recurly.png" alt="Recurly Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Recurly')" class="btn btn-primary">Integrate Recurly</button>
              </div>
              <div class="tool-item">
                <img src="https://logosandtypes.com/wp-content/uploads/2020/06/Chargebee.png" alt="Chargebee Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Chargebee')" class="btn btn-primary">Integrate Chargebee</button>
              </div>

            </div>
          </div>

          <div id="Development" class="tabcontent">
            <h3>Development Tools</h3>
            <div class="tool-list">
              <div class="tool-item">
                <img src="github-logo.png" alt="GitHub Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('GitHub')" class="btn btn-primary">Integrate GitHub</button>
              </div>
              <div class="tool-item">
                <img src="zapier-logo.png" alt="Zapier Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Zapier')" class="btn btn-primary">Integrate Zapier</button>
              </div>
              <div class="tool-list">
                <div class="tool-item">
                  <img src="github-logo.png" alt="GitHub Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('GitHub')" class="btn btn-primary">Integrate GitHub</button>
                </div>
                <div class="tool-item">
                  <img src="zapier-logo.png" alt="Zapier Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Zapier')" class="btn btn-primary">Integrate Zapier</button>
                </div>
                <div class="tool-item">
                  <img src="jira-logo.png" alt="Jira Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Jira')" class="btn btn-primary">Integrate Jira</button>
                </div>
                <div class="tool-item">
                  <img src="trello-logo.png" alt="Trello Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Trello')" class="btn btn-primary">Integrate Trello</button>
                </div>
                <div class="tool-item">
                  <img src="asana-logo.png" alt="Asana Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Asana')" class="btn btn-primary">Integrate Asana</button>
                </div>
                <div class="tool-item">
                  <img src="slack-logo.png" alt="Slack Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Slack')" class="btn btn-primary">Integrate Slack</button>
                </div>
                <div class="tool-item">
                  <img src="figma-logo.png" alt="Figma Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Figma')" class="btn btn-primary">Integrate Figma</button>
                </div>
                <div class="tool-item">
                  <img src="gitlab-logo.png" alt="GitLab Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('GitLab')" class="btn btn-primary">Integrate GitLab</button>
                </div>
                <div class="tool-item">
                  <img src="confluence-logo.png" alt="Confluence Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Confluence')" class="btn btn-primary">Integrate Confluence</button>
                </div>
                <div class="tool-item">
                  <img src="azure-devops-logo.png" alt="Azure DevOps Logo" class="tool-logo">
                  <button onclick="showIntegrationSteps('Azure DevOps')" class="btn btn-primary">Integrate Azure DevOps</button>
                </div>
              </div>

            </div>
          </div>

          <div id="RiskMgt" class="tabcontent">
            <h3>Risk Management Tools</h3>
            <div class="tool-list">
              <div class="tool-item">
                <img src="risktools-logo.png" alt="Risk Tools Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('RiskTools')" class="btn btn-primary">Integrate Risk Tools</button>
              </div>
              <div class="tool-item">
                <img src="eramba-logo.png" alt="Eramba Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Eramba')" class="btn btn-primary">Integrate Eramba</button>
              </div>
              <div class="tool-item">
                <img src="resolver-logo.png" alt="Resolver Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Resolver')" class="btn btn-primary">Integrate Resolver</button>
              </div>
              <div class="tool-item">
                <img src="riskonnect-logo.png" alt="Riskonnect Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Riskonnect')" class="btn btn-primary">Integrate Riskonnect</button>
              </div>
              <div class="tool-item">
                <img src="metricstream-logo.png" alt="MetricStream Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('MetricStream')" class="btn btn-primary">Integrate MetricStream</button>
              </div>
              <div class="tool-item">
                <img src="logicmanager-logo.png" alt="LogicManager Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('LogicManager')" class="btn btn-primary">Integrate LogicManager</button>
              </div>
              <div class="tool-item">
                <img src="sap-grm-logo.png" alt="SAP GRC Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('SAP GRC')" class="btn btn-primary">Integrate SAP GRC</button>
              </div>
              <div class="tool-item">
                <img src="archer-logo.png" alt="Archer Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Archer')" class="btn btn-primary">Integrate Archer</button>
              </div>
              <div class="tool-item">
                <img src="galvanize-logo.png" alt="Galvanize Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Galvanize')" class="btn btn-primary">Integrate Galvanize</button>
              </div>
              <div class="tool-item">
                <img src="onspring-logo.png" alt="OnSpring Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('OnSpring')" class="btn btn-primary">Integrate OnSpring</button>
              </div>
              <div class="tool-item">
                <img src="servicenow-grm-logo.png" alt="ServiceNow GRM Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('ServiceNow GRM')" class="btn btn-primary">Integrate ServiceNow GRM</button>
              </div>
              <div class="tool-item">
                <img src="camms-logo.png" alt="CAMMS Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('CAMMS')" class="btn btn-primary">Integrate CAMMS</button>
              </div>
              <div class="tool-item">
                <img src="protecht-logo.png" alt="Protecht Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Protecht')" class="btn btn-primary">Integrate Protecht</button>
              </div>
              <div class="tool-item">
                <img src="riskrecon-logo.png" alt="RiskRecon Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('RiskRecon')" class="btn btn-primary">Integrate RiskRecon</button>
              </div>
              <div class="tool-item">
                <img src="bwise-logo.png" alt="BWise Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('BWise')" class="btn btn-primary">Integrate BWise</button>
              </div>
              <div class="tool-item">
                <img src="acuity-logo.png" alt="Acuity Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Acuity')" class="btn btn-primary">Integrate Acuity</button>
              </div>
              <div class="tool-item">
                <img src="sword-grc-logo.png" alt="Sword GRC Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Sword GRC')" class="btn btn-primary">Integrate Sword GRC</button>
              </div>
              <div class="tool-item">
                <img src="riskvision-logo.png" alt="RiskVision Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('RiskVision')" class="btn btn-primary">Integrate RiskVision</button>
              </div>
              <div class="tool-item">
                <img src="cura-logo.png" alt="CURA Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('CURA')" class="btn btn-primary">Integrate CURA</button>
              </div>
              <div class="tool-item">
                <img src="optial-logo.png" alt="Optial Logo" class="tool-logo">
                <button onclick="showIntegrationSteps('Optial')" class="btn btn-primary">Integrate Optial</button>
              </div>

            </div>
          </div>

          <!-- remove this line --->

          <div id="integrationSteps" style="display: none;">
            <h3 id="integrationTitle"></h3>
            <div id="integrationContent"></div>
          </div>
        </div>
      </div>
      <div id="kpiModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Key Performance Indicators (KPIs)</h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openKpiTab(event, 'FinancialKPIs')">Financial KPIs</button>
            <button class="tablinks" onclick="openKpiTab(event, 'OperationalKPIs')">Operational KPIs</button>
            <button class="tablinks" onclick="openKpiTab(event, 'CustomerKPIs')">Customer KPIs</button>
            <button class="tablinks" onclick="openKpiTab(event, 'EmployeeKPIs')">Employee KPIs</button>
          </div>

          <div id="FinancialKPIs" class="tabcontent">
            <h3>Financial KPIs</h3>
            <div class="kpi-list">
              <div class="kpi-item">
                <h4>Revenue Growth Rate</h4>
                <div class="kpi-value">8.5%</div>
                <div class="kpi-trend positive">↑ 2.1%</div>
              </div>
              <div class="kpi-item">
                <h4>Profit Margin</h4>
                <div class="kpi-value">15.3%</div>
                <div class="kpi-trend negative">↓ 0.7%</div>
              </div>
              <div class="kpi-item">
                <h4>Return on Investment (ROI)</h4>
                <div class="kpi-value">12.8%</div>
                <div class="kpi-trend positive">↑ 1.5%</div>
              </div>
            </div>
          </div>

          <div id="OperationalKPIs" class="tabcontent">
            <h3>Operational KPIs</h3>
            <div class="kpi-list">
              <div class="kpi-item">
                <h4>On-Time Delivery Rate</h4>
                <div class="kpi-value">94.2%</div>
                <div class="kpi-trend positive">↑ 1.8%</div>
              </div>
              <div class="kpi-item">
                <h4>Inventory Turnover</h4>
                <div class="kpi-value">6.5</div>
                <div class="kpi-trend negative">↓ 0.3</div>
              </div>
              <div class="kpi-item">
                <h4>Capacity Utilization</h4>
                <div class="kpi-value">78.9%</div>
                <div class="kpi-trend positive">↑ 3.2%</div>
              </div>
            </div>
          </div>

          <div id="CustomerKPIs" class="tabcontent">
            <h3>Customer KPIs</h3>
            <div class="kpi-list">
              <div class="kpi-item">
                <h4>Customer Satisfaction Score</h4>
                <div class="kpi-value">8.7/10</div>
                <div class="kpi-trend positive">↑ 0.3</div>
              </div>
              <div class="kpi-item">
                <h4>Customer Retention Rate</h4>
                <div class="kpi-value">85.6%</div>
                <div class="kpi-trend negative">↓ 1.2%</div>
              </div>
              <div class="kpi-item">
                <h4>Net Promoter Score (NPS)</h4>
                <div class="kpi-value">62</div>
                <div class="kpi-trend positive">↑ 5</div>
              </div>
            </div>
          </div>

          <div id="EmployeeKPIs" class="tabcontent">
            <h3>Employee KPIs</h3>
            <div class="kpi-list">
              <div class="kpi-item">
                <h4>Employee Engagement Score</h4>
                <div class="kpi-value">7.8/10</div>
                <div class="kpi-trend positive">↑ 0.5</div>
              </div>
              <div class="kpi-item">
                <h4>Employee Turnover Rate</h4>
                <div class="kpi-value">12.3%</div>
                <div class="kpi-trend negative">↑ 1.5%</div>
              </div>
              <div class="kpi-item">
                <h4>Training Completion Rate</h4>
                <div class="kpi-value">92.7%</div>
                <div class="kpi-trend positive">↑ 2.9%</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="alertsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Alerts</h2>
          </div>
          <div class="modal-body">
            <div id="alertsList">
              <!-- Alerts will be dynamically added here -->
            </div>
          </div>
        </div>
      </div>
      <div id="activityLogsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Activity Logs</h2>
          </div>
          <div class="modal-body">
            <div class="log-details" style="display:none;">

              <div class="input-container">
                <select id="logType">
                  <option value="all">All Logs</option>
                  <option value="user">User Activity</option>
                  <option value="system">System Events</option>
                  <option value="security">Security Alerts</option>
                </select>
                <label> Log Types </label>
              </div>

              <div class="input-container">

                <input type="date" id="startDate">
                <label> Start Date </label>

              </div>
              <div class="input-container">

                <input type="date" id="endDate">
                <label for="endDate"> End Date </label>

              </div>
              <button onclick="filterLogs()">Filter</button>
            </div>

            <br>

            <div class="log-list">
              <table id="logTable">
                <thead>
                  <tr>
                    <th>Timestamp</th>
                    <th>Type</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                  </tr>
                </thead>
                <tbody id="logTableBody">
                  <!-- Log entries will be dynamically added here -->
                </tbody>
              </table>
            </div>
            <div class="pagination">
              <button onclick="previousPage()">« Previous</button>
              <span id="currentPage">Page 1</span>
              <button onclick="nextPage()">Next »</button>
            </div>
          </div>
        </div>
      </div>
      <div id="reportsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Reports</h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'FinancialReports')">Financial Reports</button>
            <button class="tablinks" onclick="openTab(event, 'OperationalReports')">Operational Reports</button>
            <button class="tablinks" onclick="openTab(event, 'HRReports')">HR Reports</button>
            <button class="tablinks" onclick="openTab(event, 'SalesReports')">Sales Reports</button>
            <button class="tablinks" onclick="openTab(event, 'CustomReports')">Custom Reports</button>
          </div>

          <div id="FinancialReports" class="tabcontent">
            <h3>Financial Reports</h3>
            <ul>
              <li><a href="#">Income Statement</a></li>
              <li><a href="#">Balance Sheet</a></li>
              <li><a href="#">Cash Flow Statement</a></li>
              <li><a href="#">Profit and Loss</a></li>
            </ul>
          </div>

          <div id="OperationalReports" class="tabcontent">
            <h3>Operational Reports</h3>
            <ul>
              <li><a href="#">Productivity Report</a></li>
              <li><a href="#">Inventory Report</a></li>
              <li><a href="#">Quality Control Report</a></li>
              <li><a href="#">Equipment Utilization Report</a></li>
            </ul>
          </div>

          <div id="HRReports" class="tabcontent">
            <h3>HR Reports</h3>
            <ul>
              <li><a href="#">Employee Turnover Report</a></li>
              <li><a href="#">Recruitment Report</a></li>
              <li><a href="#">Training and Development Report</a></li>
              <li><a href="#">Performance Evaluation Report</a></li>
            </ul>
          </div>

          <div id="SalesReports" class="tabcontent">
            <h3>Sales Reports</h3>
            <ul>
              <li><a href="#">Sales by Region</a></li>
              <li><a href="#">Product Performance Report</a></li>
              <li><a href="#">Customer Acquisition Report</a></li>
              <li><a href="#">Sales Pipeline Report</a></li>
            </ul>
          </div>

          <div id="CustomReports" class="tabcontent">
            <!--<h3>Custom Reports</h3>-->
            <!--<p>Create a custom report by selecting the data points you need:</p>-->
            <form id="customReportForm">
              <div class="input-container">
                <input type="text" id="reportName" name="reportName" required>
                <label for="reportName">Report Name:</label>

              </div>
              <div class="input-container">
                <select id="dataPoints" name="dataPoints" multiple>
                  <option value="revenue">Revenue</option>
                  <option value="expenses">Expenses</option>
                  <option value="employees">Employees</option>
                  <option value="customers">Customers</option>
                  <option value="products">Products</option>
                </select>
                <label for="dataPoints">Select Data Points:</label>

              </div>
              <div class="input-container">
                <input type="date" id="startDate" name="startDate" required>
                <label for="startRange">Start Date:</label>
              </div>
              <div class="input-container">

                <input type="date" id="endDate" name="endDate" required>

                <label for="endRange">End Date:</label>

              </div>
              <button type="submit" class="btn btn-primary">Generate Custom Report</button>
            </form>
          </div>
        </div>
      </div>
      <div id="updatesModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Updates</h2>
          </div>
          <div class="updates-container">
            <div class="update-item">
              <h3>Update 1</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
              <span class="update-date">May 15, 2023</span>
            </div>
            <div class="update-item">
              <h3>Update 2</h3>
              <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
              <span class="update-date">May 10, 2023</span>
            </div>
          </div>
        </div>
      </div>
      <div id="talentModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Talent Suggestions</h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'SuggestTalent')">Suggest Talent</button>
            <button class="tablinks" onclick="openTab(event, 'ViewSuggestions')">View Suggestions</button>
          </div>




          <div id="SuggestTalent" class="tabcontent">


            <?php

            include '../../sql_queries/db.php';


            // Check connection
            if (!$conn) {
              die("Connection failed: " . mysqli_connect_error());
            }

            // Query to get names and talent_id from talents table with limit and offset
            $query = "SELECT talent_id, name FROM talents";
            $result = mysqli_query($conn, $query);

            // Display talents
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<div>";
              echo $row['name'];
              echo " <button onclick='suggestToRecruiter(" . $row['talent_id'] . ")'>Suggest to Recruiter</button>";
              echo "</div>";
            }

            ?>



          </div>

          <div id="ViewSuggestions" class="tabcontent">
            <!--<h3>View Talent Suggestions</h3>-->
            <?php // include 'employees/work/talentlist.php'; 
            ?>

          </div>
        </div>
      </div>
      <div id="createTaskModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Create Task</h2>
          </div>
          <div class="modal-body">

            <style>
              .input-container {
                position: relative;
                margin-bottom: 15px;
              }

              .customizeforms label {
                position: absolute;
                left: 8px;
                color: #667;
                top: 50%;
                transform: translateY(-50%);
                font-size: 10px;
                pointer-events: none;
                transition: 0.2s ease all;
                padding: 0 4px;
                background-color: white;
              }

              input[type="text"],
              input[type="email"],
              input[type="password"],
              input[type="date"],
              input[type="file"],
              input[type="tel"],
              input[type="address"],
              select,
              textarea {
                width: 100%;
                padding: 12px 8px 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
              }

              input:focus+label,
              input:not(:placeholder-shown)+label,
              select:focus+label,
              select:not(:placeholder-shown)+label,
              textarea:focus+label,
              textarea:not(:placeholder-shown)+label {
                top: 0;
                color: grey;
              }



              input[type="submit"] {
                width: 100%;
                padding: 10px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 11px;
              }

              input[type="submit"]:hover {
                background-color: #45a049;
              }
            </style>

            <form id="createTaskForm" method="POST" enctype="multipart" class="customizeforms">


              <div class="input-container">

                <select name="project_id" id="project_id" required>
                  <?php
                  $projects_query = "SELECT id, projectName FROM projects";
                  $projects_result = $conn->query($projects_query);
                  if ($projects_result->num_rows > 0) {

                    while ($project = $projects_result->fetch_assoc()) {
                      echo "<option value='" . $project['id'] . "'>" . htmlspecialchars($project['projectName']) . "</option>";
                    }
                  } else {
                    echo "<option value='' disabled selected>No projects found for your organization</option>";
                  }

                  ?>
                </select>

                <label for="priority">Select Project:</label>
              </div>

              <div class="taskflex" style="display: flex; flex-direction: row; gap: 5px;">
                <div class="input-container" style="flex: 1;">
                  <input type="text" id="taskTitle" name="taskTitle" required placeholder=" ">
                  <label for="taskTitle">Task Title:</label>
                </div>

                <div class="buttonflex">
                  <button class="genetaskbutton" onclick="xologenerate(); event.preventDefault();" style="padding: 11px 10px; background-color: black; color: white; outline: none; border-radius: 5px; border: none; white-space: nowrap;">Generate Task with Xolo</button>
                </div>

                <style>
                  .genetaskbutton:hover {
                    cursor: pointer;
                  }
                </style>


              </div>
              <div class="input-container">
                <textarea id="taskDescription" name="taskDescription" rows="3" placeholder="Describe your task" style="font-size: 10px; color: green;"></textarea>

              </div>


              <div class="input-container">
                <input id="taskFile" type="file" name="taskfile" placeholder=" " multiple>
                <label for="taskFile">Attachments:</label>

              </div>

              <div class="input-container">
                <input type="date" id="dueDate" name="dueDate" required>
                <label for="dueDate">Due Date:</label>

              </div>
              <div class="input-container">
                <select id="priority" name="priority" required>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
                <label for="priority">Priority:</label>

              </div>
              <div class="input-container">
                <select id="assignee" name="assignee" required>
                  <option value="" disabled>Select an employee</option>
                  <!-- Add options dynamically based on employees -->
                  <?php include '../../users/admin/fetchemployeesfortask.php'; ?>
                </select>
                <label for="priority">Assign:</label>
              </div>
              <div class="input-container">
                <input type="hidden" id="taskcompany_id" name="company_id" value=" <?php echo $companyId; ?>" required placeholder=" ">
                <!--<label for="email">Email</label>-->
              </div>


              <input type="submit" value="Create Task">
            </form>

            <script>
              document.getElementById('createTaskForm').addEventListener('submit', function(e) {
                e.preventDefault();

                var createTaskData = new FormData(this);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'createtask.php', true);

                xhr.onload = function() {
                  if (xhr.status === 200) {
                    // Request successful
                    alert(xhr.responseText);
                    document.getElementById('createTaskForm').reset();
                    // Handle the response as needed
                  } else {
                    // Request failed
                    console.error('Error:', xhr.status);
                  }
                };

                xhr.onerror = function() {
                  console.error('Request failed');
                };

                xhr.send(createTaskData);
              });
            </script>

          </div>
        </div>
      </div>
      <div id="changeLanguageModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Change Language</h2>
          </div>
          <div class="modal-body">
            <form id="changeLanguageForm" class="styled-form">
              <div class="form-group">
                <label for="language">Select Language:</label>
                <ul id="languageList" class="language-list">
                  <li>
                    <span>English</span>
                    <button type="button" class="choose-language" data-lang="en">Choose</button>
                  </li>
                  <li>
                    <span>Español</span>
                    <button type="button" class="choose-language" data-lang="es">Choose</button>
                  </li>
                  <li>
                    <span>Français</span>
                    <button type="button" class="choose-language" data-lang="fr">Choose</button>
                  </li>
                  <li>
                    <span>Deutsch</span>
                    <button type="button" class="choose-language" data-lang="de">Choose</button>
                  </li>
                  <li>
                    <span>Italiano</span>
                    <button type="button" class="choose-language" data-lang="it">Choose</button>
                  </li>
                  <!-- Add more language options as needed -->
                </ul>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div id="bankWalletModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Bank Accounts and Wallets</h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'BankAccounts')">Create Bank Accounts</button>
            <button class="tablinks" onclick="openTab(event, 'Wallets')">Wallets</button>
          </div>

          <div id="BankAccounts" class="tabcontent">


            <!--<button> Create Naira Account </button>-->

            <!--<button> Create Dollar Account </button>-->


            <?php include '../users/admin/createbankaccount.php'; ?>

          </div>

          <div id="Wallets" class="tabcontent">
            <h3>Wallets</h3>
            <div id="walletList">
              <!-- Wallet items will be dynamically added here -->
            </div>
          </div>
        </div>
      </div>


      <!-- top level menu --->


      <div id="createProjectModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Create Project</h2>
          </div>
          <div class="modal-body">


            <form id="createprojectform" method="POST" enctype="multipart">
              <div class="input-container">
                <input type="text" id="projectName" name="projectName" required placeholder=" ">
                <label for="projectName">Project Name:</label>
              </div>

              <div class="input-container">
                <textarea id="projectDescription" name="projectDescription" rows="3" placeholder="Describe your project" style="font-size: 10px; color: green;"></textarea>
              </div>

              <div class="input-container">
                <input id="projectFile" type="file" name="projectfile" placeholder=" " multiple>
                <label for="projectFile">Attachments:</label>
              </div>

              <div class="input-container">
                <input type="date" id="startDate" name="startDate" required>
                <label for="startDate">Start Date:</label>
              </div>

              <div class="input-container">
                <input type="date" id="endDate" name="endDate" required>
                <label for="endDate">End Date:</label>
              </div>

              <div class="input-container">
                <select id="projectDepartment" name="projectDepartment" required>
                  <option value="" disabled selected>Select a department</option>
                  <?php include '../../users/admin/fetchdepartements.php'; ?>
                  <!-- Add more departments as needed -->
                </select>
                <label for="projectDepartment">Department:</label>
              </div>

              <div class="input-container">
                <select id="projectStatus" name="projectStatus" required>
                  <option value="planning">Planning</option>
                  <option value="inProgress">In Progress</option>
                  <option value="completed">Completed</option>
                </select>
                <label for="projectStatus">Project Status:</label>
              </div>

              <div class="input-container">
                <select id="projectManager" name="projectManager" required>
                  <option value="" disabled>Select a project manager</option>
                  <!-- Add options dynamically based on employees -->
                  <?php include '../../users/admin/fetchmanagersforproject.php'; ?>
                </select>
                <label for="projectManager">Project Manager:</label>
              </div>

              <div class="input-container">
                <input type="hidden" id="projectCompany_id" name="company_id" value="<?php echo $companyId; ?>" required>
              </div>

              <input type="submit" value="Create Project">
            </form>
          </div>
        </div>
      </div>
      <script>
        document.getElementById('createprojectform').addEventListener('submit', function(e) {
          e.preventDefault();

          var projectformData = new FormData(this);

          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'createproject.php', true);

          xhr.onload = function() {
            if (xhr.status === 200) {
              // Request successful
              alert(xhr.responseText);
              document.getElementById('createprojectform').reset();

              // Handle the response as needed
            } else {
              // Request failed
              console.error('Error:', xhr.status);
            }
          };

          xhr.onerror = function() {
            console.error('Request failed');
          };

          xhr.send(projectformData);
        });
      </script>



      <div id="createVaultModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Upload Vault </h2>
          </div>
          <div class="modal-body">
            <form method="post" id="vaultform" enctype="multipart/form-data">



              <div class="input-container">
                <input type="text" id="asset_name" name="asset_name" required placeholder="">
                <label for="vaultName">Asset Name:</label>
              </div>

              <div class="input-container">
                <textarea id="assest_description" name="asset_description" placeholder="Asset Description"></textarea>
                <label for="Asset Description"></label>
              </div>

              <div class="input-container">
                <input type="file" id="asset_file" name="asset_file[]" multiple>
                <label for="UploadAsset">Upload sensitive files, environment variables, or source code snippets, etc (Optional):</label>
              </div>

              <div class="input-container">
                <select id="asset_access_level" name="asset_access_level" required>
                  <option value="" disabled>Select Access Level</option>
                  <option value="private">Private</option>
                  <option value="team">Team</option>
                  <option value="company">Company-wide</option>
                </select>
                <label for="accesslevel">Select Access Level:</label>
              </div>




              <div class="encryptflex" style="display: flex; flex-direction: row; gap: 5px;">
                <div class="input-container" style="flex: 2;">
                  <input type="text" id="asset_password" name="asset_password" required>
                  <label for="asset_password">Encryption Code:</label>
                </div>

                <div class="buttonflex" style="flex: 1;">
                  <button class="geneencrypt" onclick="xoloencyrptgenerate(); event.preventDefault();" style="width: 100%; padding: 11px 10px; background-color: black; color: white; outline: none; border-radius: 5px; border: none;">Generate Encryption Key with Xolo</button>
                </div>

                <script>
                  function xoloencyrptgenerate() {
                    // Generate a random 256-bit encryption code
                    const encryptionCode = Array.from(crypto.getRandomValues(new Uint8Array(32)))
                      .map(b => b.toString(16).padStart(2, '0'))
                      .join('');

                    // Place the encryption code inside input with id = asset_password
                    document.getElementById('asset_password').value = encryptionCode;

                    return encryptionCode;
                  }
                </script>
              </div>

              <div class="input-container">
                <input type="text" id="asset_tags" name="asset_tags" placeholder="">
                <label for="assettags">Add (comma-separated) tags to help organize your vaults:</label>

              </div>

              <div class="input-container">
                <input type="date" id="asset_expiry" name="asset_expiry">
                <label for="asset_expiry">Set an expiration date for this asset (Optional):</label>

              </div>

              <div class="input-container">
                <input type="hidden" id="vaultcompany_id" name="company_id" value="<?php echo $companyId; ?>" required>
              </div>

              <input type="submit" value="Upload to Vault">
            </form>
          </div>
        </div>
      </div>

      <div id="customizeWidgetModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Customize Widgets</h2>
          </div>
          <div class="modal-body">

            <?php include '../users/admin/getwidgets.php' ?>


          </div>
        </div>
      </div>


      <div id="subscriptionsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Manage Tools </h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'DisplayTools')">Toolkit Subscriptions</button>
            <button class="tablinks" onclick="openTab(event, 'AssignTools')">Assign Tools</button>
            <button class="tablinks" onclick="openTab(event, 'RevokeTools')">Revoke Tools </button>

            <button class="tablinks" onclick="openTab(event, 'DisplayAssigned')">Tool Users </button>

          </div>

          <div id="DisplayTools" class="displaytool tabcontent">
            <?php include '../../users/admin/subtable.php'; ?>
          </div>


          <div id="AssignTools" class="assigntools tabcontent">
            <?php include '../../users/admin/assigntools.php'; ?>

          </div>
          <div id="RevokeTools" class="revoketools tabcontent">
            <?php include '../../users/admin/revokefetch.php'; ?>

          </div>

          <div id="DisplayAssigned" class="displayassigned tabcontent">
            <?php include '../../users/admin/displayedtoolsassigned.php'; ?>

          </div>


        </div>
      </div>

      <div id="eventsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Manage Events </h2>
          </div>
        </div>
      </div>



      <div id="activeUsersModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Manage Employees </h2>
          </div>
        </div>
      </div>

      <div id="automationsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Manage Automations</h2>
          </div>
        </div>
      </div>

      <div id="workflowsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Manage Workflows </h2>
          </div>
          <div class="tab">

            <button class="tablinks" onclick="openTab(event, 'ViewTasks')">All Tasks</button>
            <button class="tablinks" onclick="openTab(event, 'ViewOngoingTasks')">Ongoing Tasks</button>
            <button class="tablinks" onclick="openTab(event, 'ViewCompletedTasks')">Completed Tasks</button>
            <button class="tablinks" onclick="openTab(event, 'ViewPendingTasks')">Pending Tasks</button>


          </div>

          <div id="ViewTasks" class="viewteams tabcontent">
            <?php include '../../users/admin/viewtasks.php'; ?>
          </div>

          <div id="ViewOngoingTasks" class="viewteams tabcontent">
            <?php include '../../users/admin/viewongoingtasks.php'; ?>
          </div>

          <div id="ViewCompletedTasks" class="viewteams tabcontent">
            <?php include '../../users/admin/viewcompletedtasks.php'; ?>
          </div>

          <div id="ViewPendingTasks" class="viewteams tabcontent">
            <?php include '../../users/admin/viewpendingtasks.php'; ?>
          </div>
        </div>

      </div> <!-- main containter -->

      <div id="mentionsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Mentions</h2>
          </div>
        </div>
      </div>

      <div id="sIntelligenceModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Intelligence</h2>
          </div>
        </div>
      </div>

      <div id="insightsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Insights</h2>
          </div>
        </div>
      </div>

      <div id="teamsModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <span class="close">×</span>
            <h2>Manage Teams </h2>
          </div>
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'CreateTeams')">Create Team</button>
            <button class="tablinks" onclick="openTab(event, 'ViewTeams')">View Teams</button>
            <button class="tablinks" onclick="openTab(event, 'UpdateTeams')">Update Teams</button>


          </div>

          <div id="CreateTeams" class="createteams tabcontent">
            <?php include '../../users/admin/createteamform.php'; ?>
          </div>


          <div id="ViewTeams" class="viewteams tabcontent">
            <?php include '../../users/admin/viewteams.php'; ?>
          </div>


          <div id="UpdateTeams" class="updateteams tabcontent">
            <?php
            //include '../../users/admin/updateteamform.php';
            ?>

          </div>
        </div>

      </div> <!-- main containter -->

      <!-- <style>
    .messages{
      display: none;
      position: fixed;
      z-index: 99;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      padding-top: 2.5rem;
      background-color: rgba(0,0,0,0.4);
    }
  </style> -->
      <div id="messages" class="modal">
        <!-- <span onclick="" class="close">×</span>   -->
        <?php
        include '../../users/admin/messages.php'
        ?>
      </div>


      <script>
        function openBankModal() {
          var bankWalletModal = document.getElementById("bankWalletModal");
          bankWalletModal.style.display = "block";
        }

        function openReportsModal() {
          var reportsModal = document.getElementById("reportsModal");
          reportsModal.style.display = "block";
        }

        function openTalentModal() {
          var talentModal = document.getElementById("talentModal");
          talentModal.style.display = "block";
        }

        function openChangeLanguageModal() {
          var changeLanguageModal = document.getElementById("changeLanguageModal");
          changeLanguageModal.style.display = "block";
        }

        function openCreateTaskModal() {
          var createTaskModal = document.getElementById("createTaskModal");
          createTaskModal.style.display = "block";
        }

        function openUpdatesModal() {
          var updatesModal = document.getElementById("updatesModal");
          updatesModal.style.display = "block";
          openUpdatesModal();

        }

        function openActivityLogsModal() {
          var activityLogsModal = document.getElementById("activityLogsModal");
          activityLogsModal.style.display = "block";
          populateLogs();

        }

        function openAlertsModal() {
          var alertsModal = document.getElementById("alertsModal");
          alertsModal.style.display = "block";
          loadAlerts();
        }

        function openKpiModal() {
          var kpiModal = document.getElementById("kpiModal");
          kpiModal.style.display = "block";
        }

        function openAppIntegrationModal() {
          var appIntegrationModal = document.getElementById("appIntegrationModal");
          appIntegrationModal.style.display = "block";
        }

        function openEmployeeModal() {
          var employeeModal = document.getElementById("employeeModal");
          employeeModal.style.display = "block";
        }

        function openProjectModal() {
          var projectModal = document.getElementById("createProjectModal")
          projectModal.style.display = "block";
        }

        function openVaultModal() {
          var vaultModal = document.getElementById("createVaultModal")
          vaultModal.style.display = "block";
        }

        function openWidgetModal() {
          var widgetModal = document.getElementById("customizeWidgetModal")
          widgetModal.style.display = "block";
        }


        function openSubscriptionsModal() {
          var subscriptionsModal = document.getElementById("subscriptionsModal");
          subscriptionsModal.style.display = "block";

        }

        function openEventsModal() {
          eventsModal.style.display = "block";
        }

        function openActiveUsersModal() {
          activeUsersModal.style.display = "block";
        }


        function openAutomationsModal() {
          automationsModal.style.display = "block";
        }

        function openWorkflowsModal() {
          workflowsModal.style.display = "block";
        }

        function openMentionsModal() {
          mentionsModal.style.display = "block";
        }

        function openSIntelligenceModal() {
          sIntelligenceModal.style.display = "block";
        }


        function openInsightsModal() {
          insightsModal.style.display = "block";
        }

        function openTeamsModal() {
          teamsModal.style.display = "block";
        }


        // Get the modals
        var bankWalletModal = document.getElementById("bankWalletModal");
        var reportsModal = document.getElementById("reportsModal");
        var talentModal = document.getElementById("talentModal");
        var changeLanguageModal = document.getElementById("changeLanguageModal");
        var createTaskModal = document.getElementById("createTaskModal");
        var updatesModal = document.getElementById("updatesModal");
        var activityLogsModal = document.getElementById("activityLogsModal");
        var alertsModal = document.getElementById("alertsModal");
        var kpiModal = document.getElementById("kpiModal");
        var appIntegrationModal = document.getElementById("appIntegrationModal");
        var employeeModal = document.getElementById("employeeModal");
        var projectModal = document.getElementById("createProjectModal");
        var vaultModal = document.getElementById("createVaultModal");
        var widgetModal = document.getElementById("customizeWidgetModal");

        var subscriptionsModal = document.getElementById("subscriptionsModal");
        var eventsModal = document.getElementById("eventsModal");
        var activeUsersModal = document.getElementById("activeUsersModal");
        var automationsModal = document.getElementById("automationsModal");
        var workflowsModal = document.getElementById("workflowsModal");
        var mentionsModal = document.getElementById("mentionsModal");
        var sIntelligenceModal = document.getElementById("sIntelligenceModal");
        var insightsModal = document.getElementById("insightsModal");
        var teamsModal = document.getElementById("teamsModal");

        //spans
        var subscriptionsSpan = subscriptionsModal.getElementsByClassName("close")[0];
        var eventsSpan = eventsModal.getElementsByClassName("close")[0];
        var activeUsersSpan = activeUsersModal.getElementsByClassName("close")[0];
        var automationsSpan = automationsModal.getElementsByClassName("close")[0];
        var workflowsSpan = workflowsModal.getElementsByClassName("close")[0];
        var mentionsSpan = mentionsModal.getElementsByClassName("close")[0];
        var sIntelligenceSpan = sIntelligenceModal.getElementsByClassName("close")[0];
        var insightsSpan = insightsModal.getElementsByClassName("close")[0];
        var teamsSpan = teamsModal.getElementsByClassName("close")[0];

        var bankWalletSpan = bankWalletModal.getElementsByClassName("close")[0];
        var reportsSpan = reportsModal.getElementsByClassName("close")[0];
        var talentSpan = talentModal.getElementsByClassName("close")[0];
        var changeLanguageSpan = changeLanguageModal.getElementsByClassName("close")[0];
        var createTaskSpan = createTaskModal.getElementsByClassName("close")[0];
        var updatesSpan = updatesModal.getElementsByClassName("close")[0];
        var activityLogsSpan = activityLogsModal.getElementsByClassName("close")[0];
        var alertsSpan = alertsModal.getElementsByClassName("close")[0];
        var kpiSpan = kpiModal.getElementsByClassName("close")[0];
        var appIntegrationSpan = appIntegrationModal.getElementsByClassName("close")[0];
        var employeeSpan = employeeModal.getElementsByClassName("close")[0];
        var projectSpan = projectModal.getElementsByClassName("close")[0];
        var vaultSpan = vaultModal.getElementsByClassName("close")[0];
        var widgetSpan = widgetModal.getElementsByClassName("close")[0];


        // When the user clicks on <span> (x), close the modal
        subscriptionsSpan.onclick = function() {
          subscriptionsModal.style.display = "none";
        }

        eventsSpan.onclick = function() {
          eventsModal.style.display = "none";
        }

        activeUsersSpan.onclick = function() {
          activeUsersModal.style.display = "none";
        }

        automationsSpan.onclick = function() {
          automationsModal.style.display = "none";
        }

        workflowsSpan.onclick = function() {
          workflowsModal.style.display = "none";
        }

        mentionsSpan.onclick = function() {
          mentionsModal.style.display = "none";
        }


        sIntelligenceSpan.onclick = function() {
          sIntelligenceModal.style.display = "none";
        }

        insightsSpan.onclick = function() {
          insightsModal.style.display = "none";
        }

        teamsSpan.onclick = function() {
          teamsModal.style.display = "none";
        }

        // When the user clicks on <span> (x), close the respective modal
        bankWalletSpan.onclick = function() {
          bankWalletModal.style.display = "none";
        }

        reportsSpan.onclick = function() {
          reportsModal.style.display = "none";
        }

        talentSpan.onclick = function() {
          talentModal.style.display = "none";
        }

        changeLanguageSpan.onclick = function() {
          changeLanguageModal.style.display = "none";
        }

        createTaskSpan.onclick = function() {
          createTaskModal.style.display = "none";
        }

        updatesSpan.onclick = function() {
          updatesModal.style.display = "none";
        }

        activityLogsSpan.onclick = function() {
          activityLogsModal.style.display = "none";
        }

        alertsSpan.onclick = function() {
          alertsModal.style.display = "none";
        }

        kpiSpan.onclick = function() {
          kpiModal.style.display = "none";
        }

        appIntegrationSpan.onclick = function() {
          appIntegrationModal.style.display = "none";
        }

        employeeSpan.onclick = function() {
          employeeModal.style.display = "none";
        }

        projectSpan.onclick = function() {
          projectModal.style.display = "none";
        }

        vaultSpan.onclick = function() {
          vaultModal.style.display = "none";
        }

        widgetSpan.onclick = function() {
          widgetModal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modals, close them
        window.onclick = function(event) {
          if (event.target == bankWalletModal) {
            bankWalletModal.style.display = "none";
          }
          if (event.target == reportsModal) {
            reportsModal.style.display = "none";
          }
          if (event.target == talentModal) {
            talentModal.style.display = "none";
          }
          if (event.target == changeLanguageModal) {
            changeLanguageModal.style.display = "none";
          }
          if (event.target == createTaskModal) {
            createTaskModal.style.display = "none";
          }
          if (event.target == updatesModal) {
            updatesModal.style.display = "none";
          }
          if (event.target == activityLogsModal) {
            activityLogsModal.style.display = "none";
          }
          if (event.target == alertsModal) {
            alertsModal.style.display = "none";
          }
          if (event.target == kpiModal) {
            kpiModal.style.display = "none";
          }
          if (event.target == appIntegrationModal) {
            appIntegrationModal.style.display = "none";
          }
          if (event.target == employeeModal) {
            employeeModal.style.display = "none";
          }

          if (event.target == projectModal) {
            projectModal.style.display = "none";
          }

          if (event.target == vaultModal) {
            vaultModal.style.display = "none";
          }

          if (event.target == widgetModal) {
            widgetModal.style.display = "none";
          }

          if (event.target == subscriptionsModal) {
            subscriptionsModal.style.display = "none";
          }
          if (event.target == eventsModal) {
            eventsModal.style.display = "none";
          }
          if (event.target == activeUsersModal) {
            activeUsersModal.style.display = "none";
          }

          if (event.target == automationsModal) {
            automationsModal.style.display = "none";
          }
          if (event.target == workflowsModal) {
            workflowsModal.style.display = "none";
          }
          if (event.target == mentionsModal) {
            mentionsModal.style.display = "none";
          }

          if (event.target == sIntelligenceModal) {
            sIntelligenceModal.style.display = "none";
          }
          if (event.target == insightsModal) {
            insightsModal.style.display = "none";
          }
          if (event.target == teamsModal) {
            teamsModal.style.display = "none";
          }
        }


        function openTab(evt, tabName) {
          var i, tabcontent, tablinks;
          tabcontent = document.getElementsByClassName("tabcontent");
          for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
          }
          tablinks = document.getElementsByClassName("tablinks");
          for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
          }
          document.getElementById(tabName).style.display = "block";
          evt.currentTarget.className += " active";
        }

        function openKpiTab(evt, tabName) {
          var i, tabcontent, tablinks;
          tabcontent = document.getElementsByClassName("tabcontent");
          for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
          }
          tablinks = document.getElementsByClassName("tablinks");
          for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
          }
          document.getElementById(tabName).style.display = "block";
          evt.currentTarget.className += " active";
        }
      </script>

      <script>
        function showIntegrationSteps(tool) {
          var integrationSteps = document.getElementById("integrationSteps");
          var integrationTitle = document.getElementById("integrationTitle");
          var integrationContent = document.getElementById("integrationContent");

          integrationTitle.textContent = tool + " Integration Steps";

          switch (tool) {
            case "Stripe":
              integrationContent.innerHTML = `
        <form id="stripeIntegrationForm" class="styled-form">
          <div class="form-group">
            <label for="stripeApiKey">Stripe API Key:</label>
            <input type="password" id="stripeApiKey" name="stripeApiKey" required>
          </div>
          <div class="form-group">
            <label for="stripeWebhookSecret">Webhook Secret:</label>
            <input type="password" id="stripeWebhookSecret" name="stripeWebhookSecret" required>
          </div>
          <button type="submit" class="btn btn-primary">Connect Stripe</button>
        </form>
      `;
              break;
            case "Plaid":
              integrationContent.innerHTML = `
        <form id="plaidIntegrationForm" class="styled-form">
          <div class="form-group">
            <label for="plaidClientId">Plaid Client ID:</label>
            <input type="text" id="plaidClientId" name="plaidClientId" required>
          </div>
          <div class="form-group">
            <label for="plaidSecret">Plaid Secret:</label>
            <input type="password" id="plaidSecret" name="plaidSecret" required>
          </div>
          <div class="form-group">
            <label for="plaidEnv">Environment:</label>
            <select id="plaidEnv" name="plaidEnv" required>
              <option value="sandbox">Sandbox</option>
              <option value="development">Development</option>
              <option value="production">Production</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Connect Plaid</button>
        </form>
      `;
              break;
            case "GitHub":
              integrationContent.innerHTML = `
        <form id="githubIntegrationForm" class="styled-form">
          <div class="form-group">
            <label for="githubToken">GitHub Access Token:</label>
            <input type="password" id="githubToken" name="githubToken" required>
          </div>
          <div class="form-group">
            <label for="githubRepo">Repository Name:</label>
            <input type="text" id="githubRepo" name="githubRepo" required>
          </div>
          <button type="submit" class="btn btn-primary">Connect GitHub</button>
        </form>
      `;
              break;
            case "Zapier":
              integrationContent.innerHTML = `
        <p>To integrate Zapier, follow these steps:</p>
        <ol>
          <li>Log in to your Zapier account</li>
          <li>Create a new Zap</li>
          <li>Choose our app as the trigger</li>
          <li>Follow the prompts to connect your account</li>
          <li>Test and turn on your Zap</li>
        </ol>
      `;
              break;
            case "RiskTools":
              integrationContent.innerHTML = `
        <p>To integrate Risk Management Tools, please contact our support team for a personalized setup process.</p>
        <button onclick="contactSupport()" class="btn btn-primary">Contact Support</button>
      `;
              break;
          }

          integrationSteps.style.display = "block";
        }


        function contactSupport() {
          // Implement contact support functionality
          alert("Support team will contact you shortly.");
        }
      </script>

      <script>
        // Function to load alerts dynamically from the server
        function loadAlerts() {
          var alertsList = document.getElementById("alertsList");
          alertsList.innerHTML = ""; // Clear existing alerts

          // Fetch alerts from the server using Fetch API
          fetch('getalerts.php') // Replace with the path to your PHP file
            .then(response => response.json()) // Parse the JSON response
            .then(alerts => {
              // Loop through the alerts and display them
              alerts.forEach(function(alert) {
                var alertItem = document.createElement("div");
                alertItem.className = "alert-item";
                alertItem.innerHTML = `
            <h3>${alert.title}</h3>
            <p>${alert.message}</p>
            <span class="alert-date">${alert.date}</span>
          `;
                alertsList.appendChild(alertItem);
              });
            })
            .catch(error => {
              console.error('Error fetching alerts:', error);
            });
        }

        // Function to open the alerts modal
        function openAlertsModal() {
          loadAlerts(); // Load alerts when the modal is opened
          document.getElementById("alertsModal").style.display = "block"; // Display the modal (you can customize this)
        }

        // Close the modal (this is optional)
        function closeAlertsModal() {
          document.getElementById("alertsModal").style.display = "none";
        }

        // Add click event to the alerts link
        document.querySelector(".alerts-link").addEventListener("click", function(e) {
          e.preventDefault();
          openAlertsModal();
        });
      </script>

      <script>
        function filterLogs() {
          // Implement log filtering logic here
          console.log("Filtering logs...");
        }

        function previousPage() {
          // Implement previous page logic here
          console.log("Going to previous page...");
        }

        function nextPage() {
          // Implement next page logic here
          console.log("Going to next page...");
        }

        // Function to populate logs (example)
        // Function to populate logs
        function populateLogs() {
          var logTableBody = document.getElementById("logTableBody");
          // Clear existing logs
          logTableBody.innerHTML = "";

          // Fetch logs from get_alerts.php
          fetch('getlogs.php')
            .then(response => response.json())
            .then(logs => {
              logs.forEach(function(log) {
                var row = logTableBody.insertRow();
                row.insertCell(0).textContent = log.created_at;
                row.insertCell(1).textContent = log.type;
                row.insertCell(2).textContent = log.user;
                row.insertCell(3).textContent = log.action;
                row.insertCell(4).textContent = log.details;
              });
            })
            .catch(error => console.error('Error fetching logs:', error));
        }
      </script>
      <script>
        // Function to handle talent suggestion form submission
        document.getElementById("suggestTalentForm").onsubmit = function(event) {
          event.preventDefault();
          // Here you would typically send this data to your backend
          // For now, we'll just log it to the console
          console.log("Talent suggested:", {
            name: document.getElementById("talentName").value,
            skills: document.getElementById("talentSkills").value,
            experience: document.getElementById("talentExperience").value,
            notes: document.getElementById("talentNotes").value,
            recruiter: document.getElementById("recruiterName").value
          });
          alert("Talent suggestion sent to recruiter!");
          this.reset();
        };
      </script>
      <script>
        // Handle form submission
        document.getElementById("createTaskForm").onsubmit = function(event) {
          event.preventDefault();
          // Add code here to handle the task creation
          console.log("Task created");
          createTaskModal.style.display = "none";
        }
      </script>
      <script>
        // Get the button that opens the modal
        var changeLanguageBtn = document.querySelector(".change-language-button");

        // Get the <span> element that closes the modal
        var closeChangeLanguage = changeLanguageModal.querySelector(".close");

        // When the user clicks the button, open the modal 
        changeLanguageBtn.onclick = function() {
          changeLanguageModal.style.display = "block";
          // Set the current language as selected
          var currentLanguage = document.documentElement.lang || 'en';
          updateLanguageList(currentLanguage);
        }

        // Function to update the language list
        function updateLanguageList(currentLanguage) {
          var languageList = document.getElementById("languageList");
          var buttons = languageList.querySelectorAll(".choose-language");
          buttons.forEach(function(button) {
            if (button.dataset.lang === currentLanguage) {
              button.style.display = "none";
            } else {
              button.style.display = "inline-block";
            }
          });
        }

        // Handle language selection
        document.getElementById("languageList").addEventListener("click", function(event) {
          if (event.target.classList.contains("choose-language")) {
            var selectedLanguage = event.target.dataset.lang;
            var previousLanguage = document.documentElement.lang || 'en';
            console.log("Language changed from: " + previousLanguage + " to: " + selectedLanguage);
            document.documentElement.lang = selectedLanguage;
            changeLanguageModal.style.display = "none";
            updateLanguageList(selectedLanguage);
          }
        });
      </script>
      <script>
        // Function to create wallet items (example data)
        function createWalletItems() {
          const walletList = document.getElementById("walletList");
          const currencies = ["Naira", "USD", "Euro", "GBP"];

          currencies.forEach(currency => {
            const walletItem = document.createElement("div");
            walletItem.className = "wallet-item";
            walletItem.innerHTML = `
      <h4>${currency} Wallet</h4>
      <p class="balance">Balance: ${getRandomBalance(currency)}</p>
      <p>Debits: ${getRandomAmount(currency)}</p>
      <p>Credits: ${getRandomAmount(currency)}</p>
      <p class="percentage">In-Out: ${getRandomPercentage()}%</p>
    `;
            walletList.appendChild(walletItem);
          });
        }

        function getRandomBalance(currency) {
          const amount = Math.floor(Math.random() * 10000) + 1000;
          return formatCurrency(amount, currency);
        }

        function getRandomAmount(currency) {
          const amount = Math.floor(Math.random() * 5000) + 100;
          return formatCurrency(amount, currency);
        }

        function getRandomPercentage() {
          return (Math.random() * 10 + 1).toFixed(2);
        }

        function formatCurrency(amount, currency) {
          switch (currency) {
            case "Naira":
              return `₦${amount.toLocaleString()}`;
            case "USD":
              return `$${amount.toLocaleString()}`;
            case "Euro":
              return `€${amount.toLocaleString()}`;
            case "GBP":
              return `£${amount.toLocaleString()}`;
            default:
              return `${amount.toLocaleString()} ${currency}`;
          }
        }

        // Call this function when the modal is opened
        createWalletItems();
      </script>





      <script>
        // Object to hold all CKEditor instances
        let editorInstances = {};

        // Create CKEditor for each textarea and store instance by ID
        document.querySelectorAll('textarea').forEach(textarea => {
          ClassicEditor
            .create(textarea)
            .then(editor => {
              // Store the editor instance in the object using the textarea's id as the key
              editorInstances[textarea.id] = editor;

              // Apply custom styles for font size and color after editor is created
              editor.editing.view.change(writer => {
                writer.setStyle('font-size', '14px', editor.editing.view.document.getRoot()); // Set font size
                writer.setStyle('color', 'black', editor.editing.view.document.getRoot()); // Set text color
              });
            })
            .catch(error => {
              console.error(error);
            });
        });
      </script>
      <script>
        const loaderOverlay = document.createElement('div');
        loaderOverlay.id = 'loaderModal';
        loaderOverlay.innerHTML = `
    <div class="modal-content-custom">
        <div id="loader-custom"></div>
        <div id="parseStatus-custom" style="display: none; text-align: center; margin-top: 10px;"></div>
    </div>
`;
        document.body.appendChild(loaderOverlay);

        // Create and add CSS for the modal and loader
        const style = document.createElement('style');
        style.textContent = `
    #loaderModal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content-custom {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    #loader-custom {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
        document.head.appendChild(style);

        // Show modal function
        function showModal() {
          loaderOverlay.style.display = 'block';
        }

        function xologenerate() {
          showModal(); // Show the loading modal

          var xhr = new XMLHttpRequest();
          xhr.open("POST", "generatetask.php", true);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
              var response = JSON.parse(this.responseText);
              console.log(response);
              if (response.task_title && response.task_description) {
                document.getElementById("taskTitle").value = response.task_title;

                // Use the CKEditor instance for taskDescription to set the task description
                if (editorInstances['taskDescription']) {
                  editorInstances['taskDescription'].setData(response.task_description);
                } else {
                  console.error("Editor instance not found for taskDescription");
                }
              } else {
                alert("Failed to generate task. Please try again.");
              }
              loaderOverlay.style.display = 'none'; // Hide the loading modal when done
            }
          };
          var projectId = document.getElementById("project_id").value; // Assuming you have a hidden input with project ID
          xhr.send("project_id=" + encodeURIComponent(projectId));
        }
      </script>


      <script>
        function toggleLearn() {
          document.querySelector('.indexpage').style.display = "none";
          document.querySelector('.learn').style.display = "block";
          document.querySelector('.categories').style.display = "none";
          document.querySelector('.notifications').style.display = "none";
          document.querySelector('.messages').style.display = "none";
          document.querySelector('.wallets').style.display = "none";
          document.querySelector('.support').style.display = "none";
        }

        function toggleCategories() {
          document.querySelector('.indexpage').style.display = "none";
          document.querySelector('.learn').style.display = "none";
          document.querySelector('.categories').style.display = "block";
          document.querySelector('.notifications').style.display = "none";
          document.querySelector('.messages').style.display = "none";
          document.querySelector('.wallets').style.display = "none";
          document.querySelector('.support').style.display = "none";
        }

        function toggleNotifications() {
          document.querySelector('.indexpage').style.display = "none";
          document.querySelector('.learn').style.display = "none";
          document.querySelector('.categories').style.display = "none";
          document.querySelector('.notifications').style.display = "block";
          document.querySelector('.messages').style.display = "none";
          document.querySelector('.wallets').style.display = "none";
          document.querySelector('.support').style.display = "none";
        }

        function toggleMessages() {
          // document.querySelector('.indexpage').style.display = "none";
          // document.querySelector('.learn').style.display = "none";
          // document.querySelector('.categories').style.display = "none";
          // document.querySelector('.notifications').style.display = "none";
          // document.querySelector('.messages').style.display = "block";
          // document.querySelector('.wallets').style.display = "none";
          // document.querySelector('.support').style.display = "none";

          var messagesModal = document.getElementById("messages");
          messagesModal.style.display = "block";
          console.log("messsages clicked");
        }

        function toggleWallet() {
          document.querySelector('.indexpage').style.display = "none";
          document.querySelector('.learn').style.display = "none";
          document.querySelector('.categories').style.display = "none";
          document.querySelector('.notifications').style.display = "none";
          document.querySelector('.messages').style.display = "none";
          document.querySelector('.wallets').style.display = "block";
          document.querySelector('.support').style.display = "none";
        }

        function toggleSupport() {
          document.querySelector('.indexpage').style.display = "none";
          document.querySelector('.learn').style.display = "none";
          document.querySelector('.categories').style.display = "none";
          document.querySelector('.notifications').style.display = "none";
          document.querySelector('.messages').style.display = "none";
          document.querySelector('.wallets').style.display = "none";
          document.querySelector('.support').style.display = "block";
        }

        function toggleIndex() {
          document.querySelector('.indexpage').style.display = "block";
          document.querySelector('.learn').style.display = "none";
          document.querySelector('.categories').style.display = "none";
          document.querySelector('.notifications').style.display = "none";
          document.querySelector('.messages').style.display = "none";
          document.querySelector('.wallets').style.display = "none";
          document.querySelector('.support').style.display = "none";
        }
      </script>

      <script>
        function loadTeamData(teamId) {
          var xhr = new XMLHttpRequest();
          xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              try {
                var teamData = JSON.parse(this.responseText);
                document.getElementById('team_name').value = teamData.team_name;
                document.getElementById('team_description').value = teamData.team_description;
                document.getElementById('team_leader_id').value = teamData.team_leader_id;
                document.getElementById('project_id').value = teamData.project_id;
                console.log(teamData);

                var teamMembersSelect = document.getElementById('team_members');
                teamData.team_members.forEach(function(memberId) {
                  for (var i = 0; i < teamMembersSelect.options.length; i++) {
                    if (teamMembersSelect.options[i].value == memberId) {
                      teamMembersSelect.options[i].selected = true;
                      break;
                    }
                  }
                });

                // Enable all form elements
                var formElements = document.getElementById('updateteamform').elements;
                for (var i = 0; i < formElements.length; i++) {
                  formElements[i].disabled = false;
                }

                updateTeamMembers();
              } catch (e) {
                console.error("Error parsing JSON:", e);
              }
            }
          };
          xhr.open("GET", "getteamdata.php?team_id=" + encodeURIComponent(teamId), true);
          xhr.send();
        }

        function checkTeamName(teamName) {
          var xhr = new XMLHttpRequest();
          xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              if (this.responseText === "exists") {
                document.getElementById("team_name_error").style.display = "block";
                document.getElementById("team_name").setCustomValidity("Team name already exists");
              } else {
                document.getElementById("team_name_error").style.display = "none";
                document.getElementById("team_name").setCustomValidity("");
              }
            }
          };
          xhr.open("GET", "check_team_name.php?team_name=" + encodeURIComponent(teamName) + "&company_id=<?php echo $companyId; ?>", true);
          xhr.send();
        }

        function updateTeamMembers() {
          var teamLeaderSelect = document.getElementById('team_leader_id');
          var teamMembersSelect = document.getElementById('team_members');
          var selectedLeaderId = teamLeaderSelect.value;

          // Store currently selected members
          var selectedMembers = Array.from(teamMembersSelect.selectedOptions).map(option => option.value);

          // Enable all options
          for (var i = 0; i < teamMembersSelect.options.length; i++) {
            teamMembersSelect.options[i].disabled = false;
          }

          // If a team leader is selected, disable that option in team members
          if (selectedLeaderId) {
            for (var i = 0; i < teamMembersSelect.options.length; i++) {
              if (teamMembersSelect.options[i].value === selectedLeaderId) {
                teamMembersSelect.options[i].disabled = true;
                teamMembersSelect.options[i].selected = false;
                break;
              }
            }
          }

          // Reselect previously selected members (except the new leader)
          for (var i = 0; i < teamMembersSelect.options.length; i++) {
            if (selectedMembers.includes(teamMembersSelect.options[i].value) && teamMembersSelect.options[i].value !== selectedLeaderId) {
              teamMembersSelect.options[i].selected = true;
            }
          }
        }

        document.getElementById('updateteamform').addEventListener('submit', function(e) {
          e.preventDefault();

          var updateTeamData = new FormData(this);

          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'updateteams.php', true);

          xhr.onload = function() {
            if (xhr.status === 200) {
              // Request successful
              alert(xhr.responseText);
              // Handle the response as needed
            } else {
              // Request failed
              console.error('Error:', xhr.status);
            }
          };

          xhr.onerror = function() {
            console.error('Request failed');
          };

          xhr.send(updateTeamData);
        });
      </script>

</body>

</html>