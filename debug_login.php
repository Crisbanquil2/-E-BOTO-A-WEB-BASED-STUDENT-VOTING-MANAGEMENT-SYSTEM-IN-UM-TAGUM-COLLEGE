<?php
/**
 * Debug Login Script
 * Check database connection and admin accounts
 */

require_once '../../config/database.php';

echo "<h2>Admin Login Debug</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Check if admins table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'admins'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: green;'>✅ Admins table exists!</p>";
        
        // Check admin accounts
        $stmt = $conn->prepare("SELECT admin_id, username, full_name, role, status FROM admins");
        $stmt->execute();
        $admins = $stmt->fetchAll();
        
        if (count($admins) > 0) {
            echo "<p style='color: green;'>✅ Found " . count($admins) . " admin account(s):</p>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Status</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . $admin['admin_id'] . "</td>";
                echo "<td>" . $admin['username'] . "</td>";
                echo "<td>" . $admin['full_name'] . "</td>";
                echo "<td>" . $admin['role'] . "</td>";
                echo "<td>" . $admin['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ No admin accounts found!</p>";
            echo "<p>Run the setup script or create admin accounts manually.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Admins table does not exist!</p>";
        echo "<p>Run the database setup first.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Test Login</h3>";
echo "<form method='post'>";
echo "<p>Username: <input type='text' name='test_username' value='admin'></p>";
echo "<p>Password: <input type='password' name='test_password' value='admin123'></p>";
echo "<p><input type='submit' name='test_login' value='Test Login'></p>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $username = $_POST['test_username'];
    $password = $_POST['test_password'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Check if password is hashed or plain text
            if (password_verify($password, $admin['password'])) {
                echo "<p style='color: green;'>✅ Login successful! Password matches (hashed).</p>";
            } elseif ($admin['password'] === $password) {
                echo "<p style='color: orange;'>⚠️ Password matches (plain text) - should be hashed!</p>";
                echo "<p>Stored password: " . $admin['password'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Password does not match.</p>";
                echo "<p>Stored password: " . $admin['password'] . "</p>";
                echo "<p>Expected: " . $password . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Username not found or account inactive.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Login test error: " . $e->getMessage() . "</p>";
    }
}
?>