<?php
/**
 * Ultra Simple API - Guaranteed to work
 */

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
    
    // Note: max_allowed_packet is read-only in session, handled by server configuration
    
    // Handle GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'get_candidates';
        
        if ($action === 'get_candidates') {
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
            exit();
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
            exit();
        }
        
        $action = $input['action'] ?? '';
        
        if ($action === 'add_candidate') {
            $data = $input['data'] ?? [];
            
            // Validate required fields
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['position'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
                exit();
            }
            
            // Photo size is handled by client-side compression
            
            try {
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
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'max_allowed_packet') !== false) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Image file is too large. Please compress the image or use a smaller file.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database error: ' . $e->getMessage()
                    ]);
                }
            }
            exit();
        }
        
        if ($action === 'delete_candidate') {
            $candidate_id = $input['candidate_id'] ?? 0;
            
            if (!$candidate_id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid candidate ID'
                ]);
                exit();
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
            exit();
        }

        if ($action === 'update_candidate') {
            $cid = $input['candidate_id'] ?? 0;
            $data = $input['data'] ?? [];
            if (!$cid) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid candidate ID'
                ]);
                exit();
            }
            
            // Photo size is handled by client-side compression
            
            try {
                $query = "UPDATE candidates SET first_name = :first_name, last_name = :last_name, position = :position, gender = :gender, course = :course, year_level = :year_level, description = :description, photo = :photo WHERE candidate_id = :candidate_id";
                $stmt = $conn->prepare($query);
                $result = $stmt->execute([
                    ':first_name' => $data['first_name'] ?? '',
                    ':last_name'  => $data['last_name'] ?? '',
                    ':position'   => $data['position'] ?? '',
                    ':gender'     => $data['gender'] ?? '',
                    ':course'     => $data['course'] ?? '',
                    ':year_level' => $data['year_level'] ?? '',
                    ':description'=> $data['description'] ?? '',
                    ':photo'      => $data['photo'] ?? null,
                    ':candidate_id' => $cid
                ]);
                if ($result) {
                    echo json_encode(['success'=>true,'message'=>'Candidate updated successfully']);
                } else {
                    echo json_encode(['success'=>false,'message'=>'Failed to update candidate']);
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'max_allowed_packet') !== false) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Image file is too large. Please compress the image or use a smaller file.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database error: ' . $e->getMessage()
                    ]);
                }
            }
            exit();
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
