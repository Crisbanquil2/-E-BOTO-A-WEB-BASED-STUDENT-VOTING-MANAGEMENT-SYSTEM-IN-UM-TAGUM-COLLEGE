<?php
/**
 * Voter List API for Student Voting Management System
 * Handles fetching and managing voter data
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

class VoterListAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Get all voters from database and localStorage
     */
    public function getVoters() {
        try {
            $voters = [];

            // First, try to get voters from database
            $dbVoters = $this->getVotersFromDatabase();
            $voters = array_merge($voters, $dbVoters);

            // Also get voters from localStorage (if any)
            $localVoters = $this->getVotersFromLocalStorage();
            $voters = array_merge($voters, $localVoters);

            // Remove duplicates based on student_id or email
            $voters = $this->removeDuplicateVoters($voters);

            // Add voting status for each voter
            $voters = $this->addVotingStatus($voters);

            return [
                'success' => true,
                'data' => $voters,
                'total' => count($voters)
            ];

        } catch (Exception $e) {
            error_log("Error getting voters: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve voter list',
                'data' => []
            ];
        }
    }

    /**
     * Get voters from database
     */
    private function getVotersFromDatabase() {
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
                WHERE status = 'active'
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $voters = $stmt->fetchAll();

            // Add voting status for each voter
            foreach ($voters as &$voter) {
                $voter['has_voted'] = false;
            }

            return $voters;

        } catch (Exception $e) {
            error_log("Error getting voters from database: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get voters from localStorage (simulated)
     * In a real application, this would be stored in database
     */
    private function getVotersFromLocalStorage() {
        // This simulates getting data from localStorage
        // In a real application, you would store this in the database
        $localVoters = [];
        
        // Check if there's a way to get localStorage data
        // For now, we'll return empty array as the main data should be in database
        return $localVoters;
    }

    /**
     * Remove duplicate voters based on student_id or email
     */
    private function removeDuplicateVoters($voters) {
        $uniqueVoters = [];
        $seenIds = [];
        $seenEmails = [];

        foreach ($voters as $voter) {
            $studentId = $voter['student_id'] ?? $voter['studentId'] ?? null;
            $email = $voter['email'] ?? '';

            // Check if we've already seen this student ID or email
            if (($studentId && in_array($studentId, $seenIds)) || 
                ($email && in_array($email, $seenEmails))) {
                continue;
            }

            if ($studentId) $seenIds[] = $studentId;
            if ($email) $seenEmails[] = $email;

            $uniqueVoters[] = $voter;
        }

        return $uniqueVoters;
    }

    /**
     * Add voting status to voters
     */
    private function addVotingStatus($voters) {
        try {
            foreach ($voters as &$voter) {
                $studentId = $voter['student_id'] ?? $voter['studentId'] ?? null;
                
                if ($studentId) {
                    // Check if student has voted
                    $stmt = $this->conn->prepare("
                        SELECT COUNT(*) as vote_count 
                        FROM votes 
                        WHERE student_id = ?
                    ");
                    $stmt->execute([$studentId]);
                    $result = $stmt->fetch();
                    
                    $voter['has_voted'] = ($result['vote_count'] > 0);
                } else {
                    $voter['has_voted'] = false;
                }
            }

            return $voters;

        } catch (Exception $e) {
            error_log("Error adding voting status: " . $e->getMessage());
            return $voters;
        }
    }

    /**
     * Toggle voter status
     */
    public function toggleVoterStatus($voterId) {
        try {
            // Get current status
            $stmt = $this->conn->prepare("
                SELECT status FROM students WHERE student_id = ?
            ");
            $stmt->execute([$voterId]);
            $voter = $stmt->fetch();

            if (!$voter) {
                return [
                    'success' => false,
                    'message' => 'Voter not found'
                ];
            }

            $newStatus = $voter['status'] === 'active' ? 'inactive' : 'active';

            // Update status
            $stmt = $this->conn->prepare("
                UPDATE students 
                SET status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE student_id = ?
            ");
            $stmt->execute([$newStatus, $voterId]);

            return [
                'success' => true,
                'message' => 'Voter status updated successfully',
                'new_status' => $newStatus
            ];

        } catch (Exception $e) {
            error_log("Error toggling voter status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update voter status'
            ];
        }
    }

    /**
     * Get voter statistics
     */
    public function getVoterStatistics() {
        try {
            $stats = [];

            // Total voters
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM students");
            $stmt->execute();
            $stats['total'] = $stmt->fetch()['total'];

            // Active voters
            $stmt = $this->conn->prepare("SELECT COUNT(*) as active FROM students WHERE status = 'active'");
            $stmt->execute();
            $stats['active'] = $stmt->fetch()['active'];

            // Voted count
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT student_id) as voted 
                FROM votes
            ");
            $stmt->execute();
            $stats['voted'] = $stmt->fetch()['voted'];

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (Exception $e) {
            error_log("Error getting voter statistics: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get voter statistics'
            ];
        }
    }
}

// Handle API requests
try {
    $api = new VoterListAPI();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_voters':
            $result = $api->getVoters();
            break;

        case 'get_statistics':
            $result = $api->getVoterStatistics();
            break;

        case 'toggle_status':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $voterId = $input['voter_id'] ?? null;
                
                if (!$voterId) {
                    $result = [
                        'success' => false,
                        'message' => 'Voter ID is required'
                    ];
                } else {
                    $result = $api->toggleVoterStatus($voterId);
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
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
