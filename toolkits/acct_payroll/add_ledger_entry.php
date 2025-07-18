<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $reference_id = $_POST['reference_id'];

    $sql = "INSERT INTO ledger_entries (date, description, amount, type, category, reference_id) VALUES ('$date', '$description', '$amount', '$type', '$category', '$reference_id')";

    if ($conn->query($sql) === TRUE) {
        echo "New ledger entry created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>
