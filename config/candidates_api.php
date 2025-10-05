<?php
/**
 * Candidates API for Student Voting Management System
 * Handles CRUD operations for candidates and voting
 */

require_once 'database.php';

class CandidatesAPI {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Get all active candidates
     */
    public function getCandidates() {
        try {
            $query = "SELECT * FROM candidates ORDER BY position, last_name, first_name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group candidates by position
            $groupedCandidates = [];
            foreach ($candidates as $candidate) {
                $position = $candidate['position'];
                if (!isset($groupedCandidates[$position])) {
                    $groupedCandidates[$position] = [];
                }
                $groupedCandidates[$position][] = $candidate;
            }
            
            return [
                'success' => true,
                'candidates' => $candidates,
                'data' => $groupedCandidates,
                'total' => count($candidates)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching candidates: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get candidates by position
     */
    public function getCandidatesByPosition($position) {
        try {
            $query = "SELECT * FROM candidates WHERE position = :position ORDER BY last_name, first_name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':position', $position);
            $stmt->execute();
            
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $candidates
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching candidates: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Add a new candidate
     */
    public function addCandidate($data) {
        try {
            $query = "INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description, photo) 
                     VALUES (:first_name, :last_name, :position, :gender, :course, :year_level, :description, :photo)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':position', $data['position']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':course', $data['course']);
            $stmt->bindParam(':year_level', $data['year_level']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':photo', $data['photo']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Candidate added successfully',
                    'candidate_id' => $this->db->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to add candidate'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error adding candidate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update candidate
     */
    public function updateCandidate($candidate_id, $data) {
        try {
            $query = "UPDATE candidates SET 
                     first_name = :first_name, 
                     last_name = :last_name, 
                     position = :position, 
                     gender = :gender, 
                     course = :course, 
                     year_level = :year_level, 
                     description = :description, 
                     photo = :photo,
                     updated_at = CURRENT_TIMESTAMP
                     WHERE candidate_id = :candidate_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':candidate_id', $candidate_id);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':position', $data['position']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':course', $data['course']);
            $stmt->bindParam(':year_level', $data['year_level']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':photo', $data['photo']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Candidate updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update candidate'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating candidate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete candidate
     */
    public function deleteCandidate($candidate_id) {
        try {
            $query = "DELETE FROM candidates WHERE candidate_id = :candidate_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':candidate_id', $candidate_id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Candidate deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete candidate'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting candidate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Submit vote
     */
    public function submitVote($student_id, $candidate_id, $position) {
        try {
            // Check if student already voted for this position
            $checkQuery = "SELECT vote_id FROM votes WHERE student_id = :student_id AND position = :position";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':student_id', $student_id);
            $checkStmt->bindParam(':position', $position);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'You have already voted for this position'
                ];
            }
            
            // Insert vote
            $query = "INSERT INTO votes (student_id, candidate_id, position, ip_address, user_agent) 
                     VALUES (:student_id, :candidate_id, :position, :ip_address, :user_agent)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':candidate_id', $candidate_id);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
            $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Vote submitted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to submit vote'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error submitting vote: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get voting results
     */
    public function getVotingResults() {
        try {
            $query = "SELECT c.candidate_id, c.first_name, c.last_name, c.position, COUNT(v.vote_id) as vote_count
                     FROM candidates c
                     LEFT JOIN votes v ON c.candidate_id = v.candidate_id
                     GROUP BY c.candidate_id, c.position
                     ORDER BY c.position, vote_count DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group results by position
            $groupedResults = [];
            foreach ($results as $result) {
                $position = $result['position'];
                if (!isset($groupedResults[$position])) {
                    $groupedResults[$position] = [];
                }
                $groupedResults[$position][] = $result;
            }
            
            return [
                'success' => true,
                'data' => $groupedResults
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching results: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get voting results grouped by voter year level per candidate
     */
    public function getVotingResultsByYearLevel($position = null) {
        try {
            // Define standard year level order/labels
            $yearLabels = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];

            // Helper: normalize different year formats to one of the labels above
            $normalizeYear = function($value) use ($yearLabels) {
                if ($value === null) return null;
                $v = strtolower(trim((string)$value));
                // Remove punctuation and extra spaces
                $v = preg_replace('/[^a-z0-9 ]/i', ' ', $v);
                $v = preg_replace('/\s+/', ' ', $v);
                // Direct numeric
                if (in_array($v, ['1','1st','first','year 1','1 year'])) return $yearLabels[0];
                if (in_array($v, ['2','2nd','second','year 2','2 year'])) return $yearLabels[1];
                if (in_array($v, ['3','3rd','third','year 3','3 year'])) return $yearLabels[2];
                if (in_array($v, ['4','4th','fourth','year 4','4 year'])) return $yearLabels[3];
                if (in_array($v, ['5','5th','fifth','year 5','5 year'])) return $yearLabels[4];
                // Common variants
                if ($v === '1st year' || $v === 'first year') return $yearLabels[0];
                if ($v === '2nd year' || $v === 'second year') return $yearLabels[1];
                if ($v === '3rd year' || $v === 'third year') return $yearLabels[2];
                if ($v === '4th year' || $v === 'fourth year') return $yearLabels[3];
                if ($v === '5th year' || $v === 'fifth year') return $yearLabels[4];
                return null;
            };

            // Build base query to aggregate votes by candidate and student year level
            $query = "SELECT 
                        c.candidate_id,
                        c.first_name,
                        c.last_name,
                        c.position,
                        c.photo,
                        COALESCE(s.year_level, 'Unknown') AS year_level,
                        COUNT(v.vote_id) AS vote_count
                      FROM candidates c
                      LEFT JOIN votes v ON c.candidate_id = v.candidate_id
                      LEFT JOIN students s ON v.student_id = s.student_id";

            $params = [];
            if (!empty($position)) {
                $query .= " WHERE c.position = :position";
                $params[':position'] = $position;
            }

            $query .= " GROUP BY c.candidate_id, year_level
                        ORDER BY c.position, c.last_name, c.first_name";

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Organize results: positions -> candidates -> counts per year label
            $positions = [];
            $candidatesIndex = [];
            foreach ($rows as $row) {
                $pos = $row['position'];
                if (!isset($positions[$pos])) {
                    $positions[$pos] = [
                        'position' => $pos,
                        'years' => $yearLabels,
                        'candidates' => []
                    ];
                    $candidatesIndex[$pos] = [];
                }

                $candidateId = (int)$row['candidate_id'];
                if (!isset($candidatesIndex[$pos][$candidateId])) {
                    $positions[$pos]['candidates'][] = [
                        'candidate_id' => $candidateId,
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'photo' => $row['photo'],
                        'counts' => array_fill(0, count($yearLabels), 0)
                    ];
                    $candidatesIndex[$pos][$candidateId] = count($positions[$pos]['candidates']) - 1;
                }

                // Map year to index; ignore unknown years not in labels
                $year = $normalizeYear($row['year_level']);
                $idx = array_search($year, $yearLabels, true);
                if ($idx !== false) {
                    $positions[$pos]['candidates'][$candidatesIndex[$pos][$candidateId]]['counts'][$idx] = (int)$row['vote_count'];
                }
            }

            // Ensure candidates with zero votes still appear (when filtering by position)
            if (!empty($position) && !isset($positions[$position])) {
                // Load candidates for this position to produce zero-filled entries
                $cStmt = $this->db->prepare("SELECT candidate_id, first_name, last_name, position, photo FROM candidates WHERE position = :position ORDER BY last_name, first_name");
                $cStmt->bindParam(':position', $position);
                $cStmt->execute();
                $candRows = $cStmt->fetchAll(PDO::FETCH_ASSOC);
                $positions[$position] = [
                    'position' => $position,
                    'years' => $yearLabels,
                    'candidates' => []
                ];
                foreach ($candRows as $c) {
                    $positions[$position]['candidates'][] = [
                        'candidate_id' => (int)$c['candidate_id'],
                        'first_name' => $c['first_name'],
                        'last_name' => $c['last_name'],
                        'photo' => $c['photo'],
                        'counts' => array_fill(0, count($yearLabels), 0)
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $positions
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching results by year: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if student has voted for a position
     */
    public function hasVotedForPosition($student_id, $position) {
        try {
            $query = "SELECT vote_id FROM votes WHERE student_id = :student_id AND position = :position";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':position', $position);
            $stmt->execute();
            
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $api = new CandidatesAPI();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get_candidates':
                $response = $api->getCandidates();
                break;
            case 'get_candidates_by_position':
                $position = $_GET['position'] ?? '';
                $response = $api->getCandidatesByPosition($position);
                break;
            case 'get_results':
                $response = $api->getVotingResults();
                break;
            case 'get_results_by_year':
                $position = $_GET['position'] ?? null;
                $response = $api->getVotingResultsByYearLevel($position);
                break;
            default:
                $response = ['success' => false, 'message' => 'Invalid action'];
        }
    } else {
        $response = $api->getCandidates();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api = new CandidatesAPI();
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'add_candidate':
                $response = $api->addCandidate($input['data']);
                break;
            case 'update_candidate':
                $response = $api->updateCandidate($input['candidate_id'], $input['data']);
                break;
            case 'delete_candidate':
                $response = $api->deleteCandidate($input['candidate_id']);
                break;
            case 'submit_vote':
                $response = $api->submitVote($input['student_id'], $input['candidate_id'], $input['position']);
                break;
            default:
                $response = ['success' => false, 'message' => 'Invalid action'];
        }
    } else {
        $response = ['success' => false, 'message' => 'No action specified'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
