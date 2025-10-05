<?php
/**
 * Quick test to verify the API is working
 */

echo "<h2>Quick API Test</h2>";

// Test 1: Test database connection
echo "<h3>1. Testing Database Connection</h3>";
try {
    require_once 'database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Test adding a candidate
echo "<h3>2. Testing Add Candidate</h3>";
try {
    $testData = [
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'position' => 'President',
        'gender' => 'Male',
        'course' => 'BSIT',
        'year_level' => '3rd Year',
        'description' => 'Test candidate',
        'photo' => null
    ];
    
    $query = "INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description, photo) 
             VALUES (:first_name, :last_name, :position, :gender, :course, :year_level, :description, :photo)";
    
    $stmt = $conn->prepare($query);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $candidateId = $conn->lastInsertId();
        echo "<p style='color: green;'>✓ Successfully added test candidate with ID: $candidateId</p>";
        
        // Test 3: Test getting candidates
        echo "<h3>3. Testing Get Candidates</h3>";
        $query = "SELECT * FROM candidates ORDER BY position, last_name, first_name";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p style='color: green;'>✓ Found " . count($candidates) . " candidates</p>";
        
        if (count($candidates) > 0) {
            echo "<h4>Sample Candidate:</h4>";
            echo "<pre>" . json_encode($candidates[0], JSON_PRETTY_PRINT) . "</pre>";
        }
        
        // Clean up
        $conn->query("DELETE FROM candidates WHERE candidate_id = $candidateId");
        echo "<p>✓ Test candidate cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>✗ Failed to add test candidate</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 4: Test API endpoint
echo "<h3>4. Testing API Endpoint</h3>";
try {
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/candidates_api.php?action=get_candidates';
    $response = file_get_contents($apiUrl);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "<p style='color: green;'>✓ API endpoint is working</p>";
            echo "<p>Response: " . json_encode($data, JSON_PRETTY_PRINT) . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠ API responded but with unexpected format</p>";
            echo "<p>Response: " . $response . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ API endpoint not accessible</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ API test failed: " . $e->getMessage() . "</p>";
}

echo "<h3>Test Complete!</h3>";
echo "<p><strong>If all tests show green checkmarks, your API is working!</strong></p>";
echo "<p><a href='../Admin/admin candidate/admincandidate.html' style='background: #d32f2f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Candidate Management</a></p>";
?>
