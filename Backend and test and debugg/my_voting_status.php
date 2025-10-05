<?php
/**
 * Direct Voting Status - Shows voting history without JavaScript
 */

require_once 'config/database.php';

// Start session to get student data
session_start();

// Get student from session or redirect to login
$student = null;
if (isset($_SESSION['student'])) {
    $student = $_SESSION['student'];
} else {
    // Try to get from JavaScript session storage via URL parameter
    $studentId = $_GET['studentId'] ?? null;
    if ($studentId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ? OR student_number = ?");
            $stmt->execute([$studentId, $studentId]);
            $student = $stmt->fetch();
        } catch (Exception $e) {
            // Handle error
        }
    }
}

if (!$student) {
    header('Location: Login/login.html');
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get student's votes with candidate information
    $stmt = $conn->prepare("
        SELECT 
            v.vote_id,
            v.position,
            v.voted_at,
            c.first_name,
            c.last_name,
            c.photo,
            c.course,
            c.year_level
        FROM votes v
        JOIN candidates c ON v.candidate_id = c.candidate_id
        WHERE v.student_id = ?
        ORDER BY v.voted_at DESC
    ");
    $stmt->execute([$student['student_id']]);
    $votes = $stmt->fetchAll();
    
    // Get all positions and check which ones the student has voted for
    $stmt = $conn->prepare("SELECT DISTINCT position FROM candidates WHERE status = 'active'");
    $stmt->execute();
    $allPositions = $stmt->fetchAll();
    
    $votedPositions = array_column($votes, 'position');
    
} catch (Exception $e) {
    $votes = [];
    $allPositions = [];
    $votedPositions = [];
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Voting Status - Direct View</title>
    <link rel="icon" href="pictures/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .student-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #d32f2f;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .status-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .status-card.voted {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .status-card.not-voted {
            border-color: #ffc107;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        }

        .status-card i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .status-card.voted i {
            color: #28a745;
        }

        .status-card.not-voted i {
            color: #ffc107;
        }

        .status-card h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #333;
        }

        .status-card p {
            color: #666;
            font-size: 1rem;
        }

        .voting-history {
            background: white;
            border-radius: 10px;
            padding: 25px;
            border: 2px solid #e9ecef;
        }

        .history-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #d32f2f;
        }

        .vote-item {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            border: 1px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .vote-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .vote-item:last-child {
            margin-bottom: 0;
        }

        .candidate-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #28a745;
        }

        .vote-details {
            flex: 1;
        }

        .candidate-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .position {
            color: #666;
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .vote-date {
            color: #888;
            font-size: 0.9rem;
        }

        .vote-status {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .no-votes {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .no-votes i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ccc;
        }

        .no-votes h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .no-votes p {
            font-size: 1.1rem;
            margin-bottom: 25px;
        }

        .btn {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }

        .actions .btn {
            margin: 0 10px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-vote-yea"></i> My Voting Status</h1>
            <p>Direct view of your voting history and status</p>
        </div>

        <div class="content">
            <div class="student-info">
                <i class="fas fa-user"></i> 
                <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                Student ID: <?php echo htmlspecialchars($student['student_id']); ?> | 
                Student Number: <?php echo htmlspecialchars($student['student_number']); ?>
            </div>

            <?php if (isset($error)): ?>
                <div class="error">
                    <h3><i class="fas fa-exclamation-triangle"></i> Database Error</h3>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <h2><i class="fas fa-chart-bar"></i> Voting Summary</h2>
            <div class="status-grid">
                <?php if (empty($allPositions)): ?>
                    <div class="error">
                        <h3><i class="fas fa-exclamation-triangle"></i> No Positions Available</h3>
                        <p>No candidate positions found in the system.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($allPositions as $positionRow): ?>
                        <?php $position = $positionRow['position']; ?>
                        <?php $hasVoted = in_array($position, $votedPositions); ?>
                        <?php $vote = $hasVoted ? array_values(array_filter($votes, function($v) use ($position) { return $v['position'] === $position; }))[0] : null; ?>
                        
                        <div class="status-card <?php echo $hasVoted ? 'voted' : 'not-voted'; ?>">
                            <i class="fas fa-<?php echo $hasVoted ? 'check-circle' : 'clock'; ?>"></i>
                            <h3><?php echo htmlspecialchars($position); ?></h3>
                            <p>
                                <?php if ($hasVoted && $vote): ?>
                                    ✅ Voted for <?php echo htmlspecialchars($vote['first_name'] . ' ' . $vote['last_name']); ?>
                                <?php else: ?>
                                    ⏳ Not voted yet
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="voting-history">
                <div class="history-title">
                    <i class="fas fa-history"></i> Your Voting History
                </div>
                
                <?php if (empty($votes)): ?>
                    <div class="no-votes">
                        <i class="fas fa-vote-yea"></i>
                        <h3>No Votes Yet</h3>
                        <p>You haven't voted for any candidates yet.</p>
                        <a href="Voting/voting.html" class="btn">
                            <i class="fas fa-vote-yea"></i> Go Vote Now
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($votes as $vote): ?>
                        <div class="vote-item">
                            <img src="<?php echo htmlspecialchars($vote['photo'] ?: 'pictures/logo.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($vote['first_name']); ?>" 
                                 class="candidate-photo"
                                 onerror="this.src='pictures/logo.png'">
                            <div class="vote-details">
                                <div class="candidate-name">
                                    <?php echo htmlspecialchars($vote['first_name'] . ' ' . $vote['last_name']); ?>
                                </div>
                                <div class="position"><?php echo htmlspecialchars($vote['position']); ?></div>
                                <div class="vote-date">
                                    Voted on <?php echo date('F j, Y \a\t g:i A', strtotime($vote['voted_at'])); ?>
                                </div>
                            </div>
                            <div class="vote-status">
                                <i class="fas fa-check"></i> Voted
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="actions">
                <a href="Voting/voting.html" class="btn">
                    <i class="fas fa-vote-yea"></i> Go Vote
                </a>
                <a href="Dasboard for Student or user/index.html" class="btn">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="my_voting_status.php" class="btn">
                    <i class="fas fa-sync"></i> Refresh
                </a>
            </div>
        </div>
    </div>
</body>
</html>
