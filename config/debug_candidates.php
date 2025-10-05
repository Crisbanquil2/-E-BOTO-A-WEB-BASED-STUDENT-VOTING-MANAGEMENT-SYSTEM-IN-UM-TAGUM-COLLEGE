<?php
/**
 * Debug script for candidate API issues
 */

echo "<h2>Debugging Candidate API</h2>";

// Test 1: Check if database connection works
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

// Test 2: Check if candidates table exists and is accessible
echo "<h3>2. Testing Candidates Table</h3>";
try {
    $stmt = $conn->query("DESCRIBE candidates");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color: green;'>✓ Candidates table exists with " . count($columns) . " columns</p>";
    echo "<p>Columns: " . implode(', ', array_column($columns, 'Field')) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Candidates table error: " . $e->getMessage() . "</p>";
}

// Test 3: Test inserting a sample candidate
echo "<h3>3. Testing Candidate Insert</h3>";
try {
    $testCandidate = [
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
    $result = $stmt->execute($testCandidate);
    
    if ($result) {
        $candidateId = $conn->lastInsertId();
        echo "<p style='color: green;'>✓ Successfully inserted test candidate with ID: $candidateId</p>";
        
        // Clean up test data
        $conn->query("DELETE FROM candidates WHERE candidate_id = $candidateId");
        echo "<p>✓ Test candidate cleaned up</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to insert test candidate</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Insert test failed: " . $e->getMessage() . "</p>";
}

// Test 4: Test the API endpoint
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

// Test 5: Check file permissions
echo "<h3>5. Checking File Permissions</h3>";
$files = ['candidates_api.php', 'database.php', 'candidates_database.sql'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        echo "<p>✓ $file exists (permissions: " . substr(sprintf('%o', $perms), -4) . ")</p>";
    } else {
        echo "<p style='color: red;'>✗ $file not found</p>";
    }
}

echo "<h3>Debug Complete!</h3>";
echo "<p>If all tests pass, the issue might be in the admin form submission.</p>";
echo "<p><a href='../Admin/admin candidate/admincandidate.html'>Go back to Admin Candidate Management</a></p>";
?>
