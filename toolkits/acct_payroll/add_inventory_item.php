<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $category = $_POST['category'];

    $sql = "INSERT INTO inventory_items (name, description, quantity, unit_price, category) VALUES ('$name', '$description', '$quantity', '$unit_price', '$category')";

    if ($conn->query($sql) === TRUE) {
        echo "New inventory item created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>
