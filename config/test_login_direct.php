<?php
/**
 * Direct test of login API
 */

echo "<h2>Direct Login API Test</h2>";

// Test the login API directly
$testData = [
    'studentId' => 'TEST001',
    'password' => 'password123'
];

$url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/login_api.php?action=login';

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($testData)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<h3>API Response:</h3>";
echo "<pre>" . htmlspecialchars($result) . "</pre>";

$response = json_decode($result, true);

if ($response && $response['success']) {
    echo "<h3>✅ Login API is working!</h3>";
    echo "Student: " . $response['student']['first_name'] . " " . $response['student']['last_name'] . "<br>";
    echo "Student ID: " . $response['student']['student_number'] . "<br>";
} else {
    echo "<h3>❌ Login API failed</h3>";
    echo "Error: " . ($response['message'] ?? 'Unknown error') . "<br>";
}

echo "<br><h3>Try these credentials in the login form:</h3>";
echo "Student ID: TEST001<br>";
echo "Password: password123<br>";
echo "<a href='../Login/login.html' target='_blank'>Go to Login Page</a>";
?>
