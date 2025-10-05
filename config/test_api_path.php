<?php
/**
 * Test API path accessibility
 */

echo "<h2>API Path Test</h2>";

echo "<h3>1. Test if login_api.php is accessible</h3>";

// Test direct access
$url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/login_api.php';
$response = @file_get_contents($url);

if ($response === false) {
    echo "❌ Cannot access login_api.php directly<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
} else {
    echo "✅ login_api.php is accessible<br>";
    echo "Response length: " . strlen($response) . " characters<br>";
}

echo "<h3>2. Test API with action parameter</h3>";

$url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/login_api.php?action=login';
$response = @file_get_contents($url);

if ($response === false) {
    echo "❌ Cannot access login_api.php with action parameter<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
} else {
    echo "✅ login_api.php with action parameter is accessible<br>";
    echo "Response: " . htmlspecialchars($response) . "<br>";
}

echo "<h3>3. Test from different directory</h3>";

// Simulate request from Login directory
$testData = json_encode(['studentId' => 'TEST001', 'password' => 'password123']);

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $testData
    ]
];

$context = stream_context_create($options);
$url = 'http://localhost/STUDENT%20VOTING%20MANAGEMENT%20SYSTEM/config/login_api.php?action=login';
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ POST request failed<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
} else {
    echo "✅ POST request successful<br>";
    echo "Response: " . htmlspecialchars($response) . "<br>";
    
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ Login test successful!<br>";
    } else {
        echo "❌ Login test failed: " . ($data['message'] ?? 'Unknown error') . "<br>";
    }
}

echo "<h3>4. Check file permissions</h3>";

$apiFile = 'login_api.php';
if (file_exists($apiFile)) {
    echo "✅ login_api.php exists<br>";
    echo "File size: " . filesize($apiFile) . " bytes<br>";
    echo "Readable: " . (is_readable($apiFile) ? "Yes" : "No") . "<br>";
} else {
    echo "❌ login_api.php not found<br>";
}

echo "<br><h3>Test Complete</h3>";
echo "<p>If all tests pass, the API is working and the issue is in the login form.</p>";
?>
