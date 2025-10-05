<?php
/**
 * Simple Add Candidates Script
 */

echo "<h1>Adding Sample Candidates</h1>";

try {
    // Database connection
    $host = 'localhost';
    $db_name = 'voting_system';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create candidates table if it doesn't exist
    $createTable = "
        CREATE TABLE IF NOT EXISTS candidates (
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
    echo "<p style='color: green;'>✅ Table ready</p>";
    
    // Clear existing candidates
    $conn->exec("DELETE FROM candidates");
    echo "<p style='color: blue;'>🗑️ Cleared existing candidates</p>";
    
    // Add sample candidates
    $candidates = [
        ['John', 'Doe', 'President', 'Male', 'BSIT', '4th Year', 'Experienced leader'],
        ['Jane', 'Smith', 'Vice President', 'Female', 'BSCS', '3rd Year', 'Student advocate'],
        ['Mike', 'Johnson', 'Secretary', 'Male', 'BSIT', '2nd Year', 'Organized'],
        ['Sarah', 'Wilson', 'Treasurer', 'Female', 'BSCS', '3rd Year', 'Financial expert'],
        ['David', 'Brown', 'Mayor', 'Male', 'BSIT', '4th Year', 'Community leader'],
        ['Lisa', 'Garcia', 'Auditor', 'Female', 'Bachelor of Arts in Communication', '2nd Year', 'Transparency advocate']
    ];
    
    $stmt = $conn->prepare("INSERT INTO candidates (first_name, last_name, position, gender, course, year_level, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($candidates as $candidate) {
        $stmt->execute($candidate);
        echo "<p style='color: green;'>✅ Added: {$candidate[0]} {$candidate[1]} - {$candidate[2]}</p>";
    }
    
    echo "<h2 style='color: green;'>🎉 Success! Added " . count($candidates) . " candidates</h2>";
    echo "<p><a href='Admin/admin candidate/admincandidate.html' style='background: #1976d2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
