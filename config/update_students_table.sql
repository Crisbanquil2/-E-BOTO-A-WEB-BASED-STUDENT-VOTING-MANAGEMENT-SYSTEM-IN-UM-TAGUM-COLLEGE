-- Update students table to include gender and password columns
-- Run this SQL to update your existing database

-- Add gender column if it doesn't exist
ALTER TABLE `students` 
ADD COLUMN IF NOT EXISTS `gender` enum('MALE', 'FEMALE') DEFAULT NULL AFTER `year_level`;

-- Add password column if it doesn't exist
ALTER TABLE `students` 
ADD COLUMN IF NOT EXISTS `password` varchar(255) DEFAULT NULL AFTER `gender`;

-- Update the table structure to match the registration form
-- This ensures compatibility with the new registration API
