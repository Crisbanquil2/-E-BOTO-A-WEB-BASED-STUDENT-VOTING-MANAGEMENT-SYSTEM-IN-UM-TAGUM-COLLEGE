<?php
/**
 * Debug voting API directly
 */

require_once 'database.php';
require_once 'voting_api.php';

echo "<h2>Debug Voting API Direct</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get a student and candidate for testing
    $stmt = $conn->prepare("SELECT student_number FROM students WHERE status = 'active' LIMIT 1");
    $stmt->execute();
    $student = $stmt->fetch();
    
    $stmt = $conn->prepare("SELECT candidate_id, position FROM candidates WHERE status = 'active' LIMIT 1");
    $stmt->execute();
    $candidate = $stmt->fetch();
    
    if (!$student || !$candidate) {
        echo "❌ No students or candidates found<br>";
        exit;
    }
    
    echo "<h3>Test Data:</h3>";
    echo "Student ID: " . $student['student_number'] . "<br>";
    echo "Candidate ID: " . $candidate['candidate_id'] . "<br>";
    echo "Position: " . $candidate['position'] . "<br><br>";
    
    // Test voting API directly
    $votingAPI = new VotingAPI();
    
    echo "<h3>Testing submit_vote action:</h3>";
    $result = $votingAPI->submitVote(
        $student['student_number'],
        $candidate['candidate_id'],
        $candidate['position']
    );
    
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "<br><br>";
    
    // Test check_vote_status action
    echo "<h3>Testing check_vote_status action:</h3>";
    $status = $votingAPI->hasVotedForPosition($student['student_number'], $candidate['position']);
    echo "Status: " . json_encode($status, JSON_PRETTY_PRINT) . "<br><br>";
    
    // Test get_leading_candidates action
    echo "<h3>Testing get_leading_candidates action:</h3>";
    $leading = $votingAPI->getLeadingCandidates();
    echo "Leading: " . json_encode($leading, JSON_PRETTY_PRINT) . "<br><br>";
    
    // Test get_voting_stats action
    echo "<h3>Testing get_voting_stats action:</h3>";
    $stats = $votingAPI->getVotingStats();
    echo "Stats: " . json_encode($stats, JSON_PRETTY_PRINT) . "<br><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<br><h3>Debug Complete</h3>";
?>
