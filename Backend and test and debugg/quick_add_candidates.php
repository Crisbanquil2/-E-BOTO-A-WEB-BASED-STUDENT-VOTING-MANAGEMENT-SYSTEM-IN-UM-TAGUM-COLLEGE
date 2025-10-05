<?php
/**
 * Quick Add Candidates - Run this to add sample candidates
 */

echo "<h1>Adding Sample Candidates to Database</h1>";

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
        echo "<p style='color: green;'>✅ Created candidates table</p>";
    }
    
    // Clear existing candidates first
    $conn->exec("DELETE FROM candidates");
    echo "<p style='color: blue;'>🗑️ Cleared existing candidates</p>";
    
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
        ],
        [
            'first_name' => 'Lisa',
            'last_name' => 'Garcia',
            'position' => 'Auditor',
            'gender' => 'Female',
            'course' => 'Bachelor of Arts in Communication',
            'year_level' => '2nd Year',
            'description' => 'Transparency and accountability advocate'
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
        echo "<p style='color: green;'>✅ Added: {$candidate['first_name']} {$candidate['last_name']} - {$candidate['position']}</p>";
    }
    
    echo "<h2 style='color: green;'>🎉 Successfully added {$addedCount} candidates!</h2>";
    echo "<p><a href='Admin/admin candidate/admincandidate.html' style='background: #1976d2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Candidate Management</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
