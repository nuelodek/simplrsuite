<!DOCTYPE html>
<html>
<head>
    <title>Simplr - Inventory Management</title>
</head>
<body>
    <h1>Add Inventory Item</h1>
    <form action="add_inventory_item.php" method="post">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name"><br>
        <label for="description">Description:</label><br>
        <input type="text" id="description" name="description"><br>
        <label for="quantity">Quantity:</label><br>
        <input type="number" id="quantity" name="quantity"><br>
        <label for="unit_price">Unit Price:</label><br>
        <input type="number" step="0.01" id="unit_price" name="unit_price"><br>
        <label for="category">Category:</label><br>
        <input type="text" id="category" name="category"><br><br>
        <input type="submit" value="Add Item">
    </form>

    <h1>Inventory Items</h1>
    <?php include 'list_inventory_items.php'; ?>

    <h1>Update Inventory</h1>
    <form action="update_inventory.php" method="post">
        <label for="item_id">Item ID:</label><br>
        <input type="number" id="item_id" name="item_id"><br>
        <label for="transaction_type">Transaction Type:</label><br>
        <select id="transaction_type" name="transaction_type">
            <option value="add">Add</option>
            <option value="remove">Remove</option>
        </select><br>
        <label for="quantity">Quantity:</label><br>
        <input type="number" id="quantity" name="quantity"><br>
        <label for="date">Date:</label><br>
        <input type="date" id="date" name="date"><br><br>
        <input type="submit" value="Update Inventory">
    </form>
</body>
</html>
