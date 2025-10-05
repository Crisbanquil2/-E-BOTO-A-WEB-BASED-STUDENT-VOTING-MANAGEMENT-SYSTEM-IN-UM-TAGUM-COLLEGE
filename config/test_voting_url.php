<?php
/**
 * Test what parameters are being received by the voting API
 */

echo "<h2>Test Voting API URL Parameters</h2>";

echo "<h3>GET Parameters:</h3>";
echo "<pre>" . print_r($_GET, true) . "</pre>";

echo "<h3>POST Data:</h3>";
$postData = file_get_contents('php://input');
echo "Raw POST data: " . htmlspecialchars($postData) . "<br>";
$decodedPost = json_decode($postData, true);
echo "Decoded POST data: <pre>" . print_r($decodedPost, true) . "</pre>";

echo "<h3>Request Method:</h3>";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";

echo "<h3>Request URI:</h3>";
echo "URI: " . $_SERVER['REQUEST_URI'] . "<br>";

echo "<h3>Query String:</h3>";
echo "Query: " . ($_SERVER['QUERY_STRING'] ?? 'None') . "<br>";

echo "<h3>Test with action parameter:</h3>";
echo "Action from GET: " . ($_GET['action'] ?? 'NOT SET') . "<br>";

// Test the actual voting API call
echo "<br><h3>Testing actual API call:</h3>";
$url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=submit_vote';
echo "URL: " . $url . "<br>";

$postData = json_encode([
    'studentId' => 'TEST001',
    'candidateId' => 1,
    'position' => 'President'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);

$response = @file_get_contents($url, false, $context);
echo "Response: " . htmlspecialchars($response) . "<br>";
?>
