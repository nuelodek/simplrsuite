<?php
function displayAssignedTools(): void {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Database connection
    include '../../sql_queries/db.php'; // Ensure this file contains your DB connection logic

    if (!isset($_SESSION['company_id'])) {
        echo '<p>Error: Company ID not set in session.</p>';
        return;
    }

    $companyId = $_SESSION['company_id'];

    // Check connection
    // if (!$conn || $conn->connect_error) {
    //     die("Connection failed: " . $conn->connect_error);
    // }

    // Prepare SQL statement to fetch employees with assigned tools
    $sql = "SELECT e.first_name, e.last_name, t.tool_name, t.category, ta.assigned_at, ta.assigned_by, ta.assignment_notes
            FROM employees e 
            INNER JOIN tools_assigned ta ON e.id = ta.employee_id 
            INNER JOIN tools t ON ta.tool_id = t.id
            WHERE e.company_id = ?
            ORDER BY e.last_name, e.first_name";

    $stmt = $conn->prepare($sql);
    
    // if (!$stmt) {
    //     die("Error preparing statement: " . $conn->error);
    // }

    // Bind the company ID parameter to the SQL statement
    $stmt->bind_param("i", $companyId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Start the table
        echo '<table class="nice-table">
                <tr>
                <th>S/N</th>
                <th>Employee Name</th>
                <th>Assigned Tool</th>
                <th>Tool Category</th>
                <th>Assigned At</th>
                </tr>';

        // Fetch and display results
        $serialNumber = 1;
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . $serialNumber . '</td>
                    <td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>
                    <td>' . htmlspecialchars($row['tool_name']) . '</td>
                    <td>' . htmlspecialchars($row['category']) . '</td>
                    <td>' . htmlspecialchars($row['assigned_at']) . '</td>
                  </tr>';
            $serialNumber++;
        }

        // Close the table
        echo '</table>';
    } else {
        echo '<p>No tools assigned to your employees.</p>';
    }

    // Close database connection
    $stmt->close();
    $conn->close();
}

// Call the function to display assigned tools
displayAssignedTools();
