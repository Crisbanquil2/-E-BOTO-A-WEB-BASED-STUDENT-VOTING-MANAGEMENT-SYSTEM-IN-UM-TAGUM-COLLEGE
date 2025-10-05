<?php
/**
 * Simple API Version 2 - More robust error handling
 */

// Start output buffering to catch any errors
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to send JSON response
function sendResponse($data) {
    // Clear any output before sending JSON
    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Function to send error response
function sendError($message, $code = 400) {
    http_response_code($code);
    sendResponse([
        'success' => false,
        'message' => $message
    ]);
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
        
        if ($action === 'get_candidates') {
            $query = "SELECT * FROM candidates ORDER BY last_name, first_name";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Define position order
            $positionOrder = [
                'President' => 1,
                'Vice President' => 2,
                'Mayor' => 3,
                'Vice Mayor' => 4,
                'Secretary' => 5,
                'Auditor' => 6,
                'Treasurer' => 7,
                'P.I.O.' => 8
            ];
            
            // Group by position
            $grouped = [];
            foreach ($candidates as $candidate) {
                $position = $candidate['position'];
                if (!isset($grouped[$position])) {
                    $grouped[$position] = [];
                }
                $grouped[$position][] = $candidate;
            }
            
            // Sort positions according to defined order
            uksort($grouped, function($a, $b) use ($positionOrder) {
                $orderA = $positionOrder[$a] ?? 999;
                $orderB = $positionOrder[$b] ?? 999;
                return $orderA - $orderB;
            });
            
            sendResponse([
                'success' => true,
                'data' => $grouped,
                'total' => count($candidates)
            ]);
        }
        
        if ($action === 'get_results') {
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
            
            sendResponse([
                'success' => true,
                'data' => $grouped
            ]);
        }
        
        if ($action === 'get_student_votes') {
            $student_id = $_GET['student_id'] ?? 0;
            
            if (!$student_id) {
                sendError('Student ID is required');
            }
            
            // Handle both student_id (database ID) and student_number
            $query = "SELECT v.vote_id, v.position, v.voted_at, c.first_name, c.last_name, c.photo
                     FROM votes v
                     JOIN candidates c ON v.candidate_id = c.candidate_id
                     JOIN students s ON v.student_id = s.student_id
                     WHERE (s.student_id = :student_id OR s.student_number = :student_id)
                     ORDER BY v.voted_at DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':student_id' => $student_id]);
            $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse([
                'success' => true,
                'votes' => $votes
            ]);
        }
        
        sendError('Invalid action');
    }
    
    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            sendError('Invalid JSON input');
        }
        
        $action = $input['action'] ?? '';
        
        if ($action === 'add_candidate') {
            $data = $input['data'] ?? [];
            
            // Validate required fields
            $required = ['first_name', 'last_name', 'position'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    sendError("Missing required field: $field");
                }
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
                sendResponse([
                    'success' => true,
                    'message' => 'Candidate added successfully',
                    'candidate_id' => $conn->lastInsertId()
                ]);
            } else {
                sendError('Failed to add candidate');
            }
        }
        
        if ($action === 'delete_candidate') {
            $candidate_id = $input['candidate_id'] ?? 0;
            
            if (!$candidate_id) {
                sendError('Invalid candidate ID');
            }
            
            $query = "DELETE FROM candidates WHERE candidate_id = :candidate_id";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([':candidate_id' => $candidate_id]);
            
            if ($result) {
                sendResponse([
                    'success' => true,
                    'message' => 'Candidate deleted successfully'
                ]);
            } else {
                sendError('Failed to delete candidate');
            }
        }
        
        if ($action === 'submit_vote') {
            $student_id = $input['student_id'] ?? 0;
            $candidate_id = $input['candidate_id'] ?? 0;
            $position = $input['position'] ?? '';
            
            if (!$student_id || !$candidate_id || !$position) {
                sendError('Missing required fields: student_id, candidate_id, position');
            }
            
            // Check if student exists (handle both student_id and student_number)
            $query = "SELECT student_id FROM students WHERE (student_id = :student_id OR student_number = :student_id) AND status = 'active'";
            $stmt = $conn->prepare($query);
            $stmt->execute([':student_id' => $student_id]);
            $student = $stmt->fetch();
            
            if (!$student) {
                sendError('Student not found or inactive');
            }
            
            // Get the actual student_id from database
            $actual_student_id = $student['student_id'];
            
            // Check if student already voted for this position
            $query = "SELECT vote_id FROM votes WHERE student_id = :student_id AND position = :position";
            $stmt = $conn->prepare($query);
            $stmt->execute([':student_id' => $actual_student_id, ':position' => $position]);
            
            if ($stmt->fetch()) {
                sendError('You have already voted for this position');
            }
            
            // Check if candidate exists
            $query = "SELECT candidate_id, first_name, last_name FROM candidates WHERE candidate_id = :candidate_id AND status = 'active'";
            $stmt = $conn->prepare($query);
            $stmt->execute([':candidate_id' => $candidate_id]);
            $candidate = $stmt->fetch();
            
            if (!$candidate) {
                sendError('Candidate not found or inactive');
            }
            
            // Insert vote
            $query = "INSERT INTO votes (student_id, candidate_id, position, voted_at, ip_address, user_agent) 
                     VALUES (:student_id, :candidate_id, :position, NOW(), :ip_address, :user_agent)";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                ':student_id' => $actual_student_id,
                ':candidate_id' => $candidate_id,
                ':position' => $position,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            if ($result) {
                sendResponse([
                    'success' => true,
                    'message' => 'Vote submitted successfully',
                    'candidate_name' => $candidate['first_name'] . ' ' . $candidate['last_name']
                ]);
            } else {
                sendError('Failed to submit vote');
            }
        }
        
        if ($action === 'get_student_votes') {
            $student_id = $input['student_id'] ?? 0;
            
            if (!$student_id) {
                sendError('Student ID is required');
            }
            
            // Handle both student_id (database ID) and student_number
            $query = "SELECT v.vote_id, v.position, v.voted_at, c.first_name, c.last_name, c.photo, c.candidate_id
                     FROM votes v
                     JOIN candidates c ON v.candidate_id = c.candidate_id
                     JOIN students s ON v.student_id = s.student_id
                     WHERE (s.student_id = :student_id OR s.student_number = :student_id)
                     ORDER BY v.voted_at DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':student_id' => $student_id]);
            $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse([
                'success' => true,
                'votes' => $votes
            ]);
        }
        
        sendError('Invalid action');
    }
    
    sendError('Invalid request method');
    
} catch (PDOException $e) {
    sendError('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage());
}
?>
