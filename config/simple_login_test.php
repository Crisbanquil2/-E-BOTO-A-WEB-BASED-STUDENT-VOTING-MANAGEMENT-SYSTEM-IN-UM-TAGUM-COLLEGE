<?php
/**
 * Simple login test to verify API is working
 */

echo "<h2>Simple Login Test</h2>";

// Test with TEST001 which we know works
$testCredentials = [
    'studentId' => 'TEST001',
    'password' => 'password123'
];

echo "<h3>Testing with TEST001</h3>";
echo "Student ID: " . $testCredentials['studentId'] . "<br>";
echo "Password: " . $testCredentials['password'] . "<br><br>";

// Test the login API directly using the class
require_once 'login_api.php';

$loginApi = new LoginAPI();
$result = $loginApi->authenticateStudent('TEST001', 'password123');

echo "<h3>Direct API Test Result:</h3>";
echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";

if ($result['success']) {
    echo "<h3>✅ SUCCESS! Login API is working correctly</h3>";
    echo "Student: " . $result['student']['first_name'] . " " . $result['student']['last_name'] . "<br>";
    echo "Student ID: " . $result['student']['student_number'] . "<br>";
    echo "Email: " . $result['student']['email'] . "<br>";
} else {
    echo "<h3>❌ FAILED</h3>";
    echo "Error: " . ($result['message'] ?? 'Unknown error') . "<br>";
}

echo "<br><h3>Test via HTTP Request:</h3>";

// Test via HTTP request
$url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/login_api.php?action=login';
$data = json_encode($testCredentials);

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$httpResult = file_get_contents($url, false, $context);

echo "<h4>HTTP Response:</h4>";
echo "<pre>" . htmlspecialchars($httpResult) . "</pre>";

$httpResponse = json_decode($httpResult, true);

if ($httpResponse && $httpResponse['success']) {
    echo "<h4>✅ HTTP Test Successful!</h4>";
} else {
    echo "<h4>❌ HTTP Test Failed: " . ($httpResponse['message'] ?? 'Unknown error') . "</h4>";
}

echo "<br><h3>Next Steps:</h3>";
echo "1. If both tests work, the API is fine and the problem is in the login form<br>";
echo "2. If HTTP test fails, there's a server configuration issue<br>";
echo "3. Try the login form with TEST001 / password123<br>";
echo "<a href='../Login/login.html' target='_blank'>Go to Login Page</a>";
?>
