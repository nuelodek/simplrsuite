<?php
include 'db.php';

$sql = "SELECT * FROM inventory_items";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Description</th><th>Quantity</th><th>Unit Price</th><th>Category</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"]. "</td><td>" . $row["name"]. "</td><td>" . $row["description"]. "</td><td>" . $row["quantity"]. "</td><td>" . $row["unit_price"]. "</td><td>" . $row["category"]. "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>
