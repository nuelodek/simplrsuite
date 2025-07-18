<!DOCTYPE html>
<html>
<head>
    <title>Simplr - Expense Tracker</title>
</head>
<body>
    <h1>Add Expense</h1>
    <form action="add_expense.php" method="post">
        <label for="date">Date:</label><br>
        <input type="date" id="date" name="date"><br>
        <label for="description">Description:</label><br>
        <input type="text" id="description" name="description"><br>
        <label for="amount">Amount:</label><br>
        <input type="number" step="0.01" id="amount" name="amount"><br>
        <label for="category">Category:</label><br>
        <input type="text" id="category" name="category"><br><br>
        <input type="submit" value="Add Expense">
    </form>

    <h1>Expenses</h1>
    <?php include 'list_expenses.php'; ?>
</body>
</html>
