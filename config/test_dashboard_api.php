<?php
/**
 * Test Dashboard API Script
 * This script tests if the dashboard API is working correctly
 */

// Database configuration
$host = 'localhost';
$dbname = 'voting_system';
$username = 'root';
$password = '';

echo "<h2>Testing Dashboard API...</h2>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test each query that the dashboard API uses
    echo "<h3>Testing Database Queries:</h3>";
    
    // Test students table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total_voters FROM students");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Students table: {$result['total_voters']} students</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Students table error: " . $e->getMessage() . "</p>";
    }
    
    // Test active voters
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as active_voters FROM students WHERE status = 'active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Active voters: {$result['active_voters']}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Active voters error: " . $e->getMessage() . "</p>";
    }
    
    // Test candidates table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total_candidates FROM candidates");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Candidates table: {$result['total_candidates']} candidates</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Candidates table error: " . $e->getMessage() . "</p>";
    }
    
    // Test votes table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as votes_cast FROM votes");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Votes table: {$result['votes_cast']} votes</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Votes table error: " . $e->getMessage() . "</p>";
    }
    
    // Test voting_sessions table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as active_elections FROM voting_sessions WHERE status = 'active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Voting sessions: {$result['active_elections']} active</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Voting sessions error: " . $e->getMessage() . "</p>";
    }
    
    // Test voted students
    try {
        $stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) as voted FROM votes");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Students who voted: {$result['voted']}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Voted students error: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Testing Dashboard API Endpoint:</h3>";
    
    // Test the actual API endpoint
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/dashboard_stats_api.php';
    echo "<p>API URL: <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($apiUrl, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✓ Dashboard API is working correctly!</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
            echo json_encode($data, JSON_PRETTY_PRINT);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>✗ Dashboard API returned error:</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
            echo $response;
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red;'>✗ Could not connect to dashboard API</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Database Connection Failed!</h3>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>MySQL server is running</li>";
    echo "<li>Database 'voting_system' exists</li>";
    echo "<li>Tables are created (run create_database.php first)</li>";
    echo "</ul>";
}
?>
