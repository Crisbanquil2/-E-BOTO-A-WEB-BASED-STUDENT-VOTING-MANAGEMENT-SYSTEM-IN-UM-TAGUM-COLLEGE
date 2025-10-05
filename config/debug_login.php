<?php
/**
 * Debug script to troubleshoot login issues
 */

require_once 'database.php';

echo "<h2>Debug Login Issues</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h3>1. Check what's in the students table</h3>";
    
    $stmt = $conn->prepare("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    if (empty($students)) {
        echo "❌ No students found in database<br>";
        echo "Try registering a student first.<br><br>";
    } else {
        echo "✅ Found " . count($students) . " students in database:<br><br>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Student Number</th><th>Name</th><th>Email</th><th>Password Hash</th><th>Status</th></tr>";
        
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>" . $student['student_id'] . "</td>";
            echo "<td>" . $student['student_number'] . "</td>";
            echo "<td>" . $student['first_name'] . " " . $student['last_name'] . "</td>";
            echo "<td>" . $student['email'] . "</td>";
            echo "<td>" . (empty($student['password']) ? '❌ EMPTY' : '✅ SET (' . strlen($student['password']) . ' chars)') . "</td>";
            echo "<td>" . $student['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    echo "<h3>2. Test Login API with first student</h3>";
    
    if (!empty($students)) {
        $firstStudent = $students[0];
        echo "Testing with student: " . $firstStudent['student_number'] . "<br>";
        echo "Student name: " . $firstStudent['first_name'] . " " . $firstStudent['last_name'] . "<br>";
        
        // Test with a common password
        $testPasswords = ['password123', 'password', '123456', 'test123'];
        
        foreach ($testPasswords as $testPassword) {
            echo "<br>Testing password: '$testPassword'<br>";
            
            if (empty($firstStudent['password'])) {
                echo "❌ No password stored in database<br>";
                break;
            }
            
            $isValid = password_verify($testPassword, $firstStudent['password']);
            echo "Password verification: " . ($isValid ? "✅ VALID" : "❌ INVALID") . "<br>";
            
            if ($isValid) {
                echo "🎉 Found working password: '$testPassword'<br>";
                break;
            }
        }
    }
    
    echo "<h3>3. Test Login with Existing Student</h3>";
    
    // Test login with the first student (TEST001)
    require_once 'login_api.php';
    $loginApi = new LoginAPI();
    
    $loginResult = $loginApi->authenticateStudent('TEST001', 'password123');
    
    if ($loginResult['success']) {
        echo "✅ Login test successful!<br>";
        echo "Student logged in: " . $loginResult['student']['first_name'] . " " . $loginResult['student']['last_name'] . "<br>";
        echo "Student ID: " . $loginResult['student']['student_number'] . "<br>";
        echo "Email: " . $loginResult['student']['email'] . "<br>";
    } else {
        echo "❌ Login test failed: " . $loginResult['message'] . "<br>";
    }
    
    echo "<br><h3>4. Test with Your Real Account</h3>";
    echo "Try logging in with one of your real accounts:<br>";
    echo "Student ID: 2003 (cris gwapo)<br>";
    echo "Password: [whatever password you used when registering]<br>";
    echo "Or try: Student ID: 1911 (gwapo banquil)<br>";
    echo "Password: [whatever password you used when registering]<br>";
    
    echo "<h3>5. Manual Login Test</h3>";
    echo "Try logging in with these credentials:<br>";
    echo "Student ID: DEBUG001<br>";
    echo "Password: debug123<br>";
    echo "<a href='../Login/login.html' target='_blank'>Go to Login Page</a><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Debug Complete</h3>";
echo "<p>If you see any ❌ errors above, those are the issues that need to be fixed.</p>";
?>
