<?php

include '../../sql_queries/db.php';

?>
        
        
        <form method="post" id="createeventform" class="customizeforms" enctype="multipart/form-data">
        <div class="input-container">
            <input type="text" id="event_name" name="event_name" required placeholder=" ">
            <label for="event_name">Event Name:</label>
        </div>
          <div class="input-container">
              <input type="date" id="event_date" name="event_date" required placeholder=" ">
              <label for="event_date">Event Date:</label>
          </div>
            <div class="input-container">

                <input type="datetime-local" id="start_datetime" name="start_datetime" required placeholder=" " onchange="updateEndDateTime()" min="">
                <label for="start_datetime">Start Date and Time:</label>
            </div>

            <div class="input-container">
                <input type="datetime-local" id="end_datetime" name="end_datetime" required placeholder=" " disabled>
                <label for="end_datetime">End Date and Time:</label>
            </div>

            <script>
                function setMinStartDateTime() {
                    const now = new Date();
                    now.setMinutes(now.getMinutes() + 1);
                    const minStartDateTime = now.toISOString().slice(0, 16);
                    document.getElementById('start_datetime').min = minStartDateTime;
                }

                function updateEndDateTime() {
                    const startDateTime = document.getElementById('start_datetime').value;
                    const endDateTimeInput = document.getElementById('end_datetime');
                
                    if (startDateTime) {
                        endDateTimeInput.disabled = false;
                    
                        // Set minimum end datetime to 1 minute after start datetime
                        const minEndDateTime = new Date(startDateTime);
                        minEndDateTime.setMinutes(minEndDateTime.getMinutes() + 1);
                    
                        const minEndDateTimeString = minEndDateTime.toISOString().slice(0, 16);
                        endDateTimeInput.min = minEndDateTimeString;
                    
                        // If current end datetime is earlier than new minimum, update it
                        if (endDateTimeInput.value && endDateTimeInput.value < minEndDateTimeString) {
                            endDateTimeInput.value = minEndDateTimeString;
                        }
                    } else {
                        endDateTimeInput.disabled = true;
                        endDateTimeInput.value = '';
                    }
                }

                // Set min attribute for start_datetime on page load
                setMinStartDateTime();
                // Update min attribute every minute
                setInterval(setMinStartDateTime, 60000);
            </script>

        <div class="input-container">
            <textarea id="agenda" name="agenda" rows="4" placeholder="Agenda of meeting"></textarea>
        </div>

        <div class="input-container">
            <select id="event_type" name="event_type" required>
                <option value="">Select event type</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
            <label for="event_type">Event Type:</label>
        </div>
        <div class="input-container" id="venue_container" style="display: none;">
            <input type="text" id="meetup_venue" name="meetup_venue" placeholder=" ">
            <label for="meetup_venue">Meetup Venue:</label>
        </div>
        <div class="input-container" id="meet_link_container" style="display: none;">
            <input type="text" id="meet_link" name="meet_link" placeholder=" " readonly>
            <label for="meet_link">Meet Link:</label>
            <button type="button" id="generate_meet_link">Generate Meet Link</button>
        </div>
        <script>
            document.getElementById('event_type').addEventListener('change', function() {
                var venueContainer = document.getElementById('venue_container');
                var meetLinkContainer = document.getElementById('meet_link_container');
                if (this.value === 'offline') {
                    venueContainer.style.display = 'block';
                    meetLinkContainer.style.display = 'none';
                    document.getElementById('meetup_venue').required = true;
                    document.getElementById('meet_link').required = false;
                } else if (this.value === 'online') {
                    venueContainer.style.display = 'none';
                    meetLinkContainer.style.display = 'block';
                    document.getElementById('meetup_venue').required = false;
                    document.getElementById('meet_link').required = true;
                } else {
                    venueContainer.style.display = 'none';
                    meetLinkContainer.style.display = 'none';
                    document.getElementById('meetup_venue').required = false;
                    document.getElementById('meet_link').required = false;
                }
            });

            document.getElementById('generate_meet_link').addEventListener('click', function() {
                // Call function to generate meet link
                generateMeetLink();
            });

            function generateMeetLink() {
                // This function should be implemented to generate the meet link
                // For now, we'll just set a placeholder value
                document.getElementById('meet_link').value = 'https://meet.example.com/' + Math.random().toString(36).substring(7);
            }
        </script>        

        <div class="input-container">
            <select id="attendees" name="attendees[]" multiple required>
                <?php
                // Fetch active employees from the database
                $query = "SELECT id, email, first_name, last_name FROM employees WHERE work_status = 'active' ORDER BY first_name";
                $result = mysqli_query($conn, $query);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['id'] . "'>" . $row['first_name'] . " " . $row['last_name'] . " (" . $row['email'] . ")</option>";
                }
                ?>
            </select>
            <label for="attendees">Select Attendees:</label>
        </div>
        <div class="input-container">
            <textarea id="event_description" name="event_description" rows="4" placeholder="Event Description"></textarea>
        </div>
          <div class="input-container">
              <input type="file" id="event_image" name="event_image[]" accept="image/*" multiple>
              <label for="event_image">Event Images:</label>
          </div>

        <div class="input-container">
            <input type="text" id="organizer" name="organizer" required placeholder=" ">
            <label for="organizer">Organizer:</label>
        </div>


            <input type="hidden" id="company_id" name="company_id" value="<?php echo $companyId; ?>">
        


        <input type="submit" value="Create Event">



</form>



<script>
document.getElementById('createeventform').addEventListener('submit', function(e) {
    e.preventDefault();

    var createEventData = new FormData(this);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'createevent.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Handle successful response
            console.log(xhr.responseText);
            alert('Event created successfully!');
            // Optionally, redirect or update the page
        } else {
            // Handle error
            console.error('Error creating event:', xhr.statusText);
            alert('Error creating event. Please try again.');
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
        alert('Request failed. Please check your connection and try again.');
    };
    xhr.send(createEventData);
});
</script>
