  <?php
  include '../../sql_queries/db.php';

  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
      $reminder_message = mysqli_real_escape_string($conn, $_POST['reminder_message']);

      // Fetch event details
      $event_sql = "SELECT id, event_name, event_date, start_datetime, end_datetime, agenda, event_type, meetup_venue, meet_link, attendees, event_description, event_images, organizer, company_id, created_at FROM events WHERE id = ?";
      $event_stmt = mysqli_prepare($conn, $event_sql);
      mysqli_stmt_bind_param($event_stmt, "i", $event_id);
      mysqli_stmt_execute($event_stmt);
      $event_result = mysqli_stmt_get_result($event_stmt);
      $event = mysqli_fetch_assoc($event_result);

      if ($event) {
          $attendees = explode(',', $event['attendees']);
        
          // Fetch attendees' email addresses
          $attendees_sql = "SELECT email FROM employees WHERE id IN (" . implode(',', array_fill(0, count($attendees), '?')) . ")";
          $attendees_stmt = mysqli_prepare($conn, $attendees_sql);
          mysqli_stmt_bind_param($attendees_stmt, str_repeat('i', count($attendees)), ...$attendees);
          mysqli_stmt_execute($attendees_stmt);
          $attendees_result = mysqli_stmt_get_result($attendees_stmt);

          // Send reminder emails
          while ($attendee = mysqli_fetch_assoc($attendees_result)) {
              $to = $attendee['email'];
              $subject = "Reminder: " . $event['event_name'];
              $message = $reminder_message;
              $headers = "From: noreply@example.com";

              if (mail($to, $subject, $message, $headers)) {
                  echo "Reminder sent successfully to " . $attendee['email'] . "<br>";
              } else {
                  echo "Failed to send reminder to " . $attendee['email'] . "<br>";
              }
          }

          mysqli_stmt_close($attendees_stmt);
      } else {
          echo "Event not found.";
      }

      mysqli_stmt_close($event_stmt);
  }

  // Fetch all events for the dropdown
  $events_sql = "SELECT id, event_name, event_date, start_datetime, end_datetime, agenda, event_type, meetup_venue, meet_link, attendees, event_description, event_images, organizer, company_id, created_at FROM events WHERE company_id = ?";
  $events_stmt = mysqli_prepare($conn, $events_sql);
  mysqli_stmt_bind_param($events_stmt, "i", $companyId);
  mysqli_stmt_execute($events_stmt);
  $events_result = mysqli_stmt_get_result($events_stmt);

  if (mysqli_num_rows($events_result) == 0) {
      echo "You have no events for your organization";
  } else {
  ?>

  <form method="POST" action="" class="customizeforms">
        <div class="input-container">
            <select id="event_id" name="event_id" required>
                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                    <option value="<?php echo $event['id']; ?>"><?php echo $event['event_name']; ?></option>
                <?php endwhile; ?>
            </select>
            <label for="event_id">Select Event:</label>
        </div>

        <div class="input-container">
            <textarea id="reminder_message" name="reminder_message" rows="4" required placeholder="Reminder Message"></textarea>
        </div>

        <input type="submit" value="Send Reminder">
    </form>
  <?php
  }
  mysqli_stmt_close($events_stmt);
  mysqli_close($conn);
  ?>
