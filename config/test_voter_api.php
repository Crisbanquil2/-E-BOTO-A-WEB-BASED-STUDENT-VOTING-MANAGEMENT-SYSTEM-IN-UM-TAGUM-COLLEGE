<?php
/**
 * Test file for Voter List API
 */

require_once 'database.php';

echo "<h2>Testing Voter List API</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if students table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'students'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Students table exists</p>";
        
        // Check if there are any students
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
        $stmt->execute();
        $result = $stmt->fetch();
        $studentCount = $result['count'];
        
        echo "<p>Students in database: <strong>$studentCount</strong></p>";
        
        if ($studentCount == 0) {
            echo "<p style='color: orange;'>⚠ No students found in database. Adding sample data...</p>";
            
            // Add sample students
            $sampleStudents = [
                ['2024-0001', 'John', 'Doe', 'john.doe@email.com', 'BSIT', '3rd Year', 'Male'],
                ['2024-0002', 'Jane', 'Smith', 'jane.smith@email.com', 'BSCS', '2nd Year', 'Female'],
                ['2024-0003', 'Mike', 'Johnson', 'mike.johnson@email.com', 'BSIS', '4th Year', 'Male'],
                ['2024-0004', 'Sarah', 'Wilson', 'sarah.wilson@email.com', 'BSCE', '1st Year', 'Female'],
                ['2024-0005', 'David', 'Brown', 'david.brown@email.com', 'BSIT', '3rd Year', 'Male']
            ];
            
            $stmt = $conn->prepare("
                INSERT INTO students (student_number, first_name, last_name, email, course, year_level, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            
            foreach ($sampleStudents as $student) {
                $stmt->execute($student);
            }
            
            echo "<p style='color: green;'>✓ Sample students added successfully</p>";
        }
        
        // Show all students
        $stmt = $conn->prepare("SELECT * FROM students ORDER BY created_at DESC");
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        echo "<h3>Students in Database:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Student Number</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Status</th></tr>";
        
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>" . $student['student_id'] . "</td>";
            echo "<td>" . $student['student_number'] . "</td>";
            echo "<td>" . $student['first_name'] . " " . $student['last_name'] . "</td>";
            echo "<td>" . $student['email'] . "</td>";
            echo "<td>" . $student['course'] . "</td>";
            echo "<td>" . $student['year_level'] . "</td>";
            echo "<td>" . $student['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>✗ Students table does not exist</p>";
        echo "<p>Creating students table...</p>";
        
        $createTable = "
        CREATE TABLE IF NOT EXISTS `students` (
            `student_id` int(11) NOT NULL AUTO_INCREMENT,
            `student_number` varchar(20) NOT NULL,
            `first_name` varchar(50) NOT NULL,
            `last_name` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `course` varchar(100) NOT NULL,
            `year_level` varchar(20) NOT NULL,
            `status` enum('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`student_id`),
            UNIQUE KEY `student_number` (`student_number`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $conn->exec($createTable);
        echo "<p style='color: green;'>✓ Students table created successfully</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Testing API directly:</h3>";
echo "<p><a href='voter_list_api.php?action=get_voters' target='_blank'>Test API: Get Voters</a></p>";

// Handle POST request for adding sample data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_sample_data') {
    try {
        $sampleStudents = [
            ['2024-0001', 'John', 'Doe', 'john.doe@email.com', 'BSIT', '3rd Year', 'Male'],
            ['2024-0002', 'Jane', 'Smith', 'jane.smith@email.com', 'BSCS', '2nd Year', 'Female'],
            ['2024-0003', 'Mike', 'Johnson', 'mike.johnson@email.com', 'BSIS', '4th Year', 'Male'],
            ['2024-0004', 'Sarah', 'Wilson', 'sarah.wilson@email.com', 'BSCE', '1st Year', 'Female'],
            ['2024-0005', 'David', 'Brown', 'david.brown@email.com', 'BSIT', '3rd Year', 'Male']
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO students (student_number, first_name, last_name, email, course, year_level, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $added = 0;
        foreach ($sampleStudents as $student) {
            try {
                $stmt->execute($student);
                $added++;
            } catch (Exception $e) {
                // Skip if student already exists
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    throw $e;
                }
            }
        }
        
        echo "<p style='color: green;'>✓ Added $added sample students successfully</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error adding sample data: " . $e->getMessage() . "</p>";
    }
    exit;
}
?>
