<?php
/**
 * Test Voting System
 * This file helps test the voting system functionality
 */

require_once 'database.php';

echo "<h1>Voting System Test</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>Database Connection: ✓ Connected</h2>";
    
    // Test 1: Check if students table exists and has data
    echo "<h3>Test 1: Students Table</h3>";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $studentCount = $stmt->fetch()['count'];
    echo "Students in database: $studentCount<br>";
    
    if ($studentCount > 0) {
        $stmt = $conn->prepare("SELECT student_id, student_number, first_name, last_name FROM students LIMIT 5");
        $stmt->execute();
        $students = $stmt->fetchAll();
        echo "Sample students:<br>";
        foreach ($students as $student) {
            echo "- ID: {$student['student_id']}, Number: {$student['student_number']}, Name: {$student['first_name']} {$student['last_name']}<br>";
        }
    }
    
    // Test 2: Check if candidates table exists and has data
    echo "<h3>Test 2: Candidates Table</h3>";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates");
    $stmt->execute();
    $candidateCount = $stmt->fetch()['count'];
    echo "Candidates in database: $candidateCount<br>";
    
    if ($candidateCount > 0) {
        $stmt = $conn->prepare("SELECT candidate_id, first_name, last_name, position FROM candidates LIMIT 5");
        $stmt->execute();
        $candidates = $stmt->fetchAll();
        echo "Sample candidates:<br>";
        foreach ($candidates as $candidate) {
            echo "- ID: {$candidate['candidate_id']}, Name: {$candidate['first_name']} {$candidate['last_name']}, Position: {$candidate['position']}<br>";
        }
    }
    
    // Test 3: Check if votes table exists
    echo "<h3>Test 3: Votes Table</h3>";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes");
    $stmt->execute();
    $voteCount = $stmt->fetch()['count'];
    echo "Votes in database: $voteCount<br>";
    
    if ($voteCount > 0) {
        $stmt = $conn->prepare("
            SELECT v.vote_id, s.first_name as student_name, c.first_name as candidate_name, v.position, v.voted_at
            FROM votes v
            JOIN students s ON v.student_id = s.student_id
            JOIN candidates c ON v.candidate_id = c.candidate_id
            ORDER BY v.voted_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $votes = $stmt->fetchAll();
        echo "Recent votes:<br>";
        foreach ($votes as $vote) {
            echo "- Student: {$vote['student_name']}, Candidate: {$vote['candidate_name']}, Position: {$vote['position']}, Date: {$vote['voted_at']}<br>";
        }
    }
    
    // Test 4: Test voting API
    echo "<h3>Test 4: Voting API Test</h3>";
    echo "<p>Testing voting API endpoints...</p>";
    
    // Test get_voting_stats
    echo "<h4>Testing get_voting_stats:</h4>";
    $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/voting_api.php?action=get_voting_stats";
    $response = file_get_contents($url);
    echo "Response: " . htmlspecialchars($response) . "<br>";
    
    // Test get_student_votes (if we have a student)
    if ($studentCount > 0) {
        $stmt = $conn->prepare("SELECT student_id, student_number FROM students LIMIT 1");
        $stmt->execute();
        $testStudent = $stmt->fetch();
        
        echo "<h4>Testing get_student_votes for student {$testStudent['student_number']}:</h4>";
        $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/voting_api.php?action=get_student_votes&studentId=" . $testStudent['student_number'];
        $response = file_get_contents($url);
        echo "Response: " . htmlspecialchars($response) . "<br>";
    }
    
    echo "<h2>Test Complete!</h2>";
    echo "<p><a href='../Voting/voting.html'>Go to Voting Page</a></p>";
    echo "<p><a href='../Voting Status/voting_status.html'>Go to Voting Status Page</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
