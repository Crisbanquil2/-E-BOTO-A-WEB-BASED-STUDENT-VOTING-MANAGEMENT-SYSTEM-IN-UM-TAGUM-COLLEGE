<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Database Constraints Check</h2>";
    
    // Check votes table structure
    echo "<h3>Votes Table Structure:</h3>";
    $query = "DESCRIBE votes";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Check for unique constraints
    echo "<h3>Unique Constraints:</h3>";
    $query = "SHOW INDEX FROM votes WHERE Non_unique = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($indexes)) {
        echo "<p>No unique constraints found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Key Name</th><th>Column</th><th>Unique</th></tr>";
        foreach ($indexes as $index) {
            echo "<tr>";
            echo "<td>{$index['Key_name']}</td>";
            echo "<td>{$index['Column_name']}</td>";
            echo "<td>{$index['Non_unique'] == 0 ? 'Yes' : 'No'}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check current votes for student 2005
    echo "<h3>Current Votes for Student 2005:</h3>";
    $query = "SELECT v.vote_id, v.student_id, v.position, v.voted_at, c.first_name, c.last_name, s.student_number
              FROM votes v
              JOIN candidates c ON v.candidate_id = c.candidate_id
              JOIN students s ON v.student_id = s.student_id
              WHERE s.student_number = '2005'
              ORDER BY v.voted_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($votes)) {
        echo "<p>No votes found for student 2005.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Vote ID</th><th>Student ID</th><th>Student Number</th><th>Position</th><th>Candidate</th><th>Voted At</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>{$vote['vote_id']}</td>";
            echo "<td>{$vote['student_id']}</td>";
            echo "<td>{$vote['student_number']}</td>";
            echo "<td>{$vote['position']}</td>";
            echo "<td>{$vote['first_name']} {$vote['last_name']}</td>";
            echo "<td>{$vote['voted_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check if there are other positions available
    echo "<h3>Available Positions:</h3>";
    $query = "SELECT DISTINCT position FROM candidates ORDER BY position";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($positions as $position) {
        echo "<p><strong>$position</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
