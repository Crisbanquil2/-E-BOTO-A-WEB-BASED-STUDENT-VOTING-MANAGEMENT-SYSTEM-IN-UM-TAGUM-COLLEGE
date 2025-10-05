<?php
/**
 * Check Candidates in Database
 */

// Set JSON header
header('Content-Type: application/json');

try {
    // Database connection
    $host = 'localhost';
    $db_name = 'voting_system';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if candidates table exists
    $query = "SHOW TABLES LIKE 'candidates'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo json_encode([
            'success' => false,
            'message' => 'Candidates table does not exist'
        ]);
        exit();
    }
    
    // Get all candidates
    $query = "SELECT * FROM candidates ORDER BY position, first_name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Candidates found',
        'count' => count($candidates),
        'candidates' => $candidates
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>