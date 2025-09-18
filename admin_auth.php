<?php
/**
 * Admin Authentication System
 * Handles admin login, session management, and security
 */

require_once '../../config/database.php';

class AdminAuth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Authenticate admin login
     */
    public function login($username, $password) {
        try {
            $stmt = $this->conn->prepare("
                SELECT admin_id, username, password, full_name, email, role, status 
                FROM admins 
                WHERE username = ? AND status = 'active'
            ");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && (password_verify($password, $admin['password']) || $admin['password'] === $password)) {
                // Update last login
                $this->updateLastLogin($admin['admin_id']);
                
                // Log login action
                $this->logAction($admin['admin_id'], 'login', 'Admin logged in successfully');
                
                // Create session
                $this->createSession($admin);
                
                return [
                    'success' => true,
                    'admin' => [
                        'admin_id' => $admin['admin_id'],
                        'username' => $admin['username'],
                        'full_name' => $admin['full_name'],
                        'email' => $admin['email'],
                        'role' => $admin['role']
                    ]
                ];
            }

            return ['success' => false, 'message' => 'Invalid username or password'];
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    /**
     * Create admin session
     */
    private function createSession($admin) {
        $sessionId = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        try {
            // Store session in database
            $stmt = $this->conn->prepare("
                INSERT INTO admin_sessions (session_id, admin_id, ip_address, user_agent, expires_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$sessionId, $admin['admin_id'], $ipAddress, $userAgent, $expiresAt]);

            // Set session cookie
            setcookie('admin_session', $sessionId, time() + (24 * 60 * 60), '/', '', false, true);
            
            // Set PHP session
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_logged_in'] = true;

        } catch (Exception $e) {
            error_log("Session creation error: " . $e->getMessage());
        }
    }

    /**
     * Check if admin is logged in
     */
    public function isLoggedIn() {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            return true;
        }

        // Check session cookie
        if (isset($_COOKIE['admin_session'])) {
            return $this->validateSession($_COOKIE['admin_session']);
        }

        return false;
    }

    /**
     * Validate session from cookie
     */
    private function validateSession($sessionId) {
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
                $_SESSION['admin_id'] = $session['admin_id'];
                $_SESSION['admin_username'] = $session['username'];
                $_SESSION['admin_role'] = $session['role'];
                $_SESSION['admin_logged_in'] = true;
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Session validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logout admin
     */
    public function logout() {
        if (isset($_COOKIE['admin_session'])) {
            $this->destroySession($_COOKIE['admin_session']);
            setcookie('admin_session', '', time() - 3600, '/', '', false, true);
        }

        // Log logout action
        if (isset($_SESSION['admin_id'])) {
            $this->logAction($_SESSION['admin_id'], 'logout', 'Admin logged out');
        }

        // Clear session
        session_destroy();
        return true;
    }

    /**
     * Destroy session
     */
    private function destroySession($sessionId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM admin_sessions WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        } catch (Exception $e) {
            error_log("Session destruction error: " . $e->getMessage());
        }
    }

    /**
     * Update last login timestamp
     */
    private function updateLastLogin($adminId) {
        try {
            $stmt = $this->conn->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
            $stmt->execute([$adminId]);
        } catch (Exception $e) {
            error_log("Last login update error: " . $e->getMessage());
        }
    }

    /**
     * Log admin action
     */
    private function logAction($adminId, $action, $description = '') {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $stmt = $this->conn->prepare("
                INSERT INTO admin_logs (admin_id, action, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$adminId, $action, $description, $ipAddress, $userAgent]);
        } catch (Exception $e) {
            error_log("Action logging error: " . $e->getMessage());
        }
    }

    /**
     * Get current admin info
     */
    public function getCurrentAdmin() {
        if ($this->isLoggedIn()) {
            return [
                'admin_id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'role' => $_SESSION['admin_role']
            ];
        }
        return null;
    }

    /**
     * Check if admin has specific role
     */
    public function hasRole($requiredRole) {
        $admin = $this->getCurrentAdmin();
        if (!$admin) return false;

        $roleHierarchy = ['moderator' => 1, 'admin' => 2, 'super_admin' => 3];
        $adminLevel = $roleHierarchy[$admin['role']] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

        return $adminLevel >= $requiredLevel;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    $auth = new AdminAuth();

    switch ($action) {
        case 'login':
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            $result = $auth->login($username, $password);
            echo json_encode($result);
            break;

        case 'logout':
            $auth->logout();
            echo json_encode(['success' => true]);
            break;

        case 'check_session':
            echo json_encode(['authenticated' => $auth->isLoggedIn()]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>
