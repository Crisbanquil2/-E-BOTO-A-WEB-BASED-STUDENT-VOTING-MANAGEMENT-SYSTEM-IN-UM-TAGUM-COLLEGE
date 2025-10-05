<?php
/**
 * Student Login API for Student Voting Management System
 * Handles student authentication using database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'database.php';

class LoginAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Authenticate student login
     */
    public function authenticateStudent($studentId, $password) {
        try {
            // Find student by student_number
            $stmt = $this->conn->prepare("
                SELECT 
                    student_id,
                    student_number,
                    first_name,
                    last_name,
                    email,
                    course,
                    year_level,
                    gender,
                    password,
                    status
                FROM students 
                WHERE student_number = ? AND status = 'active'
            ");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => 'Invalid Student ID or Password'
                ];
            }

            // Verify password
            if (!password_verify($password, $student['password'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid Student ID or Password'
                ];
            }

            // Remove password from response for security
            unset($student['password']);

            return [
                'success' => true,
                'message' => 'Login successful',
                'student' => $student
            ];

        } catch (Exception $e) {
            error_log("Login authentication error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ];
        }
    }

    /**
     * Check if student exists (for validation)
     */
    public function checkStudentExists($studentId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT student_id FROM students 
                WHERE student_number = ? AND status = 'active'
            ");
            $stmt->execute([$studentId]);
            
            return $stmt->fetch() !== false;

        } catch (Exception $e) {
            error_log("Check student exists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get student session data
     */
    public function getStudentSession($studentId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    student_id,
                    student_number,
                    first_name,
                    last_name,
                    email,
                    course,
                    year_level,
                    gender,
                    status
                FROM students 
                WHERE student_number = ? AND status = 'active'
            ");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();

            if ($student) {
                return [
                    'success' => true,
                    'student' => $student
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Student not found or inactive'
                ];
            }

        } catch (Exception $e) {
            error_log("Get student session error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get student data'
            ];
        }
    }

    /**
     * Validate session
     */
    public function validateSession($studentId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT student_id, status FROM students 
                WHERE student_number = ?
            ");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => 'Student not found'
                ];
            }

            if ($student['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Account is inactive'
                ];
            }

            return [
                'success' => true,
                'message' => 'Session is valid'
            ];

        } catch (Exception $e) {
            error_log("Validate session error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Session validation failed'
            ];
        }
    }
}

// Handle API requests
try {
    $api = new LoginAPI();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'login':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || empty($input['studentId']) || empty($input['password'])) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID and password are required'
                    ];
                } else {
                    $result = $api->authenticateStudent($input['studentId'], $input['password']);
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Method not allowed'
                ];
            }
            break;

        case 'check_exists':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || empty($input['studentId'])) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID is required'
                    ];
                } else {
                    $exists = $api->checkStudentExists($input['studentId']);
                    $result = [
                        'success' => true,
                        'exists' => $exists
                    ];
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Method not allowed'
                ];
            }
            break;

        case 'get_session':
            if ($method === 'GET') {
                $studentId = $_GET['studentId'] ?? null;
                
                if (!$studentId) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID is required'
                    ];
                } else {
                    $result = $api->getStudentSession($studentId);
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Method not allowed'
                ];
            }
            break;

        case 'validate_session':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || empty($input['studentId'])) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID is required'
                    ];
                } else {
                    $result = $api->validateSession($input['studentId']);
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Method not allowed'
                ];
            }
            break;

        default:
            $result = [
                'success' => false,
                'message' => 'Invalid action'
            ];
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log("Login API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
