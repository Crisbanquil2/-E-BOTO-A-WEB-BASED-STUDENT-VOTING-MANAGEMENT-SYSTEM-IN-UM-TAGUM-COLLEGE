<?php
/**
 * Simple Admin Login
 * Direct login without AJAX
 */

require_once '../../config/database.php';

// Start session
session_start();

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && (password_verify($password, $admin['password']) || $admin['password'] === $password)) {
            // Login successful
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_logged_in'] = true;
            
            // Create session data for JavaScript
            $sessionData = [
                'admin_id' => $admin['admin_id'],
                'username' => $admin['username'],
                'full_name' => $admin['full_name'],
                'role' => $admin['role'],
                'login_time' => time()
            ];
            
            // Redirect to admin dashboard with session data
            $sessionJson = json_encode($sessionData);
            echo "<script>
                sessionStorage.setItem('admin_session', '$sessionJson');
                window.location.href = '../Admin home/Adminhome.html';
            </script>";
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } catch (Exception $e) {
        $error = 'Login failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Simple</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(145deg, #eef2f7, #e6ebf2);
            margin: 0; 
            padding: 50px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-group { 
            margin: 20px 0; 
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input { 
            padding: 12px; 
            margin: 5px 0; 
            width: 100%; 
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button { 
            padding: 12px 20px; 
            background: #dc2626; 
            color: white; 
            border: none; 
            cursor: pointer; 
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background: #b91c1c;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        h1 {
            text-align: center;
            color: #dc2626;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="admin" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="admin123" required>
            </div>
            <button type="submit">Login as Admin</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="AdminLogin.html">Back to Original Login</a>
        </p>
    </div>
</body>
</html>
