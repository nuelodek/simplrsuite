<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];

    $sql = "INSERT INTO expenses (date, description, amount, category) VALUES ('$date', '$description', '$amount', '$category')";

    if ($conn->query($sql) === TRUE) {
        $expense_id = $conn->insert_id;
        $sql = "INSERT INTO ledger_entries (date, description, amount, type, category, reference_id) VALUES ('$date', 'Expense: $description', '$amount', 'debit', 'Expense', '$expense_id')";
        $conn->query($sql);
        echo "New expense and ledger entry created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>
