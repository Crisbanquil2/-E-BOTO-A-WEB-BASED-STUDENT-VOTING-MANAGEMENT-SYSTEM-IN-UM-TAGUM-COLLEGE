<?php
/**
 * Admin Session Check
 * Returns JSON response about admin authentication status
 */

require_once '../../config/database.php';

class AdminSessionCheck {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Check if admin session is valid
     */
    public function checkSession() {
        // Check PHP session first
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            return [
                'authenticated' => true,
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'username' => $_SESSION['admin_username'] ?? null,
                'role' => $_SESSION['admin_role'] ?? null
            ];
        }

        // Check session cookie
        if (isset($_COOKIE['admin_session'])) {
            return $this->validateSessionCookie($_COOKIE['admin_session']);
        }

        return ['authenticated' => false];
    }

    /**
     * Validate session cookie
     */
    private function validateSessionCookie($sessionId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT a.admin_id, a.username, a.full_name, a.email, a.role, s.expires_at
                FROM admin_sessions s
                JOIN admins a ON s.admin_id = a.admin_id
                WHERE s.session_id = ? AND s.expires_at > NOW() AND a.status = 'active'
            ");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch();

            if ($session) {
                // Update session in PHP
                $_SESSION['admin_id'] = $session['admin_id'];
                $_SESSION['admin_username'] = $session['username'];
                $_SESSION['admin_role'] = $session['role'];
                $_SESSION['admin_logged_in'] = true;

                return [
                    'authenticated' => true,
                    'admin_id' => $session['admin_id'],
                    'username' => $session['username'],
                    'role' => $session['role']
                ];
            }

            // Clean up expired session
            $this->cleanupExpiredSession($sessionId);
            return ['authenticated' => false];

        } catch (Exception $e) {
            error_log("Session validation error: " . $e->getMessage());
            return ['authenticated' => false];
        }
    }

    /**
     * Clean up expired session
     */
    private function cleanupExpiredSession($sessionId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM admin_sessions WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        } catch (Exception $e) {
            error_log("Session cleanup error: " . $e->getMessage());
        }
    }
}

// Set JSON header
header('Content-Type: application/json');

// Initialize session check
$sessionCheck = new AdminSessionCheck();
$result = $sessionCheck->checkSession();

// Return JSON response
echo json_encode($result);
?>
