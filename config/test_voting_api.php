<?php
/**
 * Test voting API to check if it's working
 */

echo "<h2>Test Voting API</h2>";

// Test if voting_api.php exists
if (file_exists('voting_api.php')) {
    echo "✅ voting_api.php exists<br>";
} else {
    echo "❌ voting_api.php not found<br>";
}

// Test direct API call
echo "<h3>Testing API Actions</h3>";

$actions = ['get_voting_stats', 'get_leading_candidates', 'submit_vote', 'get_student_votes', 'check_vote_status'];

foreach ($actions as $action) {
    echo "<h4>Testing action: $action</h4>";
    
    $url = "http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=$action";
    
    if ($action === 'submit_vote') {
        // Test POST request
        $data = json_encode(['studentId' => 'TEST001', 'candidateId' => 1, 'position' => 'President']);
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => $data
            ]
        ];
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
    } else {
        // Test GET request
        $result = @file_get_contents($url);
    }
    
    if ($result === false) {
        echo "❌ Failed to call API<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    } else {
        echo "✅ API call successful<br>";
        echo "Response: " . htmlspecialchars($result) . "<br>";
    }
    
    echo "<br>";
}

echo "<h3>Test Complete</h3>";
echo "<p>If you see any ❌ errors above, those need to be fixed.</p>";
?>
