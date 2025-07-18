<?php
    // Database configuration
    $servername = "localhost";
    $username = "simplrco_simplr";
    $password = "3)TiskO2(0W8Iu";
    $dbname = "simplrco_dashboard";
    
    try {
        // Create connection
        $conn = new PDO("mysql:host=$servername", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // Create database if not exists
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        $conn->exec($sql);
    
        // Switch to newly created database
        $conn->exec("USE $dbname");
    
        // Create tables
        $tablesSql = file_get_contents(__DIR__ . "../sql_queries/simplrco_dashboard.sql");
        $conn->exec($tablesSql);
    
        echo "Database and tables created successfully!";
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    
    $conn = null;
    
?>