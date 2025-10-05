<?php
/**
 * Add Sample Candidates to Database
 */

// Set JSON header
header('Content-Type: application/json');

try {
    // Database connection
    $host = 'localhost';
    $db_name = 'voting_system';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if candidates table exists
    $query = "SHOW TABLES LIKE 'candidates'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        // Create candidates table
        $createTable = "
            CREATE TABLE candidates (
                candidate_id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                position VARCHAR(100) NOT NULL,
                gender VARCHAR(10) NOT NULL,
                course VARCHAR(100) NOT NULL,
                year_level VARCHAR(20) NOT NULL,
                description TEXT,
                photo LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $conn->exec($createTable);
    }
    
    // Check if candidates already exist
    $query = "SELECT COUNT(*) as count FROM candidates";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Candidates already exist in database',
            'count' => $result['count']
        ]);
        exit();
    }
    
    // Add sample candidates
    $sampleCandidates = [
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'position' => 'President',
            'gender' => 'Male',
            'course' => 'BSIT',
            'year_level' => '4th Year',
            'description' => 'Experienced leader with vision for student welfare'
        ],
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'position' => 'Vice President',
            'gender' => 'Female',
            'course' => 'BSCS',
            'year_level' => '3rd Year',
            'description' => 'Passionate about student activities and community service'
        ],
        [
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'position' => 'Secretary',
            'gender' => 'Male',
            'course' => 'BSIT',
            'year_level' => '2nd Year',
            'description' => 'Organized and detail-oriented'
        ],
        [
            'first_name' => 'Sarah',
            'last_name' => 'Wilson',
            'position' => 'Treasurer',
            'gender' => 'Female',
            'course' => 'BSCS',
            'year_level' => '3rd Year',
            'description' => 'Financial management expertise'
        ],
        [
            'first_name' => 'David',
            'last_name' => 'Brown',
            'position' => 'Mayor',
            'gender' => 'Male',
            'course' => 'BSIT',
            'year_level' => '4th Year',
            'description' => 'Community-focused leader'
        ]
    ];
    
    $insertQuery = "INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    
    $addedCount = 0;
    foreach ($sampleCandidates as $candidate) {
        $stmt->execute([
            $candidate['first_name'],
            $candidate['last_name'],
            $candidate['position'],
            $candidate['gender'],
            $candidate['course'],
            $candidate['year_level'],
            $candidate['description']
        ]);
        $addedCount++;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Sample candidates added successfully',
        'count' => $addedCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
