-- Candidates Database Structure for Student Voting Management System
-- This file creates the necessary tables for candidates and voting

-- Create candidates table
CREATE TABLE IF NOT EXISTS `candidates` (
    `candidate_id` int(11) NOT NULL AUTO_INCREMENT,
    `first_name` varchar(50) NOT NULL,
    `last_name` varchar(50) NOT NULL,
    `position` varchar(50) NOT NULL,
    `gender` enum('Male', 'Female', 'Other') NOT NULL,
    `course` varchar(100) NOT NULL,
    `year_level` varchar(20) NOT NULL,
    `description` text,
    `photo` longtext,
    `status` enum('active', 'inactive', 'disqualified') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`candidate_id`),
    KEY `position` (`position`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create votes table
CREATE TABLE IF NOT EXISTS `votes` (
    `vote_id` int(11) NOT NULL AUTO_INCREMENT,
    `student_id` int(11) NOT NULL,
    `candidate_id` int(11) NOT NULL,
    `position` varchar(50) NOT NULL,
    `voted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text,
    PRIMARY KEY (`vote_id`),
    UNIQUE KEY `unique_student_position` (`student_id`, `position`),
    KEY `candidate_id` (`candidate_id`),
    KEY `position` (`position`),
    KEY `voted_at` (`voted_at`),
    FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create students table (if not exists)
CREATE TABLE IF NOT EXISTS `students` (
    `student_id` int(11) NOT NULL AUTO_INCREMENT,
    `student_number` varchar(20) NOT NULL,
    `first_name` varchar(50) NOT NULL,
    `last_name` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `course` varchar(100) NOT NULL,
    `year_level` varchar(20) NOT NULL,
    `gender` enum('MALE', 'FEMALE') DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `status` enum('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`student_id`),
    UNIQUE KEY `student_number` (`student_number`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create voting_sessions table to track voting periods
CREATE TABLE IF NOT EXISTS `voting_sessions` (
    `session_id` int(11) NOT NULL AUTO_INCREMENT,
    `session_name` varchar(100) NOT NULL,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `status` enum('upcoming', 'active', 'ended', 'cancelled') NOT NULL DEFAULT 'upcoming',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`session_id`),
    KEY `status` (`status`),
    KEY `start_date` (`start_date`),
    KEY `end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample voting session
INSERT INTO `voting_sessions` (`session_name`, `start_date`, `end_date`, `status`) VALUES
('Student Council Election 2024', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'active');

-- Create indexes for better performance
CREATE INDEX `idx_candidates_position_status` ON `candidates` (`position`, `status`);
CREATE INDEX `idx_votes_candidate_position` ON `votes` (`candidate_id`, `position`);
CREATE INDEX `idx_votes_student_position` ON `votes` (`student_id`, `position`);
