-- Drop existing tables first
DROP TABLE IF EXISTS `admin_logs`;
DROP TABLE IF EXISTS `admin_sessions`;
DROP TABLE IF EXISTS `admins`;

-- Create admins table
CREATE TABLE `admins` (
    `admin_id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `role` enum('super_admin', 'admin', 'moderator') NOT NULL DEFAULT 'admin',
    `status` enum('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `last_login` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`admin_id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_sessions table
CREATE TABLE `admin_sessions` (
    `session_id` varchar(128) NOT NULL,
    `admin_id` int(11) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at` timestamp NOT NULL,
    PRIMARY KEY (`session_id`),
    KEY `admin_id` (`admin_id`),
    KEY `expires_at` (`expires_at`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_logs table
CREATE TABLE `admin_logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) NOT NULL,
    `action` varchar(100) NOT NULL,
    `description` text,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `admin_id` (`admin_id`),
    KEY `action` (`action`),
    KEY `created_at` (`created_at`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account
-- Password: admin123 (hashed with password_hash())
INSERT INTO `admins` (`username`, `password`, `full_name`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@votingsystem.com', 'super_admin', 'active'),
('moderator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Election Moderator', 'moderator@votingsystem.com', 'moderator', 'active');

-- Create indexes for better performance (only if they don't exist)
CREATE INDEX IF NOT EXISTS `idx_admins_status` ON `admins` (`status`);
CREATE INDEX IF NOT EXISTS `idx_admins_role` ON `admins` (`role`);
CREATE INDEX IF NOT EXISTS `idx_admin_logs_admin_action` ON `admin_logs` (`admin_id`, `action`);
