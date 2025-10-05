<?php
/**
 * Voting API for Student Voting Management System
 * Handles student voting for candidates
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

class VotingAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Submit a vote for a candidate
     */
    public function submitVote($studentId, $candidateId, $position) {
        try {
            // Check if student exists and is active - try both student_id and student_number
            $stmt = $this->conn->prepare("
                SELECT student_id, student_number, first_name, last_name 
                FROM students 
                WHERE (student_id = ? OR student_number = ?) AND status = 'active'
            ");
            $stmt->execute([$studentId, $studentId]);
            $student = $stmt->fetch();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => 'Student not found or inactive'
                ];
            }

            // Check if student has already voted for this position
            $stmt = $this->conn->prepare("
                SELECT vote_id FROM votes 
                WHERE student_id = ? AND position = ?
            ");
            $stmt->execute([$student['student_id'], $position]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'You have already voted for this position'
                ];
            }

            // Check if candidate exists and is active
            $stmt = $this->conn->prepare("
                SELECT candidate_id, first_name, last_name, position 
                FROM candidates 
                WHERE candidate_id = ? AND status = 'active'
            ");
            $stmt->execute([$candidateId]);
            $candidate = $stmt->fetch();

            if (!$candidate) {
                return [
                    'success' => false,
                    'message' => 'Candidate not found or inactive'
                ];
            }

            // Insert vote with IP address and user agent for security
            $stmt = $this->conn->prepare("
                INSERT INTO votes (student_id, candidate_id, position, voted_at, ip_address, user_agent) 
                VALUES (?, ?, ?, NOW(), ?, ?)
            ");
            $result = $stmt->execute([
                $student['student_id'], 
                $candidateId, 
                $position,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Vote submitted successfully',
                    'vote_id' => $this->conn->lastInsertId(),
                    'candidate_name' => $candidate['first_name'] . ' ' . $candidate['last_name'],
                    'position' => $position
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to submit vote'
                ];
            }

        } catch (Exception $e) {
            error_log("Voting error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Voting failed. Please try again.'
            ];
        }
    }

    /**
     * Get student's voting history
     */
    public function getStudentVotes($studentId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    v.vote_id,
                    v.position,
                    v.voted_at,
                    c.first_name,
                    c.last_name,
                    c.photo
                FROM votes v
                JOIN candidates c ON v.candidate_id = c.candidate_id
                JOIN students s ON v.student_id = s.student_id
                WHERE (s.student_id = ? OR s.student_number = ?)
                ORDER BY v.voted_at DESC
            ");
            $stmt->execute([$studentId, $studentId]);
            $votes = $stmt->fetchAll();

            return [
                'success' => true,
                'votes' => $votes
            ];

        } catch (Exception $e) {
            error_log("Get student votes error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get voting history'
            ];
        }
    }

    /**
     * Get leading candidates with vote counts and percentages
     */
    public function getLeadingCandidates() {
        try {
            // Get total votes per position
            $stmt = $this->conn->prepare("
                SELECT 
                    position,
                    COUNT(*) as total_votes
                FROM votes 
                GROUP BY position
            ");
            $stmt->execute();
            $positionTotals = [];
            while ($row = $stmt->fetch()) {
                $positionTotals[$row['position']] = $row['total_votes'];
            }

            // Get candidate votes
            $stmt = $this->conn->prepare("
                SELECT 
                    c.candidate_id,
                    c.first_name,
                    c.last_name,
                    c.position,
                    c.photo,
                    COUNT(v.vote_id) as vote_count
                FROM candidates c
                LEFT JOIN votes v ON c.candidate_id = v.candidate_id
                WHERE c.status = 'active'
                GROUP BY c.candidate_id, c.first_name, c.last_name, c.position, c.photo
                ORDER BY c.position, vote_count DESC
            ");
            $stmt->execute();
            $candidates = $stmt->fetchAll();

            // Calculate percentages
            foreach ($candidates as &$candidate) {
                $totalVotes = $positionTotals[$candidate['position']] ?? 0;
                $candidate['percentage'] = $totalVotes > 0 ? round(($candidate['vote_count'] / $totalVotes) * 100, 1) : 0;
            }

            return [
                'success' => true,
                'candidates' => $candidates,
                'position_totals' => $positionTotals
            ];

        } catch (Exception $e) {
            error_log("Get leading candidates error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get leading candidates'
            ];
        }
    }

    /**
     * Get voting statistics
     */
    public function getVotingStats() {
        try {
            $stats = [];

            // Total votes
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total_votes FROM votes");
            $stmt->execute();
            $stats['total_votes'] = $stmt->fetch()['total_votes'];

            // Total voters
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total_voters FROM students WHERE status = 'active'");
            $stmt->execute();
            $stats['total_voters'] = $stmt->fetch()['total_voters'];

            // Voted count
            $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT student_id) as voted_count FROM votes");
            $stmt->execute();
            $stats['voted_count'] = $stmt->fetch()['voted_count'];

            // Voting percentage
            $stats['voting_percentage'] = $stats['total_voters'] > 0 ? 
                round(($stats['voted_count'] / $stats['total_voters']) * 100, 1) : 0;

            return [
                'success' => true,
                'stats' => $stats
            ];

        } catch (Exception $e) {
            error_log("Get voting stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get voting statistics'
            ];
        }
    }

    /**
     * Check if student has voted for a position
     */
    public function hasVotedForPosition($studentId, $position) {
        try {
            $stmt = $this->conn->prepare("
                SELECT v.vote_id, c.first_name, c.last_name
                FROM votes v
                JOIN candidates c ON v.candidate_id = c.candidate_id
                JOIN students s ON v.student_id = s.student_id
                WHERE (s.student_id = ? OR s.student_number = ?) AND v.position = ?
            ");
            $stmt->execute([$studentId, $studentId, $position]);
            $vote = $stmt->fetch();

            return [
                'success' => true,
                'has_voted' => $vote !== false,
                'candidate_name' => $vote ? $vote['first_name'] . ' ' . $vote['last_name'] : null
            ];

        } catch (Exception $e) {
            error_log("Check vote error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to check voting status'
            ];
        }
    }
}

// Handle API requests
try {
    $api = new VotingAPI();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    // Debug logging
    error_log("Voting API Debug - Method: " . $method . ", Action: " . $action);
    error_log("Voting API Debug - GET params: " . print_r($_GET, true));
    error_log("Voting API Debug - POST data: " . file_get_contents('php://input'));

    switch ($action) {
        case 'submit_vote':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || empty($input['studentId']) || empty($input['candidateId']) || empty($input['position'])) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID, candidate ID, and position are required'
                    ];
                } else {
                    $result = $api->submitVote($input['studentId'], $input['candidateId'], $input['position']);
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Method not allowed'
                ];
            }
            break;

        case 'get_student_votes':
            if ($method === 'GET') {
                $studentId = $_GET['studentId'] ?? null;
                
                if (!$studentId) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID is required'
                    ];
                } else {
                    $result = $api->getStudentVotes($studentId);
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Method not allowed'
                ];
            }
            break;

        case 'get_leading_candidates':
            $result = $api->getLeadingCandidates();
            break;

        case 'get_voting_stats':
            $result = $api->getVotingStats();
            break;

        case 'check_vote_status':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || empty($input['studentId']) || empty($input['position'])) {
                    $result = [
                        'success' => false,
                        'message' => 'Student ID and position are required'
                    ];
                } else {
                    $result = $api->hasVotedForPosition($input['studentId'], $input['position']);
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
    error_log("Voting API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
