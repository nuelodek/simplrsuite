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
    e.first_name, 
    e.last_name, 
    e.email, 
    e.job_title, 
    e.department_id,
    CASE 
        WHEN e.role_id = 2 THEN 'Manager' 
        ELSE 'Employee' 
    END AS position,
    COALESCE(m.manager_name, 'N/A') AS manager_name,
    e.work_status
FROM 
    employees e
LEFT JOIN 
    managers m ON e.manager_id = m.id
WHERE 
    e.company_id = ?
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $companyId, $resultsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Count total rows for pagination
$countSql = "SELECT COUNT(*) as total FROM employees WHERE company_id = ?";
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
        <th>Name</th>
        <th>Email</th>
        <th>Job Title</th>
        <th>Department ID</th>
        <th>Position</th>
        <th>Manager</th>
        <th>Status</th>
    </tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>".$row["id"]."</td>
            <td>".$row["first_name"]." ".$row["last_name"]."</td>
            <td>".$row["email"]."</td>
            <td>".$row["job_title"]."</td>
            <td>".$row["department_id"]."</td>
            <td>".$row["position"]."</td>
            <td>".$row["manager_name"]."</td>
            <td>".$row["work_status"]."</td>
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
    echo "No employees or managers found for your organization";
}

$stmt->close();
$countStmt->close();
$conn->close();
