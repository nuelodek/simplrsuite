<?php
include 'db.php';

$outputFile = 'database_structure.sql';
$fileHandle = fopen($outputFile, 'w');

// Function to get the CREATE TABLE statement for each table
function getCreateTableStatement($conn, $table) {
    $sql = "SHOW CREATE TABLE `$table`";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['Create Table'] . ";\n\n";
    }
    return null;
}

// Get list of tables
$tables = $conn->query("SHOW TABLES");
if ($tables) {
    while ($row = $tables->fetch_array()) {
        $table = $row[0];
        $createTableSQL = getCreateTableStatement($conn, $table);
        if ($createTableSQL) {
            fwrite($fileHandle, $createTableSQL);
        }
    }
    echo "Database structure has been extracted to `$outputFile`.\n";
} else {
    die("Error fetching tables: " . $conn->error);
}

// Close file handle and connection
fclose($fileHandle);
$conn->close();
?>
