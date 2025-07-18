  <?php
  include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

  // Enable error reporting
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // Pagination settings
  $resultsPerPage = 10;
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $offset = ($page - 1) * $resultsPerPage;

  $sql = "SELECT 
      e.id, 
      e.event_name, 
      e.start_datetime, 
      e.end_datetime, 
      e.agenda, 
      e.event_type, 
      e.meetup_venue, 
      e.meet_link, 
      GROUP_CONCAT(CONCAT(emp.first_name, ' ', emp.last_name) SEPARATOR ', ') AS attendees,
      e.event_description,  
      e.organizer, 
      e.created_at
  FROM 
      events e
  LEFT JOIN 
      employees emp ON FIND_IN_SET(emp.id, e.attendees) > 0 AND emp.company_id = e.company_id
  WHERE 
      e.company_id = ?
  GROUP BY 
      e.id
  LIMIT ? OFFSET ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iii", $companyId, $resultsPerPage, $offset);
  $stmt->execute();
  $result = $stmt->get_result();

  // Count total rows for pagination
  $countSql = "SELECT COUNT(*) as total FROM events WHERE company_id = ?";
  $countStmt = $conn->prepare($countSql);
  $countStmt->bind_param("i", $companyId);
  $countStmt->execute();
  $countResult = $countStmt->get_result();
  $totalRows = $countResult->fetch_assoc()['total'];
  $totalPages = ceil($totalRows / $resultsPerPage);

  echo '<link rel="stylesheet" href="../../public/styles/nicetable.css">';

  if ($result->num_rows > 0) {
      echo "<table class='nice-table'>
      <tr>
          <th>ID</th>
          <th>Event Name</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Agenda</th>
          <th>Event Type</th>
          <th>Venue</th>
          <th>Meet Link</th>
          <th>Attendees</th>
          <th>Description</th>
          <th>Organizer</th>
          <th>Created At</th>
      </tr>";
    
      while($row = $result->fetch_assoc()) {
          echo "<tr>
              <td>".$row["id"]."</td>
              <td>".$row["event_name"]."</td>
              <td>".$row["start_datetime"]."</td>
              <td>".$row["end_datetime"]."</td>
              <td>".$row["agenda"]."</td>
              <td>".$row["event_type"]."</td>
              <td>".$row["meetup_venue"]."</td>
              <td>".(!empty($row["meet_link"]) ? $row["meet_link"] : "N/A")."</td>
              <td>".$row["attendees"]."</td>
              <td>".$row["event_description"]."</td>
              <td>".$row["organizer"]."</td>
              <td>".$row["created_at"]."</td>
          </tr>";
      }
      echo "</table>";

      // Pagination links
      echo "<div class='pagination'>";
      if ($page > 1) {
          echo "<a href='?page=".($page-1)."'>Previous</a>";
      }
      if ($page < $totalPages) {
          echo "<a href='?page=".($page+1)."'>Next</a>";
      }
      echo "</div>";
  } else {
      echo "No events found for your organization";
  }

  $stmt->close();
  $countStmt->close();
  $conn->close();
