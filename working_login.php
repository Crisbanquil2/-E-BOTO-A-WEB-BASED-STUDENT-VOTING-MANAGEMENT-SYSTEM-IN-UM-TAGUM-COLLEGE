<?php
/**
 * Working Admin Login
 * Simple login that definitely works
 */

// Start session
session_start();

$error = '';
$success = '';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ../Admin home/Adminhome.html');
    exit;
}

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded admin for testing
    if ($username === 'admin' && $password === 'admin123') {
        // Login successful
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = 'admin';
        $_SESSION['admin_role'] = 'super_admin';
        $_SESSION['admin_logged_in'] = true;
        
        // Set session data for JavaScript
        $sessionData = [
            'admin_id' => 1,
            'username' => 'admin',
            'full_name' => 'System Administrator',
            'role' => 'super_admin',
            'login_time' => time()
        ];
        
        // Redirect to admin dashboard
        echo "<script>
            sessionStorage.setItem('admin_session', '" . json_encode($sessionData) . "');
            window.location.href = '../Admin home/Adminhome.html';
        </script>";
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Student Voting System</title>
    <link rel="stylesheet" href="AdminLogin.css">
    <link rel="icon" href="../../pictures/logo.png">
    <meta name="color-scheme" content="light only">
    <style>
        .test-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
            font-size: 14px;
        }
        .test-info code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="left hero" aria-label="Background panel"></aside>
        <section class="right">
            <div class="container">
                <div class="brand">
                    <img class="Backround1" src="../../pictures/Students.png" alt="">
                    <img class="logo" src="../../pictures/logo.png" alt="University logo">
                    <h1>Admin Portal</h1>
                    <p>Student Voting Management System</p>
                </div>
                <div class="card" role="region" aria-label="Admin login form">
                    <h2>Admin Login</h2>
                    
                    <div class="test-info">
                        <strong>Test Credentials:</strong><br>
                        Username: <code>admin</code><br>
                        Password: <code>admin123</code>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="field">
                            <label for="username">Admin Username</label>
                            <input type="text" id="username" name="username" value="admin" required>
                        </div>
                        <div class="field">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" value="admin123" required>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn">Sign in as Admin</button>
                        </div>
                        <div class="helper" style="justify-content:center; margin-top:12px;">
                            <a href="../../Login/login.html" aria-label="Back to student login">Back to Student Login</a>
                        </div>
                    </form>
                </div>
                <div class="footer"></div>
            </div>
        </section>
    </div>
    
    <script>
        const yearEl = document.getElementById('year');
        if (yearEl) yearEl.textContent = new Date().getFullYear();
    </script>
</body>
</html>
