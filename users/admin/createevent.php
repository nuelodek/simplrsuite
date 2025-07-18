  <?php
  include '../../sql_queries/db.php';

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      // Retrieve form data
      $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
      $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
      $start_datetime = mysqli_real_escape_string($conn, $_POST['start_datetime']);
      $end_datetime = mysqli_real_escape_string($conn, $_POST['end_datetime']);
      $agenda = mysqli_real_escape_string($conn, $_POST['agenda']);
      $event_type = mysqli_real_escape_string($conn, $_POST['event_type']);
      $meetup_venue = mysqli_real_escape_string($conn, $_POST['meetup_venue']);
      $meet_link = mysqli_real_escape_string($conn, $_POST['meet_link']);
      $event_description = mysqli_real_escape_string($conn, $_POST['event_description']);
      $organizer = mysqli_real_escape_string($conn, $_POST['organizer']);
      $company_id = mysqli_real_escape_string($conn, $_POST['company_id']);

      // Handle attendees (multiple selection)
      $attendees = isset($_POST['attendees']) ? implode(',', $_POST['attendees']) : '';

      // Handle file upload for event images
      $image_paths = [];
      if(isset($_FILES['event_image'])) {
          $upload_dir = '../../uploads/event_images/';
          foreach($_FILES['event_image']['tmp_name'] as $key => $tmp_name) {
              $file_name = $_FILES['event_image']['name'][$key];
              $file_path = $upload_dir . uniqid() . '_' . $file_name;
              if(move_uploaded_file($tmp_name, $file_path)) {
                  $image_paths[] = $file_path;
              }
          }
      }
      $event_images = implode(',', $image_paths);

      // Prepare SQL statement
      $sql = "INSERT INTO events (event_name, event_date, start_datetime, end_datetime, agenda, event_type, meetup_venue, meet_link, attendees, event_description, event_images, organizer, company_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "ssssssssssssi", $event_name, $event_date, $start_datetime, $end_datetime, $agenda, $event_type, $meetup_venue, $meet_link, $attendees, $event_description, $event_images, $organizer, $company_id);

      // Execute the statement
      if (mysqli_stmt_execute($stmt)) {
          echo "Event created successfully";
      } else {
          echo "Error: " . mysqli_error($conn);
      }

      // Close statement
      mysqli_stmt_close($stmt);
  }

  // Close connection
  mysqli_close($conn);
  ?>
