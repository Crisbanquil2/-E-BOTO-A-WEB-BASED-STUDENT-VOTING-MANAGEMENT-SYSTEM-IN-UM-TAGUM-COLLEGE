<?php
/**
 * Check database structure to ensure all columns exist
 */

require_once 'database.php';

echo "<h2>Database Structure Check</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h3>1. Students Table Structure</h3>";
    
    $stmt = $conn->prepare("DESCRIBE students");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Check if required columns exist
    $requiredColumns = ['student_id', 'student_number', 'first_name', 'last_name', 'email', 'course', 'year_level', 'gender', 'password', 'status'];
    $existingColumns = array_column($columns, 'Field');
    
    echo "<h3>2. Required Columns Check</h3>";
    
    foreach ($requiredColumns as $required) {
        $exists = in_array($required, $existingColumns);
        echo $required . ": " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "<br>";
    }
    
    echo "<h3>3. Sample Data Check</h3>";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    
    echo "Total students in database: " . $count . "<br>";
    
    if ($count > 0) {
        $stmt = $conn->prepare("SELECT student_number, first_name, last_name, email, password FROM students LIMIT 1");
        $stmt->execute();
        $sample = $stmt->fetch();
        
        echo "<br>Sample student data:<br>";
        echo "Student Number: " . $sample['student_number'] . "<br>";
        echo "Name: " . $sample['first_name'] . " " . $sample['last_name'] . "<br>";
        echo "Email: " . $sample['email'] . "<br>";
        echo "Password: " . (empty($sample['password']) ? '❌ EMPTY' : '✅ SET') . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Structure Check Complete</h3>";
?>
