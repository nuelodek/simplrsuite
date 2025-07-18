<form method="POST" action="savewidgets.php" class="styledform">
    <?php

// Get all widgets
$query = "SELECT w.id, w.widget_name, w.is_default, 
                 (SELECT COUNT(*) FROM company_widgets cw WHERE cw.widget_id = w.id AND cw.company_id = ?) as selected 
          FROM widgets w";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $companyId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt); 

$widgets = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Display the widgets list
foreach ($widgets as $widget) {
    $checked = ($widget['is_default'] == 1 || $widget['selected'] > 0) ? 'checked' : '';
    echo '<label style="display: block; margin-bottom: 5px; font-size: 12px;">';
    echo '<input type="checkbox" name="widgets" value="' . $widget['id'] . '"> ' . $widget['widget_name'];
    echo '</label>';
}
?>
                <input type="submit" value="Apply Changes" style="margin-top: 10px; padding: 5px 10px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;">
                <!-- <button onclick="applyWidgets()" style="margin-top: 10px; padding: 5px 10px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;">Apply Widgets</button> -->

</form>
