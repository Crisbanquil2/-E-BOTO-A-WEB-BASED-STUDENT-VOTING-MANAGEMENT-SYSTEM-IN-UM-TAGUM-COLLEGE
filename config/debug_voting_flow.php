<?php
/**
 * Debug the complete voting flow
 */

echo "<h2>Debug Voting Flow</h2>";

// Test 1: Check if candidates API works
echo "<h3>1. Test Candidates API</h3>";
$candidatesUrl = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/candidates_api.php?action=get_candidates';
$candidatesResponse = @file_get_contents($candidatesUrl);

if ($candidatesResponse === false) {
    echo "❌ Candidates API failed<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
} else {
    echo "✅ Candidates API works<br>";
    $candidatesData = json_decode($candidatesResponse, true);
    if ($candidatesData && isset($candidatesData['candidates'])) {
        echo "Found " . count($candidatesData['candidates']) . " candidates<br>";
    } else {
        echo "Response: " . htmlspecialchars($candidatesResponse) . "<br>";
    }
}

// Test 2: Check if voting API works
echo "<h3>2. Test Voting API</h3>";
$votingUrl = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=get_voting_stats';
$votingResponse = @file_get_contents($votingUrl);

if ($votingResponse === false) {
    echo "❌ Voting API failed<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
} else {
    echo "✅ Voting API works<br>";
    $votingData = json_decode($votingResponse, true);
    if ($votingData && $votingData['success']) {
        echo "Voting stats loaded successfully<br>";
    } else {
        echo "Response: " . htmlspecialchars($votingResponse) . "<br>";
    }
}

// Test 3: Test submit vote
echo "<h3>3. Test Submit Vote</h3>";
$submitUrl = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/voting_api.php?action=submit_vote';
$submitData = json_encode(['studentId' => 'TEST001', 'candidateId' => 1, 'position' => 'President']);

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $submitData
    ]
];

$context = stream_context_create($options);
$submitResponse = @file_get_contents($submitUrl, false, $context);

if ($submitResponse === false) {
    echo "❌ Submit vote failed<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
} else {
    echo "✅ Submit vote works<br>";
    echo "Response: " . htmlspecialchars($submitResponse) . "<br>";
}

// Test 4: Check database connection
echo "<h3>4. Test Database Connection</h3>";
try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "✅ Database connection works<br>";
    
    // Check if candidates table has data
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    echo "Candidates in database: " . $count . "<br>";
    
    // Check if students table has data
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    echo "Students in database: " . $count . "<br>";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Debug Complete</h3>";
echo "<p>If any tests fail, those need to be fixed before voting will work.</p>";
?>
