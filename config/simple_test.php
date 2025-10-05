<?php
// Simple test to check if the API is working
echo "API Test - " . date('Y-m-d H:i:s') . "\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Action: " . ($_GET['action'] ?? 'none') . "\n";
echo "GET params: " . print_r($_GET, true) . "\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    echo "POST data: " . $input . "\n";
}

// Test database
try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "Database: OK\n";
} catch (Exception $e) {
    echo "Database: ERROR - " . $e->getMessage() . "\n";
}

echo "Test complete\n";
?>
