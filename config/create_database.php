<?php
/**
 * Database Creation Script for Student Voting Management System
 * This script creates the database and tables if they don't exist
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // First, connect without specifying a database to create it
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Creating Database and Tables...</h2>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `voting_system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Database 'voting_system' created/verified</p>";
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=voting_system;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute the database structure
    $sql = file_get_contents('candidates_database.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read candidates_database.sql file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
        } catch (PDOException $e) {
            $errorCount++;
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>Successfully executed: $successCount statements</p>";
    if ($errorCount > 0) {
        echo "<p style='color: orange;'>Errors encountered: $errorCount statements</p>";
    }
    
    // Test the setup
    echo "<h3>Testing Setup...</h3>";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✓ Students table is ready (currently has {$result['count']} students)</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error testing students table: " . $e->getMessage() . "</p>";
    }
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM candidates");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✓ Candidates table is ready (currently has {$result['count']} candidates)</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error testing candidates table: " . $e->getMessage() . "</p>";
    }
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM votes");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✓ Votes table is ready (currently has {$result['count']} votes)</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error testing votes table: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Database Setup Complete!</h3>";
    echo "<p style='color: green; font-weight: bold;'>Your voting system database is now ready!</p>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Add students through registration</li>";
    echo "<li>Add candidates through the Admin Candidate Management page</li>";
    echo "<li>View real-time statistics on the Admin Dashboard</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Setup Failed!</h3>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL server is running and your credentials are correct.</p>";
}
?>
