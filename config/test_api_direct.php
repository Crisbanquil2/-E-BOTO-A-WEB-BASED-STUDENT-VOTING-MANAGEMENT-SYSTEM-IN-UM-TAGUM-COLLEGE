<?php
/**
 * Direct API Test
 * Test the voting API directly
 */

echo "<h1>Direct API Test</h1>";

// Test 1: Test voting API with GET request
echo "<h2>Test 1: GET request to voting_api.php</h2>";
$url = "http://localhost:8000/config/voting_api.php?action=get_voting_stats";
echo "Testing URL: $url<br>";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = file_get_contents($url, false, $context);
echo "Response: " . htmlspecialchars($response) . "<br><br>";

// Test 2: Test voting API with POST request
echo "<h2>Test 2: POST request to voting_api.php</h2>";
$url = "http://localhost:8000/config/voting_api.php?action=submit_vote";
echo "Testing URL: $url<br>";

$postData = json_encode([
    'studentId' => '2024-001',
    'candidateId' => '1',
    'position' => 'President'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);

$response = file_get_contents($url, false, $context);
echo "POST Data: " . htmlspecialchars($postData) . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br><br>";

// Test 3: Check if the file exists and is readable
echo "<h2>Test 3: File existence check</h2>";
$apiFile = __DIR__ . '/voting_api.php';
echo "API file path: $apiFile<br>";
echo "File exists: " . (file_exists($apiFile) ? 'Yes' : 'No') . "<br>";
echo "File readable: " . (is_readable($apiFile) ? 'Yes' : 'No') . "<br>";

// Test 4: Check database connection
echo "<h2>Test 4: Database connection</h2>";
try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "Database connection: ✓ Success<br>";
    
    // Check if we have students
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $studentCount = $stmt->fetch()['count'];
    echo "Students in database: $studentCount<br>";
    
    // Check if we have candidates
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates");
    $stmt->execute();
    $candidateCount = $stmt->fetch()['count'];
    echo "Candidates in database: $candidateCount<br>";
    
} catch (Exception $e) {
    echo "Database connection: ✗ Error - " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<h2>Test Complete</h2>";
?>