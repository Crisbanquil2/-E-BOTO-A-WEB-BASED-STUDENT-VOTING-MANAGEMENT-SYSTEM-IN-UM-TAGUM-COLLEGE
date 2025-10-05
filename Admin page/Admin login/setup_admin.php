<?php
/**
 * Admin Setup Script
 * Initialize admin database and create default accounts
 */

require_once '../../config/database.php';

// Set JSON header
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Read and execute SQL file
    $sqlFile = '../../config/admin_database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('Admin database SQL file not found');
    }

    $sql = file_get_contents($sqlFile);
    $statements = explode(';', $sql);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }

    // Initialize default admin accounts
    require_once 'admin_management.php';
    $adminMgmt = new AdminManagement();
    $result = $adminMgmt->initializeDefaultAdmins();

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Admin database setup completed successfully',
            'default_accounts' => [
                'admin' => 'admin123',
                'moderator' => 'moderator123'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database setup completed but failed to create default accounts: ' . $result['message']
        ]);
    }

} catch (Exception $e) {
    error_log("Admin setup error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Setup failed: ' . $e->getMessage()
    ]);
}
?>
