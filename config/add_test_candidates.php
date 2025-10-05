<?php
/**
 * Add test candidates to the database
 */

require_once 'database.php';

echo "<h2>Add Test Candidates</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if candidates table exists and has data
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    
    echo "Current candidates in database: " . $count . "<br><br>";
    
    if ($count > 0) {
        echo "✅ Candidates already exist in database<br>";
        
        // Show existing candidates
        $stmt = $conn->prepare("SELECT candidate_id, first_name, last_name, position FROM candidates ORDER BY position, last_name");
        $stmt->execute();
        $candidates = $stmt->fetchAll();
        
        echo "<h3>Existing Candidates:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Position</th></tr>";
        foreach ($candidates as $candidate) {
            echo "<tr>";
            echo "<td>" . $candidate['candidate_id'] . "</td>";
            echo "<td>" . $candidate['first_name'] . " " . $candidate['last_name'] . "</td>";
            echo "<td>" . $candidate['position'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No candidates found. Adding test candidates...<br><br>";
        
        // Add test candidates
        $testCandidates = [
            ['John', 'Doe', 'President', 'MALE', 'BSIT', '3rd Year', 'Experienced leader with vision'],
            ['Jane', 'Smith', 'President', 'FEMALE', 'BSCS', '4th Year', 'Passionate about student welfare'],
            ['Mike', 'Johnson', 'Vice President', 'MALE', 'BSIT', '2nd Year', 'Strong organizational skills'],
            ['Sarah', 'Wilson', 'Vice President', 'FEMALE', 'BSCS', '3rd Year', 'Excellent communication skills'],
            ['David', 'Brown', 'Secretary', 'MALE', 'BSIT', '2nd Year', 'Detail-oriented and reliable'],
            ['Lisa', 'Davis', 'Secretary', 'FEMALE', 'BSCS', '3rd Year', 'Organized and efficient'],
            ['Chris', 'Garcia', 'Treasurer', 'MALE', 'BSIT', '4th Year', 'Financial management expertise'],
            ['Maria', 'Martinez', 'Treasurer', 'FEMALE', 'BSCS', '2nd Year', 'Budget planning skills']
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $added = 0;
        foreach ($testCandidates as $candidate) {
            try {
                $stmt->execute($candidate);
                $added++;
                echo "✅ Added: " . $candidate[0] . " " . $candidate[1] . " for " . $candidate[2] . "<br>";
            } catch (Exception $e) {
                echo "❌ Failed to add " . $candidate[0] . " " . $candidate[1] . ": " . $e->getMessage() . "<br>";
            }
        }
        
        echo "<br>Successfully added " . $added . " test candidates!<br>";
    }
    
    // Test the candidates API
    echo "<br><h3>Testing Candidates API:</h3>";
    $url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/candidates_api.php?action=get_candidates';
    $response = @file_get_contents($url);
    
    if ($response === false) {
        echo "❌ Candidates API failed<br>";
    } else {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "✅ Candidates API works!<br>";
            echo "Found " . $data['total'] . " candidates<br>";
            echo "Response: " . htmlspecialchars($response) . "<br>";
        } else {
            echo "❌ Candidates API error: " . ($data['message'] ?? 'Unknown error') . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Test Complete</h3>";
echo "<p>Now try the voting test again!</p>";
?>
