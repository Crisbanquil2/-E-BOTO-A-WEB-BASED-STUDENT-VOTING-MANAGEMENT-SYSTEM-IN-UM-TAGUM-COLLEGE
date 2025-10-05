<?php
/**
 * Simple test to check action parameter handling
 */

echo "<h2>Action Parameter Test</h2>";

echo "<h3>1. Test with action in URL:</h3>";
$url1 = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=submit_vote';
echo "URL: " . $url1 . "<br>";

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

$response1 = @file_get_contents($url1, false, $context);
echo "Response: " . htmlspecialchars($response1) . "<br><br>";

echo "<h3>2. Test with action in POST data:</h3>";
$url2 = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php';

$postData2 = json_encode([
    'action' => 'submit_vote',
    'studentId' => 'TEST001',
    'candidateId' => 1,
    'position' => 'President'
]);

$context2 = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData2
    ]
]);

$response2 = @file_get_contents($url2, false, $context2);
echo "Response: " . htmlspecialchars($response2) . "<br><br>";

echo "<h3>3. Test GET request:</h3>";
$url3 = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=get_voting_stats';
$response3 = @file_get_contents($url3);
echo "Response: " . htmlspecialchars($response3) . "<br><br>";

echo "<h3>4. Test with no action:</h3>";
$url4 = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php';
$response4 = @file_get_contents($url4);
echo "Response: " . htmlspecialchars($response4) . "<br>";
?>
