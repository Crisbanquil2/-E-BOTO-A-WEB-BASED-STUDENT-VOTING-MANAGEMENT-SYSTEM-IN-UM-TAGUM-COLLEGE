<?php
/**
 * Admin Management System
 * Handles admin account creation, updates, and management
 */

require_once '../../config/database.php';

class AdminManagement {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Create new admin account
     */
    public function createAdmin($username, $password, $fullName, $email, $role = 'admin') {
        try {
            // Check if username or email already exists
            $stmt = $this->conn->prepare("
                SELECT admin_id FROM admins 
                WHERE username = ? OR email = ?
            ");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new admin
            $stmt = $this->conn->prepare("
                INSERT INTO admins (username, password, full_name, email, role, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$username, $hashedPassword, $fullName, $email, $role]);

            $adminId = $this->conn->lastInsertId();

            // Log admin creation
            $this->logAction($adminId, 'admin_created', "New admin account created: $username");

            return [
                'success' => true, 
                'message' => 'Admin account created successfully',
                'admin_id' => $adminId
            ];

        } catch (Exception $e) {
            error_log("Admin creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create admin account'];
        }
    }

    /**
     * Update admin account
     */
    public function updateAdmin($adminId, $data) {
        try {
            $fields = [];
            $values = [];

            if (isset($data['full_name'])) {
                $fields[] = 'full_name = ?';
                $values[] = $data['full_name'];
            }

            if (isset($data['email'])) {
                $fields[] = 'email = ?';
                $values[] = $data['email'];
            }

            if (isset($data['role'])) {
                $fields[] = 'role = ?';
                $values[] = $data['role'];
            }

            if (isset($data['status'])) {
                $fields[] = 'status = ?';
                $values[] = $data['status'];
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = 'password = ?';
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($fields)) {
                return ['success' => false, 'message' => 'No fields to update'];
            }

            $fields[] = 'updated_at = NOW()';
            $values[] = $adminId;

            $sql = "UPDATE admins SET " . implode(', ', $fields) . " WHERE admin_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);

            // Log update action
            $this->logAction($adminId, 'admin_updated', 'Admin account updated');

            return ['success' => true, 'message' => 'Admin account updated successfully'];

        } catch (Exception $e) {
            error_log("Admin update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update admin account'];
        }
    }

    /**
     * Get all admins
     */
    public function getAllAdmins() {
        try {
            $stmt = $this->conn->prepare("
                SELECT admin_id, username, full_name, email, role, status, 
                       last_login, created_at, updated_at
                FROM admins 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get admins error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get admin by ID
     */
    public function getAdminById($adminId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT admin_id, username, full_name, email, role, status, 
                       last_login, created_at, updated_at
                FROM admins 
                WHERE admin_id = ?
            ");
            $stmt->execute([$adminId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get admin error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete admin account
     */
    public function deleteAdmin($adminId) {
        try {
            // Don't allow deletion of super admin
            $admin = $this->getAdminById($adminId);
            if ($admin && $admin['role'] === 'super_admin') {
                return ['success' => false, 'message' => 'Cannot delete super admin account'];
            }

            // Delete admin sessions first
            $stmt = $this->conn->prepare("DELETE FROM admin_sessions WHERE admin_id = ?");
            $stmt->execute([$adminId]);

            // Delete admin logs
            $stmt = $this->conn->prepare("DELETE FROM admin_logs WHERE admin_id = ?");
            $stmt->execute([$adminId]);

            // Delete admin
            $stmt = $this->conn->prepare("DELETE FROM admins WHERE admin_id = ?");
            $stmt->execute([$adminId]);

            return ['success' => true, 'message' => 'Admin account deleted successfully'];

        } catch (Exception $e) {
            error_log("Admin deletion error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete admin account'];
        }
    }

    /**
     * Get admin logs
     */
    public function getAdminLogs($adminId = null, $limit = 100) {
        try {
            $sql = "
                SELECT al.log_id, al.admin_id, a.username, al.action, al.description, 
                       al.ip_address, al.created_at
                FROM admin_logs al
                JOIN admins a ON al.admin_id = a.admin_id
            ";
            
            $params = [];
            if ($adminId) {
                $sql .= " WHERE al.admin_id = ?";
                $params[] = $adminId;
            }
            
            $sql .= " ORDER BY al.created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get admin logs error: " . $e->getMessage());
            return [];
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
     * Initialize default admin accounts
     */
    public function initializeDefaultAdmins() {
        try {
            // Check if admins already exist
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM admins");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => true, 'message' => 'Admins already exist'];
            }

            // Create default admins
            $defaultAdmins = [
                [
                    'username' => 'admin',
                    'password' => 'admin123',
                    'full_name' => 'System Administrator',
                    'email' => 'admin@votingsystem.com',
                    'role' => 'super_admin'
                ],
                [
                    'username' => 'moderator',
                    'password' => 'moderator123',
                    'full_name' => 'Election Moderator',
                    'email' => 'moderator@votingsystem.com',
                    'role' => 'moderator'
                ]
            ];

            foreach ($defaultAdmins as $admin) {
                $this->createAdmin(
                    $admin['username'],
                    $admin['password'],
                    $admin['full_name'],
                    $admin['email'],
                    $admin['role']
                );
            }

            return ['success' => true, 'message' => 'Default admin accounts created'];

        } catch (Exception $e) {
            error_log("Initialize admins error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to initialize admin accounts'];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    $adminMgmt = new AdminManagement();

    switch ($action) {
        case 'create_admin':
            $result = $adminMgmt->createAdmin(
                $input['username'] ?? '',
                $input['password'] ?? '',
                $input['full_name'] ?? '',
                $input['email'] ?? '',
                $input['role'] ?? 'admin'
            );
            echo json_encode($result);
            break;

        case 'get_admins':
            $admins = $adminMgmt->getAllAdmins();
            echo json_encode(['success' => true, 'admins' => $admins]);
            break;

        case 'initialize_defaults':
            $result = $adminMgmt->initializeDefaultAdmins();
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>
