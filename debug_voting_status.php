<?php
/**
 * Debug Voting Status - Check what's happening with voting status
 */

require_once 'config/database.php';

echo "<h1>Debug Voting Status</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>1. Check All Votes in Database</h2>";
    $query = "SELECT v.*, c.first_name, c.last_name, s.first_name as student_first_name, s.last_name as student_last_name 
              FROM votes v
              JOIN candidates c ON v.candidate_id = c.candidate_id
              JOIN students s ON v.student_id = s.student_id
              ORDER BY v.voted_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($votes) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Vote ID</th><th>Student ID</th><th>Student Name</th><th>Position</th><th>Voted For</th><th>Date</th>";
        echo "</tr>";
        
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>" . $vote['vote_id'] . "</td>";
            echo "<td>" . $vote['student_id'] . "</td>";
            echo "<td>" . $vote['student_first_name'] . " " . $vote['student_last_name'] . "</td>";
            echo "<td>" . $vote['position'] . "</td>";
            echo "<td>" . $vote['first_name'] . " " . $vote['last_name'] . "</td>";
            echo "<td>" . $vote['voted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No votes found in database!</p>";
    }
    
    echo "<h2>2. Check Students Table</h2>";
    $query = "SELECT student_id, student_number, first_name, last_name, status FROM students";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Student ID</th><th>Student Number</th><th>Name</th><th>Status</th>";
    echo "</tr>";
    
    foreach ($students as $student) {
        echo "<tr>";
        echo "<td>" . $student['student_id'] . "</td>";
        echo "<td>" . $student['student_number'] . "</td>";
        echo "<td>" . $student['first_name'] . " " . $student['last_name'] . "</td>";
        echo "<td>" . $student['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Test Voting Status API</h2>";
    
    // Test the API that the dashboard uses
    if (count($students) > 0) {
        $testStudentId = $students[0]['student_id'];
        echo "<p>Testing voting status for student ID: $testStudentId</p>";
        
        // Test the simple_api_v2.php
        $url = "http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/simple_api_v2.php?action=get_student_votes&student_id=" . $testStudentId;
        echo "<p>API URL: $url</p>";
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            echo "<p>API Response: <pre>" . htmlspecialchars($response) . "</pre></p>";
        } else {
            echo "<p style='color: red;'>Failed to get API response</p>";
        }
    }
    
    echo "<h2>4. Check Dashboard Voting Status Logic</h2>";
    
    // Simulate what the dashboard does
    if (count($students) > 0) {
        $testStudentId = $students[0]['student_id'];
        
        // Get all candidates
        $query = "SELECT * FROM candidates ORDER BY position, last_name, first_name";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by position
        $groupedCandidates = [];
        foreach ($candidates as $candidate) {
            $position = $candidate['position'];
            if (!isset($groupedCandidates[$position])) {
                $groupedCandidates[$position] = [];
            }
            $groupedCandidates[$position][] = $candidate;
        }
        
        // Get student votes
        $query = "SELECT v.position, c.first_name, c.last_name 
                  FROM votes v
                  JOIN candidates c ON v.candidate_id = c.candidate_id
                  WHERE v.student_id = :student_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':student_id' => $testStudentId]);
        $studentVotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Student votes found: " . count($studentVotes) . "</p>";
        
        if (count($studentVotes) > 0) {
            echo "<ul>";
            foreach ($studentVotes as $vote) {
                echo "<li>Voted for " . $vote['first_name'] . " " . $vote['last_name'] . " for " . $vote['position'] . "</li>";
            }
            echo "</ul>";
        }
        
        // Check each position
        echo "<h3>Position Status Check:</h3>";
        foreach ($groupedCandidates as $position => $positionCandidates) {
            $hasVoted = false;
            $votedCandidate = null;
            
            foreach ($studentVotes as $vote) {
                if ($vote['position'] === $position) {
                    $hasVoted = true;
                    $votedCandidate = $vote;
                    break;
                }
            }
            
            echo "<p><strong>$position:</strong> " . ($hasVoted ? "VOTED for " . $votedCandidate['first_name'] . " " . $votedCandidate['last_name'] : "NOT VOTED") . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
