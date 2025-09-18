<?php
/**
 * Create Working Admin Account
 * Create a simple admin account that will work
 */

require_once '../../config/database.php';

echo "<h1>Create Working Admin Account</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Delete existing admin accounts
    $stmt = $conn->prepare("DELETE FROM admins");
    $stmt->execute();
    echo "<p>🗑️ Deleted existing admin accounts</p>";
    
    // Create new admin account with simple password
    $username = 'admin';
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        INSERT INTO admins (username, password, full_name, email, role, status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $username,
        $hashedPassword,
        'System Administrator',
        'admin@votingsystem.com',
        'super_admin',
        'active'
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Admin account created successfully!</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Hashed Password:</strong> " . $hashedPassword . "</p>";
        
        // Test the password
        if (password_verify($password, $hashedPassword)) {
            echo "<p style='color: green;'>✅ Password verification test passed!</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification test failed!</p>";
        }
        
        echo "<p><a href='simple_login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login Now</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to create admin account!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
