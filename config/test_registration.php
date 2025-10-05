<?php
/**
 * Test script to verify registration and voter list functionality
 */

require_once 'database.php';

echo "<h2>Testing Student Registration and Voter List</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h3>1. Database Connection Test</h3>";
    echo "✅ Database connection successful<br><br>";
    
    // Test registration API
    echo "<h3>2. Testing Registration API</h3>";
    
    $testStudent = [
        'studentId' => 'TEST001',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@test.com',
        'course' => 'BSIT',
        'yearLevel' => '1ST',
        'gender' => 'MALE',
        'password' => 'password123'
    ];
    
    // Include the registration API
    require_once 'registration_api.php';
    $api = new RegistrationAPI();
    
    $result = $api->registerStudent($testStudent);
    
    if ($result['success']) {
        echo "✅ Registration API test successful<br>";
        echo "Student ID: " . $result['student_id'] . "<br><br>";
    } else {
        echo "❌ Registration API test failed: " . $result['message'] . "<br><br>";
    }
    
    // Test voter list API
    echo "<h3>3. Testing Voter List API</h3>";
    
    require_once 'voter_list_api.php';
    $voterApi = new VoterListAPI();
    
    $voters = $voterApi->getVoters();
    
    if ($voters['success']) {
        echo "✅ Voter List API test successful<br>";
        echo "Total voters found: " . $voters['total'] . "<br>";
        
        if ($voters['total'] > 0) {
            echo "<h4>Voters in database:</h4>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Student ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Gender</th><th>Status</th></tr>";
            
            foreach ($voters['data'] as $voter) {
                echo "<tr>";
                echo "<td>" . ($voter['student_number'] ?? $voter['studentId'] ?? 'N/A') . "</td>";
                echo "<td>" . $voter['first_name'] . " " . $voter['last_name'] . "</td>";
                echo "<td>" . $voter['email'] . "</td>";
                echo "<td>" . $voter['course'] . "</td>";
                echo "<td>" . $voter['year_level'] . "</td>";
                echo "<td>" . ($voter['gender'] ?? 'N/A') . "</td>";
                echo "<td>" . $voter['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "⚠️ No voters found in database<br>";
        }
    } else {
        echo "❌ Voter List API test failed: " . $voters['message'] . "<br>";
    }
    
    // Test login API
    echo "<h3>4. Testing Login API</h3>";
    
    require_once 'login_api.php';
    $loginApi = new LoginAPI();
    
    $loginResult = $loginApi->authenticateStudent('TEST001', 'password123');
    
    if ($loginResult['success']) {
        echo "✅ Login API test successful<br>";
        echo "Student logged in: " . $loginResult['student']['first_name'] . " " . $loginResult['student']['last_name'] . "<br><br>";
    } else {
        echo "❌ Login API test failed: " . $loginResult['message'] . "<br><br>";
    }
    
    echo "<h3>5. Database Schema Check</h3>";
    
    // Check if gender and password columns exist
    $stmt = $conn->prepare("DESCRIBE students");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    $hasGender = false;
    $hasPassword = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'gender') $hasGender = true;
        if ($column['Field'] === 'password') $hasPassword = true;
    }
    
    echo "Gender column exists: " . ($hasGender ? "✅ Yes" : "❌ No") . "<br>";
    echo "Password column exists: " . ($hasPassword ? "✅ Yes" : "❌ No") . "<br>";
    
    if (!$hasGender || !$hasPassword) {
        echo "<br><strong>⚠️ Database schema needs to be updated!</strong><br>";
        echo "Please run the SQL commands in 'update_students_table.sql' to add missing columns.<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Test Complete</h3>";
echo "<p>If you see any ❌ errors above, please fix them before testing the registration form.</p>";
?>
