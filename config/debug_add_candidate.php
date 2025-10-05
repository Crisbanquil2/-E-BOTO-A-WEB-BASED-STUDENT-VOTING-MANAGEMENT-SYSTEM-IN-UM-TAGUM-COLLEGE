<?php
/**
 * Debug script to test adding candidates
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Add Candidate</h2>";

// Test 1: Check if we can connect to database
echo "<h3>1. Testing Database Connection</h3>";
try {
    $host = 'localhost';
    $db_name = 'voting_system';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Test adding a candidate manually
echo "<h3>2. Testing Manual Add Candidate</h3>";
try {
    $testData = [
        'first_name' => 'Test',
        'last_name' => 'Candidate',
        'position' => 'President',
        'gender' => 'Male',
        'course' => 'BSIT',
        'year_level' => '3rd Year',
        'description' => 'Test candidate for debugging',
        'photo' => null
    ];
    
    $query = "INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description, photo) 
             VALUES (:first_name, :last_name, :position, :gender, :course, :year_level, :description, :photo)";
    
    $stmt = $conn->prepare($query);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $candidateId = $conn->lastInsertId();
        echo "<p style='color: green;'>✓ Successfully added test candidate with ID: $candidateId</p>";
        
        // Show the candidate
        $query = "SELECT * FROM candidates WHERE candidate_id = $candidateId";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h4>Added Candidate:</h4>";
        echo "<pre>" . json_encode($candidate, JSON_PRETTY_PRINT) . "</pre>";
        
        // Clean up
        $conn->query("DELETE FROM candidates WHERE candidate_id = $candidateId");
        echo "<p>✓ Test candidate cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>✗ Failed to add test candidate</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 3: Test the API endpoint
echo "<h3>3. Testing API Endpoint</h3>";
$testData = [
    'action' => 'add_candidate',
    'data' => [
        'first_name' => 'API',
        'last_name' => 'Test',
        'position' => 'Vice President',
        'gender' => 'Female',
        'course' => 'BSCS',
        'year_level' => '2nd Year',
        'description' => 'API test candidate',
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
        echo "<p style='color: green;'>✓ API add candidate working</p>";
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
        echo "<p>✓ API test candidate cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>✗ API add candidate failed</p>";
        echo "<p>Response: " . $response . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Cannot reach API</p>";
}

echo "<h3>Debug Complete!</h3>";
echo "<p>If all tests pass, the issue might be in the admin form JavaScript.</p>";
?>
