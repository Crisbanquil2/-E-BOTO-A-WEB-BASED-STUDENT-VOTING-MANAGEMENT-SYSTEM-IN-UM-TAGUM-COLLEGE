<?php
/**
 * Check Available Candidates
 */

require_once 'config/database.php';

echo "<h1>Available Candidates</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get all candidates
    $query = "SELECT candidate_id, first_name, last_name, position, status FROM candidates ORDER BY position, last_name, first_name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($candidates) > 0) {
        echo "<h2>Candidates in Database (" . count($candidates) . " total)</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Name</th><th>Position</th><th>Status</th><th>Action</th>";
        echo "</tr>";
        
        foreach ($candidates as $candidate) {
            $statusColor = $candidate['status'] === 'active' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>" . $candidate['candidate_id'] . "</td>";
            echo "<td>" . $candidate['first_name'] . " " . $candidate['last_name'] . "</td>";
            echo "<td>" . $candidate['position'] . "</td>";
            echo "<td style='color: $statusColor;'>" . $candidate['status'] . "</td>";
            echo "<td>";
            if ($candidate['status'] === 'active') {
                echo "<button onclick='testVote(" . $candidate['candidate_id'] . ", \"" . $candidate['position'] . "\")'>Test Vote</button>";
            } else {
                echo "Inactive";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show active candidates by position
        echo "<h2>Active Candidates by Position</h2>";
        $activeCandidates = array_filter($candidates, function($c) { return $c['status'] === 'active'; });
        
        if (count($activeCandidates) > 0) {
            $grouped = [];
            foreach ($activeCandidates as $candidate) {
                $position = $candidate['position'];
                if (!isset($grouped[$position])) {
                    $grouped[$position] = [];
                }
                $grouped[$position][] = $candidate;
            }
            
            foreach ($grouped as $position => $positionCandidates) {
                echo "<h3>$position</h3>";
                echo "<ul>";
                foreach ($positionCandidates as $candidate) {
                    echo "<li>ID: " . $candidate['candidate_id'] . " - " . $candidate['first_name'] . " " . $candidate['last_name'] . "</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p style='color: red;'>No active candidates found!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>No candidates found in database!</p>";
        echo "<p><a href='config/add_sample_data.php'>Add Sample Candidates</a></p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<script>
function testVote(candidateId, position) {
    if (confirm(`Test voting for candidate ID ${candidateId} for ${position}?`)) {
        // Make AJAX request to test voting
        fetch('config/simple_api_v2.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'submit_vote',
                student_id: 1,
                candidate_id: candidateId,
                position: position
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Vote test successful: ' + data.message);
            } else {
                alert('Vote test failed: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}
</script>
