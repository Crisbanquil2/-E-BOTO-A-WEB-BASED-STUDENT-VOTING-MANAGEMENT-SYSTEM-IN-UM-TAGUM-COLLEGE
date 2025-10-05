<?php
/**
 * Check current user's voting status
 */

require_once 'database.php';

echo "<h2>Check My Voting Status</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get all students
    $stmt = $conn->prepare("SELECT student_id, student_number, first_name, last_name FROM students WHERE status = 'active' ORDER BY student_number");
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    echo "<h3>All Students in Database:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Student Number</th><th>Name</th><th>Voting Status</th></tr>";
    
    foreach ($students as $student) {
        // Check if student has voted
        $voteStmt = $conn->prepare("SELECT COUNT(*) as vote_count FROM votes WHERE student_id = ?");
        $voteStmt->execute([$student['student_id']]);
        $voteCount = $voteStmt->fetch()['vote_count'];
        
        echo "<tr>";
        echo "<td>" . $student['student_id'] . "</td>";
        echo "<td>" . $student['student_number'] . "</td>";
        echo "<td>" . $student['first_name'] . " " . $student['last_name'] . "</td>";
        echo "<td>" . ($voteCount > 0 ? "✅ Voted (" . $voteCount . " votes)" : "❌ Not voted") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show all votes
    echo "<br><h3>All Votes in Database:</h3>";
    $stmt = $conn->prepare("
        SELECT v.vote_id, s.student_number, s.first_name, s.last_name, c.first_name as candidate_first, c.last_name as candidate_last, v.position, v.created_at
        FROM votes v
        JOIN students s ON v.student_id = s.student_id
        JOIN candidates c ON v.candidate_id = c.candidate_id
        ORDER BY v.created_at DESC
    ");
    $stmt->execute();
    $votes = $stmt->fetchAll();
    
    if (empty($votes)) {
        echo "No votes found in database.<br>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Vote ID</th><th>Student</th><th>Voted For</th><th>Position</th><th>Date</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>" . $vote['vote_id'] . "</td>";
            echo "<td>" . $vote['first_name'] . " " . $vote['last_name'] . " (" . $vote['student_number'] . ")</td>";
            echo "<td>" . $vote['candidate_first'] . " " . $vote['candidate_last'] . "</td>";
            echo "<td>" . $vote['position'] . "</td>";
            echo "<td>" . $vote['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test voting for a different student
    echo "<br><h3>Test Voting for Different Student:</h3>";
    $testStudent = $students[0]; // Use first student
    echo "Testing with: " . $testStudent['first_name'] . " " . $testStudent['last_name'] . " (" . $testStudent['student_number'] . ")<br>";
    
    // Get a candidate
    $stmt = $conn->prepare("SELECT candidate_id, first_name, last_name, position FROM candidates WHERE status = 'active' LIMIT 1");
    $stmt->execute();
    $candidate = $stmt->fetch();
    
    if ($candidate) {
        echo "Testing vote for: " . $candidate['first_name'] . " " . $candidate['last_name'] . " (" . $candidate['position'] . ")<br>";
        
        // Test the API call
        $url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=submit_vote';
        $postData = json_encode([
            'studentId' => $testStudent['student_number'],
            'candidateId' => $candidate['candidate_id'],
            'position' => $candidate['position']
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $postData
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        echo "API Response: " . htmlspecialchars($response) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Next Steps:</h3>";
echo "1. Check which student you're logged in as<br>";
echo "2. If you already voted, you can't vote again<br>";
echo "3. Try logging in as a different student to test voting<br>";
?>
