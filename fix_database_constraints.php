<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Fixing Database Constraints</h2>";
    
    // Check current constraints
    echo "<h3>Current Unique Constraints:</h3>";
    $query = "SHOW INDEX FROM votes WHERE Non_unique = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($indexes)) {
        echo "<p>Found unique constraints:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Key Name</th><th>Column</th></tr>";
        foreach ($indexes as $index) {
            echo "<tr><td>{$index['Key_name']}</td><td>{$index['Column_name']}</td></tr>";
        }
        echo "</table><br>";
        
        // Drop the problematic constraint
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'unique_student_position') {
                echo "<p>Dropping constraint: {$index['Key_name']}</p>";
                $query = "ALTER TABLE votes DROP INDEX {$index['Key_name']}";
                $stmt = $conn->prepare($query);
                $result = $stmt->execute();
                
                if ($result) {
                    echo "<p style='color: green;'>✓ Constraint dropped successfully.</p>";
                } else {
                    echo "<p style='color: red;'>✗ Failed to drop constraint.</p>";
                }
            }
        }
    } else {
        echo "<p>No unique constraints found.</p>";
    }
    
    // Add proper unique constraint (student_id + position should be unique, not student_number + position)
    echo "<h3>Adding Proper Unique Constraint:</h3>";
    try {
        $query = "ALTER TABLE votes ADD CONSTRAINT unique_student_position UNIQUE (student_id, position)";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute();
        
        if ($result) {
            echo "<p style='color: green;'>✓ Proper unique constraint added (student_id + position).</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Constraint might already exist or there was an issue.</p>";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<p style='color: orange;'>⚠ Constraint already exists.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding constraint: " . $e->getMessage() . "</p>";
        }
    }
    
    // Check current votes and clean up any duplicates
    echo "<h3>Cleaning Up Duplicate Votes:</h3>";
    $query = "SELECT student_id, position, COUNT(*) as count 
              FROM votes 
              GROUP BY student_id, position 
              HAVING COUNT(*) > 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "<p style='color: green;'>✓ No duplicate votes found.</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Found duplicate votes:</p>";
        foreach ($duplicates as $dup) {
            echo "<p>Student ID: {$dup['student_id']}, Position: {$dup['position']}, Count: {$dup['count']}</p>";
        }
        
        // Remove duplicates, keeping only the latest vote
        echo "<p>Removing duplicates...</p>";
        
        $query = "DELETE v1 FROM votes v1
                  INNER JOIN votes v2 
                  WHERE v1.student_id = v2.student_id 
                  AND v1.position = v2.position 
                  AND v1.vote_id < v2.vote_id";
        
        $stmt = $conn->prepare($query);
        $result = $stmt->execute();
        
        if ($result) {
            echo "<p style='color: green;'>✓ Duplicate votes removed successfully.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error removing duplicates.</p>";
        }
    }
    
    // Show final status
    echo "<h3>Final Status:</h3>";
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
        echo "<p><strong>Current votes for student 2005:</strong></p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Position</th><th>Candidate</th><th>Voted At</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>{$vote['position']}</td>";
            echo "<td>{$vote['first_name']} {$vote['last_name']}</td>";
            echo "<td>{$vote['voted_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
