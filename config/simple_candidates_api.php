<?php
/**
 * Simple Candidates API - Fixed Version
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Database connection
    $host = 'localhost';
    $db_name = 'voting_system';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'get_candidates';
        
        switch ($action) {
            case 'get_candidates':
                $query = "SELECT * FROM candidates ORDER BY position, last_name, first_name";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Group by position
                $grouped = [];
                foreach ($candidates as $candidate) {
                    $position = $candidate['position'];
                    if (!isset($grouped[$position])) {
                        $grouped[$position] = [];
                    }
                    $grouped[$position][] = $candidate;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $grouped,
                    'total' => count($candidates)
                ]);
                break;
                
            case 'get_results':
                $query = "SELECT c.candidate_id, c.first_name, c.last_name, c.position, COUNT(v.vote_id) as vote_count
                         FROM candidates c
                         LEFT JOIN votes v ON c.candidate_id = v.candidate_id
                         GROUP BY c.candidate_id, c.position
                         ORDER BY c.position, vote_count DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Group by position
                $grouped = [];
                foreach ($results as $result) {
                    $position = $result['position'];
                    if (!isset($grouped[$position])) {
                        $grouped[$position] = [];
                    }
                    $grouped[$position][] = $result;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $grouped
                ]);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
        }
    }
    
    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON input'
            ]);
            exit;
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'add_candidate':
                $data = $input['data'] ?? [];
                
                if (empty($data['first_name']) || empty($data['last_name']) || empty($data['position'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Missing required fields'
                    ]);
                    exit;
                }
                
                $query = "INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description, photo) 
                         VALUES (:first_name, :last_name, :position, :gender, :course, :year_level, :description, :photo)";
                
                $stmt = $conn->prepare($query);
                $result = $stmt->execute([
                    ':first_name' => $data['first_name'],
                    ':last_name' => $data['last_name'],
                    ':position' => $data['position'],
                    ':gender' => $data['gender'] ?? '',
                    ':course' => $data['course'] ?? '',
                    ':year_level' => $data['year_level'] ?? '',
                    ':description' => $data['description'] ?? '',
                    ':photo' => $data['photo'] ?? null
                ]);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Candidate added successfully',
                        'candidate_id' => $conn->lastInsertId()
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to add candidate'
                    ]);
                }
                break;
                
            case 'delete_candidate':
                $candidate_id = $input['candidate_id'] ?? 0;
                
                if (!$candidate_id) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid candidate ID'
                    ]);
                    exit;
                }
                
                $query = "DELETE FROM candidates WHERE candidate_id = :candidate_id";
                $stmt = $conn->prepare($query);
                $result = $stmt->execute([':candidate_id' => $candidate_id]);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Candidate deleted successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to delete candidate'
                    ]);
                }
                break;
                
            case 'submit_vote':
                $student_id = $input['student_id'] ?? 0;
                $candidate_id = $input['candidate_id'] ?? 0;
                $position = $input['position'] ?? '';
                
                if (!$student_id || !$candidate_id || !$position) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Missing required vote data'
                    ]);
                    exit;
                }
                
                // Check if already voted for this position
                $checkQuery = "SELECT vote_id FROM votes WHERE student_id = :student_id AND position = :position";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->execute([':student_id' => $student_id, ':position' => $position]);
                
                if ($checkStmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'You have already voted for this position'
                    ]);
                    exit;
                }
                
                // Insert vote
                $query = "INSERT INTO votes (student_id, candidate_id, position, ip_address, user_agent) 
                         VALUES (:student_id, :candidate_id, :position, :ip_address, :user_agent)";
                
                $stmt = $conn->prepare($query);
                $result = $stmt->execute([
                    ':student_id' => $student_id,
                    ':candidate_id' => $candidate_id,
                    ':position' => $position,
                    ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Vote submitted successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to submit vote'
                    ]);
                }
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>