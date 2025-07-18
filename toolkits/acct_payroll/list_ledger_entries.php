<?php
include 'db.php';

$sql = "SELECT * FROM ledger_entries ORDER BY date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Date</th><th>Description</th><th>Amount</th><th>Type</th><th>Category</th><th>Reference ID</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"]. "</td><td>" . $row["date"]. "</td><td>" . $row["description"]. "</td><td>" . $row["amount"]. "</td><td>" . $row["type"]. "</td><td>" . $row["category"]. "</td><td>" . $row["reference_id"]. "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>
