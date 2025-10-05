<?php
/**
 * Direct API Test - Test the voting APIs directly
 */

echo "<h1>Direct API Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Test 1: Candidates API
echo "<div class='test'>";
echo "<h2>Test 1: Candidates API</h2>";
try {
    $url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/candidates_api.php?action=get_candidates';
    $response = file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "<div class='success'>";
            echo "<p>✅ Candidates API is working!</p>";
            echo "<p>Found " . count($data['candidates']) . " candidates</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<p>❌ Candidates API returned error: " . ($data['message'] ?? 'Unknown error') . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='error'>";
        echo "<p>❌ Could not connect to Candidates API</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>❌ Candidates API test failed: " . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</div>";

// Test 2: Voting API
echo "<div class='test'>";
echo "<h2>Test 2: Voting API</h2>";
try {
    $url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=get_voting_stats';
    $response = file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "<div class='success'>";
            echo "<p>✅ Voting API is working!</p>";
            echo "<p>Total votes: " . $data['stats']['total_votes'] . "</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<p>❌ Voting API returned error: " . ($data['message'] ?? 'Unknown error') . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='error'>";
        echo "<p>❌ Could not connect to Voting API</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>❌ Voting API test failed: " . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</div>";

// Test 3: Direct database test
echo "<div class='test'>";
echo "<h2>Test 3: Direct Database Test</h2>";
try {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    // Test candidates
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates");
    $stmt->execute();
    $candidateCount = $stmt->fetch()['count'];
    
    // Test votes
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes");
    $stmt->execute();
    $voteCount = $stmt->fetch()['count'];
    
    // Test students
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $studentCount = $stmt->fetch()['count'];
    
    echo "<div class='success'>";
    echo "<p>✅ Database connection successful!</p>";
    echo "<p>Candidates: $candidateCount</p>";
    echo "<p>Votes: $voteCount</p>";
    echo "<p>Students: $studentCount</p>";
    echo "</div>";
    
    if ($voteCount == 0) {
        echo "<div class='error'>";
        echo "<p>⚠️ No votes found in database. You need to vote first!</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>❌ Database test failed: " . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</div>";

echo "<div class='test'>";
echo "<h2>Quick Fix Instructions</h2>";
echo "<ol>";
echo "<li>If you see 'No votes found', you need to <a href='Voting/voting.html'>vote first</a></li>";
echo "<li>Try the fixed voting status page: <a href='Voting Status/voting_status_fixed.html'>voting_status_fixed.html</a></li>";
echo "<li>Check your browser console (F12) for any JavaScript errors</li>";
echo "<li>Make sure you're logged in with the correct student account</li>";
echo "</ol>";
echo "</div>";
?>
