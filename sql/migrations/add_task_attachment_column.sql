-- =============================================
-- Add taskAttachment column to tija_proposal_tasks table
-- Run this SQL script to enable file attachments for proposal tasks
-- =============================================

-- Check if column exists before adding (MySQL 5.7+ compatible)
-- If column already exists, this will fail gracefully

ALTER TABLE `tija_proposal_tasks`
ADD COLUMN `taskAttachment` VARCHAR(500) NULL DEFAULT NULL
COMMENT 'Relative path to attached file for the task'
AFTER `isMandatory`;

-- Alternative syntax if using MariaDB or if the above fails:
-- You can run this in phpMyAdmin or MySQL CLI

-- To verify the column was added:
-- DESCRIBE tija_proposal_tasks;
