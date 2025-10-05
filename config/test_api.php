<?php
/**
 * Test script for Candidates API
 * This script tests the API endpoints to ensure they're working correctly
 */

echo "<h2>Testing Candidates API</h2>";

// Test 1: Get candidates
echo "<h3>Test 1: Getting candidates</h3>";
$response = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/candidates_api.php?action=get_candidates');
$data = json_decode($response, true);

if ($data && isset($data['success'])) {
    echo "<p style='color: green;'>✓ API is responding</p>";
    echo "<p>Response: " . json_encode($data, JSON_PRETTY_PRINT) . "</p>";
} else {
    echo "<p style='color: red;'>✗ API is not responding correctly</p>";
    echo "<p>Response: " . $response . "</p>";
}

// Test 2: Add a sample candidate
echo "<h3>Test 2: Adding a sample candidate</h3>";
$sampleCandidate = [
    'action' => 'add_candidate',
    'data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'position' => 'President',
        'gender' => 'Male',
        'course' => 'BSIT',
        'year_level' => '3rd Year',
        'description' => 'Sample candidate for testing',
        'photo' => null
    ]
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($sampleCandidate)
    ]
]);

$response = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/candidates_api.php', false, $context);
$data = json_decode($response, true);

if ($data && isset($data['success']) && $data['success']) {
    echo "<p style='color: green;'>✓ Sample candidate added successfully</p>";
    echo "<p>Response: " . json_encode($data, JSON_PRETTY_PRINT) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Failed to add sample candidate</p>";
    echo "<p>Response: " . $response . "</p>";
}

// Test 3: Get candidates again to verify
echo "<h3>Test 3: Verifying candidate was added</h3>";
$response = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/candidates_api.php?action=get_candidates');
$data = json_decode($response, true);

if ($data && isset($data['success']) && $data['success']) {
    $candidateCount = 0;
    foreach ($data['data'] as $positionCandidates) {
        $candidateCount += count($positionCandidates);
    }
    echo "<p style='color: green;'>✓ Found $candidateCount candidates in the system</p>";
    
    if ($candidateCount > 0) {
        echo "<p>Sample candidate data:</p>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to retrieve candidates</p>";
}

echo "<h3>API Test Complete!</h3>";
echo "<p>If all tests passed, your voting system is ready to use.</p>";
echo "<p><a href='../Dasboard for Student or user/index.html'>Go to Student Dashboard</a></p>";
echo "<p><a href='../Admin/admin candidate/admincandidate.html'>Go to Admin Candidate Management</a></p>";
?>
