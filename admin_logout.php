<?php
/**
 * Admin Logout Script
 * Handles admin logout and session cleanup
 */

require_once '../../config/database.php';

class AdminLogout {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Logout admin and clean up session
     */
    public function logout() {
        try {
            // Log logout action if admin is logged in
            if (isset($_SESSION['admin_id'])) {
                $this->logLogoutAction($_SESSION['admin_id']);
            }

            // Destroy database session if cookie exists
            if (isset($_COOKIE['admin_session'])) {
                $this->destroyDatabaseSession($_COOKIE['admin_session']);
                setcookie('admin_session', '', time() - 3600, '/', '', false, true);
            }

            // Clear all session data
            $_SESSION = array();

            // Destroy session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Destroy the session
            session_destroy();

            return true;
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log logout action
     */
    private function logLogoutAction($adminId) {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $stmt = $this->conn->prepare("
                INSERT INTO admin_logs (admin_id, action, description, ip_address, user_agent) 
                VALUES (?, 'logout', 'Admin logged out', ?, ?)
            ");
            $stmt->execute([$adminId, $ipAddress, $userAgent]);
        } catch (Exception $e) {
            error_log("Logout logging error: " . $e->getMessage());
        }
    }

    /**
     * Destroy database session
     */
    private function destroyDatabaseSession($sessionId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM admin_sessions WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        } catch (Exception $e) {
            error_log("Session destruction error: " . $e->getMessage());
        }
    }
}

// Handle logout
$logout = new AdminLogout();
$success = $logout->logout();

// Redirect to login page
if ($success) {
    header('Location: AdminLogin.html?logged_out=1');
} else {
    header('Location: AdminLogin.html?error=logout_failed');
}
exit;
?>
