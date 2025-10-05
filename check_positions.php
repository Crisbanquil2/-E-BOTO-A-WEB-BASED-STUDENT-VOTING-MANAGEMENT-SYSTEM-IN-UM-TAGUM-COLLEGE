<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Available Positions and Candidates</h2>";
    
    // Get all positions and their candidates
    $query = "SELECT DISTINCT position FROM candidates ORDER BY position";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Available Positions:</h3>";
    foreach ($positions as $position) {
        echo "<p><strong>$position</strong></p>";
        
        // Get candidates for this position
        $query = "SELECT candidate_id, first_name, last_name, gender, course, year_level, status 
                  FROM candidates 
                  WHERE position = :position 
                  ORDER BY first_name";
        $stmt = $conn->prepare($query);
        $stmt->execute([':position' => $position]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin-left: 20px;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Gender</th><th>Course</th><th>Year</th><th>Status</th></tr>";
        foreach ($candidates as $candidate) {
            echo "<tr>";
            echo "<td>{$candidate['candidate_id']}</td>";
            echo "<td>{$candidate['first_name']} {$candidate['last_name']}</td>";
            echo "<td>{$candidate['gender']}</td>";
            echo "<td>{$candidate['course']}</td>";
            echo "<td>{$candidate['year_level']}</td>";
            echo "<td>{$candidate['status']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Check current votes for student 2005
    echo "<h3>Current Votes for Student 2005:</h3>";
    $query = "SELECT v.vote_id, v.position, v.voted_at, c.first_name, c.last_name, s.student_number
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
        echo "<tr><th>Vote ID</th><th>Position</th><th>Candidate</th><th>Voted At</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>{$vote['vote_id']}</td>";
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
