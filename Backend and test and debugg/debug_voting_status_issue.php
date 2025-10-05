<?php
/**
 * Debug Voting Status Issue
 * This script will help identify why voting history is not showing
 */

require_once 'config/database.php';

echo "<h1>Voting Status Debug Report</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .warning { background-color: #fff3cd; border-color: #ffeaa7; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<div class='section info'>";
    echo "<h2>✅ Database Connection</h2>";
    echo "<p>Successfully connected to the database.</p>";
    echo "</div>";
    
    // Check if votes table exists and has data
    echo "<div class='section'>";
    echo "<h2>Votes Table Analysis</h2>";
    
    $query = "SELECT COUNT(*) as total_votes FROM votes";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $totalVotes = $stmt->fetch()['total_votes'];
    
    if ($totalVotes > 0) {
        echo "<div class='success'>";
        echo "<p><strong>✅ Found $totalVotes votes in the database</strong></p>";
        echo "</div>";
        
        // Show sample votes
        $query = "SELECT v.*, c.first_name, c.last_name, c.position, s.first_name as student_first_name, s.last_name as student_last_name 
                  FROM votes v 
                  JOIN candidates c ON v.candidate_id = c.candidate_id 
                  JOIN students s ON v.student_id = s.student_id 
                  ORDER BY v.voted_at DESC LIMIT 5";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $sampleVotes = $stmt->fetchAll();
        
        echo "<h3>Sample Votes (Latest 5):</h3>";
        echo "<table>";
        echo "<tr><th>Vote ID</th><th>Student</th><th>Position</th><th>Voted For</th><th>Date</th></tr>";
        foreach ($sampleVotes as $vote) {
            echo "<tr>";
            echo "<td>" . $vote['vote_id'] . "</td>";
            echo "<td>" . $vote['student_first_name'] . " " . $vote['student_last_name'] . "</td>";
            echo "<td>" . $vote['position'] . "</td>";
            echo "<td>" . $vote['first_name'] . " " . $vote['last_name'] . "</td>";
            echo "<td>" . $vote['voted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>";
        echo "<p><strong>⚠️ No votes found in the database</strong></p>";
        echo "<p>This explains why you can't see your voting history. You need to vote first!</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // Check students table
    echo "<div class='section'>";
    echo "<h2>Students Table Analysis</h2>";
    
    $query = "SELECT COUNT(*) as total_students FROM students";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $totalStudents = $stmt->fetch()['total_students'];
    
    echo "<p><strong>Total students in database: $totalStudents</strong></p>";
    
    if ($totalStudents > 0) {
        $query = "SELECT student_id, student_number, first_name, last_name FROM students LIMIT 5";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $sampleStudents = $stmt->fetchAll();
        
        echo "<h3>Sample Students:</h3>";
        echo "<table>";
        echo "<tr><th>Student ID</th><th>Student Number</th><th>Name</th></tr>";
        foreach ($sampleStudents as $student) {
            echo "<tr>";
            echo "<td>" . $student['student_id'] . "</td>";
            echo "<td>" . $student['student_number'] . "</td>";
            echo "<td>" . $student['first_name'] . " " . $student['last_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Check candidates table
    echo "<div class='section'>";
    echo "<h2>Candidates Table Analysis</h2>";
    
    $query = "SELECT COUNT(*) as total_candidates FROM candidates";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $totalCandidates = $stmt->fetch()['total_candidates'];
    
    echo "<p><strong>Total candidates in database: $totalCandidates</strong></p>";
    
    if ($totalCandidates > 0) {
        $query = "SELECT candidate_id, first_name, last_name, position FROM candidates LIMIT 5";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $sampleCandidates = $stmt->fetchAll();
        
        echo "<h3>Sample Candidates:</h3>";
        echo "<table>";
        echo "<tr><th>Candidate ID</th><th>Name</th><th>Position</th></tr>";
        foreach ($sampleCandidates as $candidate) {
            echo "<tr>";
            echo "<td>" . $candidate['candidate_id'] . "</td>";
            echo "<td>" . $candidate['first_name'] . " " . $candidate['last_name'] . "</td>";
            echo "<td>" . $candidate['position'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Test the voting API directly
    echo "<div class='section'>";
    echo "<h2>Voting API Test</h2>";
    
    if ($totalStudents > 0) {
        $query = "SELECT student_id, student_number FROM students LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $testStudent = $stmt->fetch();
        
        echo "<p>Testing voting API with student ID: " . $testStudent['student_id'] . "</p>";
        
        // Simulate the API call
        $query = "SELECT 
                    v.vote_id,
                    v.position,
                    v.voted_at,
                    c.first_name,
                    c.last_name,
                    c.photo
                FROM votes v
                JOIN candidates c ON v.candidate_id = c.candidate_id
                JOIN students s ON v.student_id = s.student_id
                WHERE (s.student_id = ? OR s.student_number = ?)
                ORDER BY v.voted_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$testStudent['student_id'], $testStudent['student_id']]);
        $votes = $stmt->fetchAll();
        
        echo "<p><strong>Votes found for test student: " . count($votes) . "</strong></p>";
        
        if (count($votes) > 0) {
            echo "<div class='success'>";
            echo "<p>✅ Voting API should work correctly</p>";
            echo "</div>";
        } else {
            echo "<div class='warning'>";
            echo "<p>⚠️ No votes found for this student - this is normal if they haven't voted yet</p>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Recommendations
    echo "<div class='section info'>";
    echo "<h2>Recommendations</h2>";
    
    if ($totalVotes == 0) {
        echo "<div class='warning'>";
        echo "<h3>⚠️ Main Issue: No Votes Found</h3>";
        echo "<p>You haven't voted yet! To see your voting history, you need to:</p>";
        echo "<ol>";
        echo "<li><a href='Voting/voting.html'>Go to the Voting Page</a></li>";
        echo "<li>Cast your votes for the candidates</li>";
        echo "<li>Then return to <a href='Voting Status/voting_status.html'>Voting Status</a> to see your history</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h3>✅ System Status</h3>";
        echo "<p>Your voting system appears to be working correctly!</p>";
        echo "<p>If you're still not seeing your voting history, try:</p>";
        echo "<ol>";
        echo "<li><a href='test_voting_status_debug.html'>Run the debug test</a></li>";
        echo "<li>Check your browser's developer console for errors</li>";
        echo "<li>Make sure you're logged in with the correct student account</li>";
        echo "<li>Clear your browser cache and try again</li>";
        echo "</ol>";
        echo "</div>";
    }
    
    echo "<h3>Quick Links:</h3>";
    echo "<ul>";
    echo "<li><a href='Voting/voting.html'>Voting Page</a></li>";
    echo "<li><a href='Voting Status/voting_status.html'>Voting Status</a></li>";
    echo "<li><a href='test_voting_status_debug.html'>Debug Test Page</a></li>";
    echo "<li><a href='check_my_votes.php'>Check All Votes</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ Database Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
    echo "</div>";
}
?>
