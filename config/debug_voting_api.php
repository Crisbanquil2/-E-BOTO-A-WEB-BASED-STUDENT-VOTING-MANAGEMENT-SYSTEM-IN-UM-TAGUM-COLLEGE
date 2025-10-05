<?php
/**
 * Debug Voting API
 * Simple debug version to test voting functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo "Debug Voting API - Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "GET params: " . print_r($_GET, true) . "\n";
echo "POST data: " . file_get_contents('php://input') . "\n";

$action = $_GET['action'] ?? '';

echo "Action received: '$action'\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    echo "Parsed POST data: " . print_r($input, true) . "\n";
}

// Test database connection
try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "Database connection: SUCCESS\n";
    
    // Test a simple query
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Students count: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

// Return a simple response
$response = [
    'success' => true,
    'message' => 'Debug API working',
    'method' => $_SERVER['REQUEST_METHOD'],
    'action' => $action,
    'timestamp' => date('Y-m-d H:i:s')
];

echo "\nResponse: " . json_encode($response, JSON_PRETTY_PRINT);
?>
