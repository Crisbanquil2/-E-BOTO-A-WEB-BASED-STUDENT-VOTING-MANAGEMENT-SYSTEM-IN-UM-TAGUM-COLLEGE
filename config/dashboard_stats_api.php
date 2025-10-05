<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'voting_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get total voters (registered students)
    $voterQuery = "SELECT COUNT(*) as total_voters FROM students";
    $voterStmt = $pdo->prepare($voterQuery);
    $voterStmt->execute();
    $voterResult = $voterStmt->fetch(PDO::FETCH_ASSOC);
    $totalVoters = $voterResult['total_voters'] ?? 0;
    
    // Get active voters (students who can vote)
    $activeVoterQuery = "SELECT COUNT(*) as active_voters FROM students WHERE status = 'active'";
    $activeVoterStmt = $pdo->prepare($activeVoterQuery);
    $activeVoterStmt->execute();
    $activeVoterResult = $activeVoterStmt->fetch(PDO::FETCH_ASSOC);
    $activeVoters = $activeVoterResult['active_voters'] ?? 0;
    
    // Get total candidates
    $candidateQuery = "SELECT COUNT(*) as total_candidates FROM candidates";
    $candidateStmt = $pdo->prepare($candidateQuery);
    $candidateStmt->execute();
    $candidateResult = $candidateStmt->fetch(PDO::FETCH_ASSOC);
    $totalCandidates = $candidateResult['total_candidates'] ?? 0;
    
    // Get votes cast
    $votesQuery = "SELECT COUNT(*) as votes_cast FROM votes";
    $votesStmt = $pdo->prepare($votesQuery);
    $votesStmt->execute();
    $votesResult = $votesStmt->fetch(PDO::FETCH_ASSOC);
    $votesCast = $votesResult['votes_cast'] ?? 0;
    
    // Get active elections (using voting_sessions table instead)
    $electionsQuery = "SELECT COUNT(*) as active_elections FROM voting_sessions WHERE status = 'active'";
    $electionsStmt = $pdo->prepare($electionsQuery);
    $electionsStmt->execute();
    $electionsResult = $electionsStmt->fetch(PDO::FETCH_ASSOC);
    $activeElections = $electionsResult['active_elections'] ?? 0;
    
    // Get students who have voted
    $votedQuery = "SELECT COUNT(DISTINCT student_id) as voted FROM votes";
    $votedStmt = $pdo->prepare($votedQuery);
    $votedStmt->execute();
    $votedResult = $votedStmt->fetch(PDO::FETCH_ASSOC);
    $voted = $votedResult['voted'] ?? 0;
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'total_voters' => (int)$totalVoters,
            'active_voters' => (int)$activeVoters,
            'total_candidates' => (int)$totalCandidates,
            'votes_cast' => (int)$votesCast,
            'active_elections' => (int)$activeElections,
            'voted' => (int)$voted
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'data' => [
            'total_voters' => 0,
            'active_voters' => 0,
            'total_candidates' => 0,
            'votes_cast' => 0,
            'active_elections' => 0,
            'voted' => 0
        ]
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Error: ' . $e->getMessage(),
        'data' => [
            'total_voters' => 0,
            'active_voters' => 0,
            'total_candidates' => 0,
            'votes_cast' => 0,
            'active_elections' => 0,
            'voted' => 0
        ]
    ];
    
    echo json_encode($response);
}
?>
