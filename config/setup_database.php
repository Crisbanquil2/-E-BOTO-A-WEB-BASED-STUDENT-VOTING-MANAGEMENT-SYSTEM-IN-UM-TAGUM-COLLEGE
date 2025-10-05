<?php
/**
 * Database Setup Script for Student Voting Management System
 * Run this script to set up the database tables
 */

require_once 'database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h2>Setting up Student Voting Management System Database...</h2>";
    
    // Read and execute the candidates database SQL
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
            $conn->exec($statement);
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
    
    // Test the setup by trying to fetch candidates
    echo "<h3>Testing Setup...</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM candidates");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✓ Candidates table is ready (currently has {$result['count']} candidates)</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error testing candidates table: " . $e->getMessage() . "</p>";
    }
    
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM votes");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✓ Votes table is ready (currently has {$result['count']} votes)</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error testing votes table: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Add candidates through the Admin Candidate Management page</li>";
    echo "<li>Students can now view and vote for candidates on the Dashboard</li>";
    echo "<li>Monitor results in real-time</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Setup Failed!</h3>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in database.php</p>";
}
?>
