<?php
/**
 * Simple test of voting API
 */

echo "<h2>Simple Voting API Test</h2>";

// Test the voting API directly
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'get_voting_stats';

echo "<h3>Testing get_voting_stats action</h3>";

// Capture the output
ob_start();
include 'voting_api.php';
$output = ob_get_clean();

echo "<h4>API Response:</h4>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

$response = json_decode($output, true);

if ($response && $response['success']) {
    echo "<h4>✅ SUCCESS! Voting API is working</h4>";
    echo "Stats: " . json_encode($response, JSON_PRETTY_PRINT) . "<br>";
} else {
    echo "<h4>❌ FAILED</h4>";
    echo "Error: " . ($response['message'] ?? 'Unknown error') . "<br>";
}

echo "<br><h3>Test submit_vote action</h3>";

// Test submit_vote
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'submit_vote';

$testData = json_encode(['studentId' => 'TEST001', 'candidateId' => 1, 'position' => 'President']);
file_put_contents('php://input', $testData);

ob_start();
include 'voting_api.php';
$output = ob_get_clean();

echo "<h4>Submit Vote Response:</h4>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

$response = json_decode($output, true);

if ($response && $response['success']) {
    echo "<h4>✅ SUCCESS! Submit vote is working</h4>";
} else {
    echo "<h4>❌ FAILED</h4>";
    echo "Error: " . ($response['message'] ?? 'Unknown error') . "<br>";
}

echo "<br><h3>Test Complete</h3>";
?>
