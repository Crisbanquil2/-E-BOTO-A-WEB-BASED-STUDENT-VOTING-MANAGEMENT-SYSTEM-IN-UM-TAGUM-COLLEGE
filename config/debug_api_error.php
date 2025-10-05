<?php
/**
 * Debug API Error - Check what's wrong with the API
 */

echo "<h2>Debug API Error</h2>";

// Test 1: Check if we can access the API
echo "<h3>1. Testing API Access</h3>";
$apiUrl = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/simple_api_v2.php?action=get_candidates';

echo "<p>Testing URL: $apiUrl</p>";

$response = file_get_contents($apiUrl);

if ($response === false) {
    echo "<p style='color: red;'>✗ Cannot access API</p>";
} else {
    echo "<p style='color: green;'>✓ API accessible</p>";
    echo "<h4>Raw Response:</h4>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Test if it's valid JSON
    $jsonData = json_decode($response, true);
    if ($jsonData === null) {
        echo "<p style='color: red;'>✗ Invalid JSON - This is the problem!</p>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Valid JSON</p>";
    }
}

// Test 2: Test POST request
echo "<h3>2. Testing POST Request</h3>";
$testData = [
    'action' => 'add_candidate',
    'data' => [
        'first_name' => 'Test',
        'last_name' => 'User',
        'position' => 'President',
        'gender' => 'Male',
        'course' => 'BSIT',
        'year_level' => '3rd Year',
        'description' => 'Test candidate',
        'photo' => null
    ]
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($testData)
    ]
]);

$apiUrl = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/simple_api_v2.php';
$response = file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo "<p style='color: red;'>✗ POST request failed</p>";
} else {
    echo "<p style='color: green;'>✓ POST request successful</p>";
    echo "<h4>POST Response:</h4>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Test if it's valid JSON
    $jsonData = json_decode($response, true);
    if ($jsonData === null) {
        echo "<p style='color: red;'>✗ Invalid JSON in POST response!</p>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Valid JSON in POST response</p>";
    }
}

echo "<h3>Debug Complete!</h3>";
?>
