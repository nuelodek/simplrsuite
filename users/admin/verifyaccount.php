  <?php
  session_start();
  require_once '../../sql_queries/db.php';
  require_once '../../vendor/autoload.php';
  require_once '../../users/admin/sentryinitialize.php';



  
  if (!isset($_SESSION['email'])) {
      header('Location: login.php');
      exit();
  }

  // Generate verification token
  function generateToken($length = 32) {
      return bin2hex(random_bytes($length));
  }

  $email = $_SESSION['email'];
  $token = generateToken();

  // Update user with verification token
  $stmt = $conn->prepare("UPDATE users SET token = ?, is_active = 0 WHERE email = ?");
  $stmt->bind_param("ss", $token, $email);

  if ($stmt->execute()) {
      // Initialize Sentry
      $initializer = new SentryInitializer();
      $initializer->initialize();
    
      // Send verification email
      $to = $email;
      $subject = "Verify Your Account";
      $verification_link = "https://yourdomain.com/verify.php?token=" . $token;
    
      $message = "
      <html>
      <head>
          <title>Account Verification</title>
      </head>
      <body>
          <h2>Welcome to Our Platform</h2>
          <p>Please click the link below to verify your account:</p>
          <a href='{$verification_link}'>{$verification_link}</a>
          <p>If you didn't create this account, please ignore this email.</p>
      </body>
      </html>
      ";
    
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      $headers .= 'From: noreply@yourdomain.com' . "\r\n";
    
      if(mail($to, $subject, $message, $headers)) {
          echo '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: #f0f0f0;">
              <img src="email-sent.png" alt="Email Sent" style="width: 64px; height: 64px;">
              <p style="font-size: 18px; color: #4CAF50; margin-top: 10px;">Verification email has been sent. Please check your inbox!</p>
          </div>';
          echo '<script>
              setTimeout(function() {
                  window.location.href = "login.php";
              }, 3000);
          </script>';
      } else {
          \Sentry\captureMessage('Failed to send verification email to ' . $email);
          echo '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: #ffebee;">
              <p style="font-size: 18px; color: #f44336; margin: 0;">Error sending verification email. Please try again.</p>
              <form method="POST" style="margin-top: 20px;">
                  <input type="hidden" name="resend_email" value="1">
                  <button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Resend Verification Email</button>
              </form>
          </div>';
      }
  } else {
      \Sentry\captureException(new Exception($stmt->error));
      echo '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: #ffebee;">
          <p style="font-size: 18px; color: #f44336; margin: 0;">Error: ' . $stmt->error . '</p>
      </div>';
  }

  $stmt->close();
  $conn->close();
  ?>