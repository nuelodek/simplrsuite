<?php
include 'db.php';

$sql = "SELECT * FROM expenses";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Date</th><th>Description</th><th>Amount</th><th>Category</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"]. "</td><td>" . $row["date"]. "</td><td>" . $row["description"]. "</td><td>" . $row["amount"]. "</td><td>" . $row["category"]. "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>
