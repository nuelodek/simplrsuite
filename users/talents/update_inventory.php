<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $transaction_type = $_POST['transaction_type'];
    $quantity = $_POST['quantity'];
    $date = $_POST['date'];

    if ($transaction_type == 'add') {
        $sql = "UPDATE inventory_items SET quantity = quantity + $quantity WHERE id = $item_id";
    } elseif ($transaction_type == 'remove') {
        $sql = "UPDATE inventory_items SET quantity = quantity - $quantity WHERE id = $item_id";
    }

    if ($conn->query($sql) === TRUE) {
        $sql = "INSERT INTO inventory_transactions (item_id, transaction_type, quantity, date) VALUES ('$item_id', '$transaction_type', '$quantity', '$date')";
        if ($conn->query($sql) === TRUE) {
            echo "Inventory updated successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>
