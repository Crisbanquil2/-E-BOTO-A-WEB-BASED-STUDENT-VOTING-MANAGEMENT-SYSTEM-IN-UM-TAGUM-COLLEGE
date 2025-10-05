<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Reset Voting System</h2>";
    
    // Check current votes
    echo "<h3>Current Votes:</h3>";
    $query = "SELECT v.vote_id, v.student_id, v.position, v.voted_at, c.first_name, c.last_name, s.student_number
              FROM votes v
              JOIN candidates c ON v.candidate_id = c.candidate_id
              JOIN students s ON v.student_id = s.student_id
              ORDER BY v.voted_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($votes)) {
        echo "<p>No votes found.</p>";
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
    
    // Clear all votes
    echo "<h3>Clearing All Votes:</h3>";
    $query = "DELETE FROM votes";
    $stmt = $conn->prepare($query);
    $result = $stmt->execute();
    
    if ($result) {
        echo "<p style='color: green;'>✓ All votes cleared successfully.</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to clear votes.</p>";
    }
    
    // Check available positions
    echo "<h3>Available Positions:</h3>";
    $query = "SELECT DISTINCT position FROM candidates ORDER BY position";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($positions as $position) {
        echo "<p><strong>$position</strong></p>";
        
        // Get candidates for this position
        $query = "SELECT candidate_id, first_name, last_name FROM candidates WHERE position = :position";
        $stmt = $conn->prepare($query);
        $stmt->execute([':position' => $position]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($candidates as $candidate) {
            echo "&nbsp;&nbsp;- {$candidate['first_name']} {$candidate['last_name']} (ID: {$candidate['candidate_id']})<br>";
        }
    }
    
    echo "<h3>System Reset Complete!</h3>";
    echo "<p>You can now test voting for different positions.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
