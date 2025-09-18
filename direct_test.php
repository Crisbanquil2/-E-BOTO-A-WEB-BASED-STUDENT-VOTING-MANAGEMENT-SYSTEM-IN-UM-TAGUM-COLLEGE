<?php
/**
 * Direct Login Test
 * Test admin authentication directly
 */

require_once '../../config/database.php';

echo "<h1>Direct Admin Login Test</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test admin login
    $username = 'admin';
    $password = 'admin123';
    
    echo "<h2>Testing Login: $username / $password</h2>";
    
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>✅ Admin found in database:</p>";
        echo "<ul>";
        echo "<li>ID: " . $admin['admin_id'] . "</li>";
        echo "<li>Username: " . $admin['username'] . "</li>";
        echo "<li>Full Name: " . $admin['full_name'] . "</li>";
        echo "<li>Role: " . $admin['role'] . "</li>";
        echo "<li>Status: " . $admin['status'] . "</li>";
        echo "</ul>";
        
        // Test password
        if (password_verify($password, $admin['password'])) {
            echo "<p style='color: green;'>✅ Password verification successful!</p>";
            echo "<p style='color: green;'>✅ Login should work!</p>";
            
            // Test session creation
            session_start();
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_logged_in'] = true;
            
            echo "<p style='color: green;'>✅ Session created successfully!</p>";
            echo "<p><a href='../Admin home/Adminhome.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a></p>";
            
        } else {
            echo "<p style='color: red;'>❌ Password verification failed!</p>";
            echo "<p>Stored password hash: " . $admin['password'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Admin not found or inactive!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
