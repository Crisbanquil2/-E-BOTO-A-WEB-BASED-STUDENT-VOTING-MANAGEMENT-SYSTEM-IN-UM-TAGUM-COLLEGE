<?php
/**
 * Check Database Contents
 * See what admin accounts exist
 */

require_once '../../config/database.php';

echo "<h1>Database Check</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Get all admin accounts
    $stmt = $conn->prepare("SELECT admin_id, username, password, full_name, role, status FROM admins");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    echo "<h2>Admin Accounts in Database:</h2>";
    if (count($admins) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Password</th><th>Full Name</th><th>Role</th><th>Status</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['admin_id'] . "</td>";
            echo "<td>" . $admin['username'] . "</td>";
            echo "<td>" . substr($admin['password'], 0, 20) . "...</td>";
            echo "<td>" . $admin['full_name'] . "</td>";
            echo "<td>" . $admin['role'] . "</td>";
            echo "<td>" . $admin['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No admin accounts found!</p>";
    }
    
    echo "<h2>Test Password Verification:</h2>";
    foreach ($admins as $admin) {
        echo "<h3>Testing: " . $admin['username'] . "</h3>";
        
        // Test with admin123
        if (password_verify('admin123', $admin['password'])) {
            echo "<p style='color: green;'>✅ Password 'admin123' matches!</p>";
        } else {
            echo "<p style='color: red;'>❌ Password 'admin123' does NOT match</p>";
        }
        
        // Test with test123
        if (password_verify('test123', $admin['password'])) {
            echo "<p style='color: green;'>✅ Password 'test123' matches!</p>";
        } else {
            echo "<p style='color: red;'>❌ Password 'test123' does NOT match</p>";
        }
        
        // Test plain text
        if ($admin['password'] === 'admin123') {
            echo "<p style='color: orange;'>⚠️ Password is plain text 'admin123'</p>";
        }
        if ($admin['password'] === 'test123') {
            echo "<p style='color: orange;'>⚠️ Password is plain text 'test123'</p>";
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
