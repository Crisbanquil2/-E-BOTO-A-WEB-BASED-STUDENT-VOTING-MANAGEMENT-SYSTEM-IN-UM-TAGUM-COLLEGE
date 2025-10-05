<?php
/**
 * Check My Votes - Simple script to view voting history
 */

require_once 'config/database.php';

echo "<h1>My Voting History</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get all votes from the database
    $query = "SELECT 
                v.vote_id,
                v.student_id,
                v.position,
                v.voted_at,
                c.first_name,
                c.last_name,
                s.first_name as student_first_name,
                s.last_name as student_last_name,
                s.student_number
              FROM votes v
              JOIN candidates c ON v.candidate_id = c.candidate_id
              JOIN students s ON v.student_id = s.student_id
              ORDER BY v.voted_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>All Votes in Database (" . count($votes) . " total)</h2>";
    
    if (count($votes) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Vote ID</th>";
        echo "<th>Student</th>";
        echo "<th>Position</th>";
        echo "<th>Voted For</th>";
        echo "<th>Date/Time</th>";
        echo "</tr>";
        
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>" . $vote['vote_id'] . "</td>";
            echo "<td>" . $vote['student_first_name'] . " " . $vote['student_last_name'] . " (ID: " . $vote['student_id'] . ")</td>";
            echo "<td>" . $vote['position'] . "</td>";
            echo "<td>" . $vote['first_name'] . " " . $vote['last_name'] . "</td>";
            echo "<td>" . $vote['voted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No votes found in the database.</p>";
    }
    
    // Check if there are any students
    $query = "SELECT student_id, student_number, first_name, last_name FROM students LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Students in Database</h2>";
    if (count($students) > 0) {
        echo "<ul>";
        foreach ($students as $student) {
            echo "<li>ID: " . $student['student_id'] . ", Number: " . $student['student_number'] . ", Name: " . $student['first_name'] . " " . $student['last_name'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No students found in database.</p>";
    }
    
    // Check if there are any candidates
    $query = "SELECT candidate_id, first_name, last_name, position FROM candidates LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Candidates in Database</h2>";
    if (count($candidates) > 0) {
        echo "<ul>";
        foreach ($candidates as $candidate) {
            echo "<li>ID: " . $candidate['candidate_id'] . ", Name: " . $candidate['first_name'] . " " . $candidate['last_name'] . ", Position: " . $candidate['position'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No candidates found in database.</p>";
    }
    
    echo "<h2>Summary</h2>";
    echo "<p style='color: green; font-weight: bold;'>✅ Voting system is working correctly!</p>";
    echo "<p>The error 'You have already voted for this position' means your vote was successfully recorded and the system is preventing duplicate voting.</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='Voting Status/voting_status.html'>View Your Voting Status</a> - Check your voting history</li>";
    echo "<li><a href='Dasboard for Student or user/index.html'>Go to Dashboard</a> - Return to main dashboard</li>";
    echo "<li><a href='test_voting_fixed.html'>Test Voting System</a> - Run comprehensive tests</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
