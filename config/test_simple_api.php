<?php
/**
 * Test the simple API
 */

echo "<h2>Testing Simple API</h2>";

// Test 1: Test GET candidates
echo "<h3>1. Testing GET candidates</h3>";
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/simple_candidates_api.php?action=get_candidates';
$response = file_get_contents($url);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['success'])) {
        echo "<p style='color: green;'>✓ GET candidates working</p>";
        echo "<p>Found " . $data['total'] . " candidates</p>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ GET candidates failed</p>";
        echo "<p>Response: " . $response . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Cannot reach API</p>";
}

// Test 2: Test POST add candidate
echo "<h3>2. Testing POST add candidate</h3>";
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

$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/simple_candidates_api.php';
$response = file_get_contents($url, false, $context);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "<p style='color: green;'>✓ POST add candidate working</p>";
        echo "<p>Added candidate with ID: " . $data['candidate_id'] . "</p>";
        
        // Clean up
        $deleteData = [
            'action' => 'delete_candidate',
            'candidate_id' => $data['candidate_id']
        ];
        
        $context2 = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($deleteData)
            ]
        ]);
        
        $deleteResponse = file_get_contents($url, false, $context2);
        echo "<p>✓ Test candidate cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>✗ POST add candidate failed</p>";
        echo "<p>Response: " . $response . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Cannot reach API for POST</p>";
}

echo "<h3>Test Complete!</h3>";
echo "<p><a href='../Admin/admin candidate/admincandidate.html' style='background: #d32f2f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Admin Candidate Management Now</a></p>";
?>