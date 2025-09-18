<?php
/**
 * Simple Logout Script
 * Clear session and redirect to login
 */

session_start();

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear JavaScript session data and redirect
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Clear session storage
        sessionStorage.removeItem('admin_session');
        localStorage.removeItem('admin_session');
        
        // Redirect to login
        window.location.href = 'working_login.php';
    </script>
    <p>Logging out...</p>
</body>
</html>
