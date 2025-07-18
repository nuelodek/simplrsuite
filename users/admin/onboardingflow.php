<div class="wedontknow" style="display: flex; justify-content: space-between; margin-top: 20px;">

<div class="onboarding-container" style="width: 40%; background-color: #e6f2ff; padding: 20px; margin-right: 10px; margin-left: 20px; border-radius: 5px; display: flex; flex-direction: column; justify-content: center; align-items: center; font-family: Arial, sans-serif; font-size: 14px; border: 2px solid #4a90e2;">
    <img src="https://images.pexels.com/photos/697509/pexels-photo-697509.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Onboarding Image" style="width: 60px; height: 60px; margin-bottom: 15px; border-radius: 50%;">
   <p style="font-weight: 600; margin-bottom: 10px;"> Welcome, Admin </p>
   <div class="progress-container">
  <div class="progress-fill" style="width: 75%;">
    <span class="progress-text">Your company's profile is 75% complete</span>
  </div>
</div>

<div class="additional-info" style="width: 100%; margin-top: 15px; color: #666;">
    <h3 style="font-size: 16px; margin-bottom: 10px; color: #666;">Next Steps</h3>
    <ul style="padding-left: 20px; margin: 0; color: #666;">
      <?php
      include '../../sql_queries/db.php';

      // Fetch user and company data

      $userQuery = "SELECT status FROM users WHERE company_id = ?";
      $userStmt = $conn->prepare($userQuery);
      $userStmt->bind_param("s", $companyId);
      $userStmt->execute();
      $userResult = $userStmt->get_result();
      $user = $userResult->fetch_assoc();

          $companyQuery = "SELECT 
              (SELECT COUNT(*) > 0 FROM toolkit_subscriptions WHERE company_id = ?) AS has_paid_tools,
              (SELECT COUNT(*) > 0 FROM departments WHERE company_id = ?) AS has_departments,
              (SELECT COUNT(*) > 0 FROM manager_auth WHERE company_id = ?) AS has_managers,
              (SELECT COUNT(*) > 0 FROM employees_auth WHERE company_id = ?) AS has_employees";
          $companyStmt = $conn->prepare($companyQuery);
          $companyStmt->bind_param("iiii", $companyId, $companyId, $companyId, $companyId);
          $companyStmt->execute();
          $companyResult = $companyStmt->get_result();
          $company = $companyResult->fetch_assoc();
      ?>

      <li style="margin-bottom: 5px;"><?php echo ($user['status'] ? '<del>' : ''); ?>Verify your company's email<?php echo ($user['status'] ? '</del>' : ''); ?></li>
      <li style="margin-bottom: 5px;"><?php echo ($company['has_paid_tools'] ? '<del>' : ''); ?>Subscribe to your toolkits<?php echo ($company['has_paid_tools'] ? '</del>' : ''); ?></li>
      <li style="margin-bottom: 5px;"><?php echo ($company['has_departments'] ? '<del>' : ''); ?>Create various departments<?php echo ($company['has_departments'] ? '</del>' : ''); ?></li>
      <li style="margin-bottom: 5px;"><?php echo (($company['has_managers'] && $company['has_employees']) ? '<del>' : ''); ?>Add managers and invite employees<?php echo (($company['has_managers'] && $company['has_employees']) ? '</del>' : ''); ?></li>
  </ul>
  
    </div>
  
  
     <button class="onboarding-button" style="background-color: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 12px; margin-top: 10px;"> Complete Your Onboarding</button>
  <div> <small> Chat with support <a href="https://wa.me/+2349151294786">here</a> or take a <a href="#">quick survey</a> </small> </div>

</div>



