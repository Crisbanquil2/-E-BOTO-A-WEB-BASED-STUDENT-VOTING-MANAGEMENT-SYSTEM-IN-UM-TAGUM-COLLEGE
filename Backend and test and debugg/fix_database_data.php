<?php
/**
 * Fix Database Data - Add test candidates and students
 */

require_once 'config/database.php';

echo "<h1>Database Data Fix</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<div class='section info'>";
    echo "<h2>🔧 Fixing Database Data</h2>";
    echo "<p>This will add test candidates and students to fix your voting system.</p>";
    echo "</div>";
    
    // Check current data
    echo "<div class='section'>";
    echo "<h2>Current Database Status</h2>";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates");
    $stmt->execute();
    $candidateCount = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $studentCount = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes");
    $stmt->execute();
    $voteCount = $stmt->fetch()['count'];
    
    echo "<p><strong>Candidates:</strong> $candidateCount</p>";
    echo "<p><strong>Students:</strong> $studentCount</p>";
    echo "<p><strong>Votes:</strong> $voteCount</p>";
    echo "</div>";
    
    // Add test candidates if none exist
    if ($candidateCount == 0) {
        echo "<div class='section'>";
        echo "<h2>Adding Test Candidates</h2>";
        
        $candidates = [
            ['John', 'Doe', 'President', 'Male', 'Computer Science', '3rd Year', 'Experienced leader with vision for the future', ''],
            ['Jane', 'Smith', 'President', 'Female', 'Business Administration', '4th Year', 'Passionate about student welfare', ''],
            ['Mike', 'Johnson', 'Vice President', 'Male', 'Engineering', '3rd Year', 'Strong advocate for student rights', ''],
            ['Sarah', 'Wilson', 'Vice President', 'Female', 'Education', '2nd Year', 'Committed to academic excellence', ''],
            ['David', 'Brown', 'Secretary', 'Male', 'Psychology', '3rd Year', 'Organized and detail-oriented', ''],
            ['Lisa', 'Davis', 'Secretary', 'Female', 'Communication', '4th Year', 'Excellent communication skills', ''],
            ['Tom', 'Miller', 'Treasurer', 'Male', 'Accounting', '3rd Year', 'Financial management expertise', ''],
            ['Amy', 'Garcia', 'Treasurer', 'Female', 'Finance', '2nd Year', 'Budget planning specialist', '']
        ];
        
        $stmt = $conn->prepare("INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description, photo, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
        
        $added = 0;
        foreach ($candidates as $candidate) {
            try {
                $stmt->execute($candidate);
                $added++;
            } catch (Exception $e) {
                echo "<p>Error adding candidate {$candidate[0]} {$candidate[1]}: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<div class='success'>";
        echo "<p>✅ Added $added test candidates</p>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='section'>";
        echo "<h2>Candidates Already Exist</h2>";
        echo "<p>✅ Found $candidateCount candidates in database</p>";
        echo "</div>";
    }
    
    // Add test students if none exist
    if ($studentCount == 0) {
        echo "<div class='section'>";
        echo "<h2>Adding Test Students</h2>";
        
        $students = [
            ['2024001', 'Alice', 'Johnson', 'alice.johnson@email.com', 'password123', 'active'],
            ['2024002', 'Bob', 'Smith', 'bob.smith@email.com', 'password123', 'active'],
            ['2024003', 'Carol', 'Davis', 'carol.davis@email.com', 'password123', 'active'],
            ['2024004', 'Daniel', 'Wilson', 'daniel.wilson@email.com', 'password123', 'active'],
            ['2024005', 'Emma', 'Brown', 'emma.brown@email.com', 'password123', 'active']
        ];
        
        $stmt = $conn->prepare("INSERT INTO students (student_number, first_name, last_name, email, password, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        $added = 0;
        foreach ($students as $student) {
            try {
                $stmt->execute($student);
                $added++;
            } catch (Exception $e) {
                echo "<p>Error adding student {$student[0]}: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<div class='success'>";
        echo "<p>✅ Added $added test students</p>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='section'>";
        echo "<h2>Students Already Exist</h2>";
        echo "<p>✅ Found $studentCount students in database</p>";
        echo "</div>";
    }
    
    // Show current data
    echo "<div class='section'>";
    echo "<h2>Updated Database Status</h2>";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates");
    $stmt->execute();
    $candidateCount = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $studentCount = $stmt->fetch()['count'];
    
    echo "<p><strong>Candidates:</strong> $candidateCount</p>";
    echo "<p><strong>Students:</strong> $studentCount</p>";
    echo "<p><strong>Votes:</strong> $voteCount</p>";
    echo "</div>";
    
    // Show sample candidates
    if ($candidateCount > 0) {
        echo "<div class='section'>";
        echo "<h2>Sample Candidates</h2>";
        
        $stmt = $conn->prepare("SELECT candidate_id, first_name, last_name, position FROM candidates LIMIT 5");
        $stmt->execute();
        $candidates = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Position</th></tr>";
        foreach ($candidates as $candidate) {
            echo "<tr>";
            echo "<td>" . $candidate['candidate_id'] . "</td>";
            echo "<td>" . $candidate['first_name'] . " " . $candidate['last_name'] . "</td>";
            echo "<td>" . $candidate['position'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    // Show sample students
    if ($studentCount > 0) {
        echo "<div class='section'>";
        echo "<h2>Sample Students</h2>";
        
        $stmt = $conn->prepare("SELECT student_id, student_number, first_name, last_name FROM students LIMIT 5");
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Number</th><th>Name</th></tr>";
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>" . $student['student_id'] . "</td>";
            echo "<td>" . $student['student_number'] . "</td>";
            echo "<td>" . $student['first_name'] . " " . $student['last_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    echo "<div class='section success'>";
    echo "<h2>✅ Database Fix Complete!</h2>";
    echo "<p>Your database now has test data. You can now:</p>";
    echo "<ol>";
    echo "<li><a href='Login/login.html'>Login</a> with student number: <strong>2024001</strong> and password: <strong>password123</strong></li>";
    echo "<li><a href='Voting/voting.html'>Vote</a> for candidates</li>";
    echo "<li><a href='simple_voting_status.html'>Check your voting status</a></li>";
    echo "<li><a href='test_api_direct.php'>Test the APIs</a> again</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ Database Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
    echo "</div>";
}
?>
