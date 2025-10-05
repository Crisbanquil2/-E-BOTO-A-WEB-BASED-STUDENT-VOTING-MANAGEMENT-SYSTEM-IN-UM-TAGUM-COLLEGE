<?php
/**
 * Student Registration API for Student Voting Management System
 * Handles student registration and saves data to database
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

class RegistrationAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Register a new student
     */
    public function registerStudent($data) {
        try {
            // Validate required fields
            $requiredFields = ['studentId', 'firstName', 'lastName', 'email', 'course', 'yearLevel', 'gender', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ];
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format'
                ];
            }

            // Validate password length
            if (strlen($data['password']) < 6) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 6 characters'
                ];
            }

            // Check if student ID already exists
            $stmt = $this->conn->prepare("
                SELECT student_id FROM students 
                WHERE student_number = ? OR email = ?
            ");
            $stmt->execute([$data['studentId'], $data['email']]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Student ID or email already registered'
                ];
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert new student
            $stmt = $this->conn->prepare("
                INSERT INTO students (
                    student_number, 
                    first_name, 
                    last_name, 
                    email, 
                    course, 
                    year_level, 
                    gender, 
                    password, 
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");

            $result = $stmt->execute([
                $data['studentId'],
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['course'],
                $data['yearLevel'],
                $data['gender'],
                $hashedPassword
            ]);

            if ($result) {
                $studentId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Student registered successfully',
                    'student_id' => $studentId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to register student'
                ];
            }

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    /**
     * Check if student ID or email exists
     */
    public function checkStudentExists($studentId, $email) {
        try {
            $stmt = $this->conn->prepare("
                SELECT student_id FROM students 
                WHERE student_number = ? OR email = ?
            ");
            $stmt->execute([$studentId, $email]);
            
            return $stmt->fetch() !== false;

        } catch (Exception $e) {
            error_log("Check student exists error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get student by ID
     */
    public function getStudent($studentId) {
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
                    status,
                    created_at
                FROM students 
                WHERE student_id = ?
            ");
            $stmt->execute([$studentId]);
            
            return $stmt->fetch();

        } catch (Exception $e) {
            error_log("Get student error: " . $e->getMessage());
            return null;
        }
    }
}

// Handle API requests
try {
    $api = new RegistrationAPI();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'register':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    $result = [
                        'success' => false,
                        'message' => 'Invalid JSON input'
                    ];
                } else {
                    $result = $api->registerStudent($input);
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
                
                if (!$input || empty($input['studentId']) || empty($input['email'])) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID and email are required'
                    ];
                } else {
                    $exists = $api->checkStudentExists($input['studentId'], $input['email']);
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

        case 'get_student':
            if ($method === 'GET') {
                $studentId = $_GET['id'] ?? null;
                
                if (!$studentId) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID is required'
                    ];
                } else {
                    $student = $api->getStudent($studentId);
                    if ($student) {
                        $result = [
                            'success' => true,
                            'data' => $student
                        ];
                    } else {
                        $result = [
                            'success' => false,
                            'message' => 'Student not found'
                        ];
                    }
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
    error_log("Registration API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
