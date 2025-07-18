<!DOCTYPE html>
<html>
<head>
    <title>Simplr - Ledger</title>
</head>
<body>
    <h1>Add Ledger Entry</h1>
    <form action="add_ledger_entry.php" method="post">
        <label for="date">Date:</label><br>
        <input type="date" id="date" name="date"><br>
        <label for="description">Description:</label><br>
        <input type="text" id="description" name="description"><br>
        <label for="amount">Amount:</label><br>
        <input type="number" step="0.01" id="amount" name="amount"><br>
        <label for="type">Type:</label><br>
        <select id="type" name="type">
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
        </select><br>
        <label for="category">Category:</label><br>
        <input type="text" id="category" name="category"><br>
        <label for="reference_id">Reference ID (optional):</label><br>
        <input type="number" id="reference_id" name="reference_id"><br><br>
        <input type="submit" value="Add Entry">
    </form>

    <h1>Ledger Entries</h1>
    <?php include 'list_ledger_entries.php'; ?>
</body>
</html>
