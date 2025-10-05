<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Fixing Duplicate Vote Issue</h2>";
    
    // Check for duplicate votes
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
    
    // Check current votes
    $query = "SELECT v.vote_id, v.student_id, v.position, s.student_number, s.first_name, s.last_name, c.first_name as candidate_first, c.last_name as candidate_last
              FROM votes v
              JOIN students s ON v.student_id = s.student_id
              JOIN candidates c ON v.candidate_id = c.candidate_id
              ORDER BY v.voted_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Votes:</h3>";
    if (empty($votes)) {
        echo "<p>No votes found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Vote ID</th><th>Student ID</th><th>Student Number</th><th>Student Name</th><th>Position</th><th>Candidate</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>{$vote['vote_id']}</td>";
            echo "<td>{$vote['student_id']}</td>";
            echo "<td>{$vote['student_number']}</td>";
            echo "<td>{$vote['first_name']} {$vote['last_name']}</td>";
            echo "<td>{$vote['position']}</td>";
            echo "<td>{$vote['candidate_first']} {$vote['candidate_last']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
