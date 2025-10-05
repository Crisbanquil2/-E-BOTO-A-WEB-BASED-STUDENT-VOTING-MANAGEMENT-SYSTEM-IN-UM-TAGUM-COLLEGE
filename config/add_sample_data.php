<?php
/**
 * Add Sample Data Script for Student Voting Management System
 * This script adds sample students and candidates for testing
 */

// Database configuration
$host = 'localhost';
$dbname = 'voting_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Adding Sample Data...</h2>";
    
    // Add sample students
    $students = [
        ['2024-0001', 'John', 'Doe', 'john.doe@email.com', 'BSIT', '3rd Year', 'MALE'],
        ['2024-0002', 'Jane', 'Smith', 'jane.smith@email.com', 'BSCS', '2nd Year', 'FEMALE'],
        ['2024-0003', 'Mike', 'Johnson', 'mike.johnson@email.com', 'BSIT', '4th Year', 'MALE'],
        ['2024-0004', 'Sarah', 'Wilson', 'sarah.wilson@email.com', 'BSCS', '3rd Year', 'FEMALE'],
        ['2024-0005', 'David', 'Brown', 'david.brown@email.com', 'BSIS', '2nd Year', 'MALE'],
        ['2024-0006', 'Lisa', 'Davis', 'lisa.davis@email.com', 'BSIT', '4th Year', 'FEMALE'],
        ['2024-0007', 'Chris', 'Banquil', 'chris.banquil@email.com', 'BSIT', '3rd Year', 'MALE'],
        ['2024-0008', 'Maria', 'Garcia', 'maria.garcia@email.com', 'BSCS', '2nd Year', 'FEMALE'],
        ['2024-0009', 'James', 'Martinez', 'james.martinez@email.com', 'BSIS', '4th Year', 'MALE'],
        ['2024-0010', 'Anna', 'Rodriguez', 'anna.rodriguez@email.com', 'BSIT', '3rd Year', 'FEMALE']
    ];
    
    $studentStmt = $pdo->prepare("INSERT IGNORE INTO students (student_number, first_name, last_name, email, course, year_level, gender, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    
    $studentCount = 0;
    foreach ($students as $student) {
        try {
            $studentStmt->execute([$student[0], $student[1], $student[2], $student[3], $student[4], $student[5], $student[6], password_hash('password123', PASSWORD_DEFAULT)]);
            $studentCount++;
            echo "<p style='color: green;'>✓ Added student: {$student[1]} {$student[2]} ({$student[0]})</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Student {$student[1]} {$student[2]} already exists or error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p><strong>Added {$studentCount} students</strong></p>";
    
    // Add sample candidates
    $candidates = [
        ['John', 'Doe', 'President', 'MALE', 'BSIT', '3rd Year', 'Experienced leader with strong communication skills', 'active'],
        ['Jane', 'Smith', 'President', 'FEMALE', 'BSCS', '2nd Year', 'Innovative thinker and team player', 'active'],
        ['Mike', 'Johnson', 'Vice President', 'MALE', 'BSIT', '4th Year', 'Dedicated to student welfare and development', 'active'],
        ['Sarah', 'Wilson', 'Vice President', 'FEMALE', 'BSCS', '3rd Year', 'Passionate about student activities and events', 'active'],
        ['David', 'Brown', 'Secretary', 'MALE', 'BSIS', '2nd Year', 'Organized and detail-oriented', 'active'],
        ['Lisa', 'Davis', 'Treasurer', 'FEMALE', 'BSIT', '4th Year', 'Financial management expertise', 'active']
    ];
    
    $candidateStmt = $pdo->prepare("INSERT IGNORE INTO candidates (first_name, last_name, position, gender, course, year_level, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $candidateCount = 0;
    foreach ($candidates as $candidate) {
        try {
            $candidateStmt->execute($candidate);
            $candidateCount++;
            echo "<p style='color: green;'>✓ Added candidate: {$candidate[0]} {$candidate[1]} for {$candidate[2]}</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Candidate {$candidate[0]} {$candidate[1]} already exists or error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p><strong>Added {$candidateCount} candidates</strong></p>";
    
    // Add some sample votes
    $votes = [
        [1, 1, 'President'], // John Doe voted for John Doe (President)
        [2, 2, 'President'], // Jane Smith voted for Jane Smith (President)
        [3, 1, 'President'], // Mike Johnson voted for John Doe (President)
        [4, 2, 'President'], // Sarah Wilson voted for Jane Smith (President)
        [5, 1, 'President'], // David Brown voted for John Doe (President)
        [1, 3, 'Vice President'], // John Doe voted for Mike Johnson (Vice President)
        [2, 4, 'Vice President'], // Jane Smith voted for Sarah Wilson (Vice President)
        [3, 3, 'Vice President'], // Mike Johnson voted for Mike Johnson (Vice President)
        [4, 4, 'Vice President'], // Sarah Wilson voted for Sarah Wilson (Vice President)
        [5, 3, 'Vice President']  // David Brown voted for Mike Johnson (Vice President)
    ];
    
    $voteStmt = $pdo->prepare("INSERT IGNORE INTO votes (student_id, candidate_id, position, ip_address) VALUES (?, ?, ?, '127.0.0.1')");
    
    $voteCount = 0;
    foreach ($votes as $vote) {
        try {
            $voteStmt->execute($vote);
            $voteCount++;
            echo "<p style='color: green;'>✓ Added vote: Student {$vote[0]} voted for candidate {$vote[1]} ({$vote[2]})</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Vote already exists or error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p><strong>Added {$voteCount} votes</strong></p>";
    
    // Show final statistics
    echo "<h3>Final Statistics:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $result = $stmt->fetch();
    echo "<p>Total Students: {$result['count']}</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM candidates");
    $result = $stmt->fetch();
    echo "<p>Total Candidates: {$result['count']}</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM votes");
    $result = $stmt->fetch();
    echo "<p>Total Votes: {$result['count']}</p>";
    
    echo "<h3 style='color: green;'>Sample Data Added Successfully!</h3>";
    echo "<p>You can now test the voting system with real data.</p>";
    echo "<p><strong>Test Login:</strong></p>";
    echo "<ul>";
    echo "<li>Student ID: 2024-0001, Password: password123</li>";
    echo "<li>Student ID: 2024-0007, Password: password123 (Chris Banquil)</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error!</h3>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure the database is set up first by running create_database.php</p>";
}
?>