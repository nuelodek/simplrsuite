
<?php
include '../../sql_queries/db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);

    // Delete the event
    $delete_sql = "DELETE FROM events WHERE id = ? AND company_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "ii", $event_id, $companyId);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        echo "Event deleted successfully.";
    } else {
        echo "Failed to delete event.";
    }

    mysqli_stmt_close($delete_stmt);
}

// Fetch all events for the dropdown
$events_sql = "SELECT id, event_name, event_date FROM events WHERE company_id = ?";
$events_stmt = mysqli_prepare($conn, $events_sql);
mysqli_stmt_bind_param($events_stmt, "i", $companyId);
mysqli_stmt_execute($events_stmt);
$events_result = mysqli_stmt_get_result($events_stmt);

if (mysqli_num_rows($events_result) == 0) {
    echo "You have no events for your organization";
} else {
?>

<form method="POST" action="" class="customizeforms">
    <div class="input-container">
        <select id="event_id" name="event_id" required>
            <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                <option value="<?php echo $event['id']; ?>"><?php echo $event['event_name']; ?> (<?php echo $event['event_date']; ?>)</option>
            <?php endwhile; ?>
        </select>
        <label for="event_id">Select Event to Delete:</label>
    </div>

    <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this event?');">
</form>

<?php
}
mysqli_stmt_close($events_stmt);
mysqli_close($conn);
?>
