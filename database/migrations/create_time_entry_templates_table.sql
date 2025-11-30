-- =============================================
-- Time Entry Templates Table
-- =============================================
-- Created: 2025-11-03
-- Purpose: Store reusable time entry templates for quick data entry
-- Author: Development Team
-- Version: 1.0
-- =============================================

-- Create the time_entry_templates table
CREATE TABLE IF NOT EXISTS `time_entry_templates` (
    `templateID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key for template',
    `userID` int(11) NOT NULL COMMENT 'User who created the template',
    `templateName` varchar(100) NOT NULL COMMENT 'Name/description of the template',
    `templateData` text NOT NULL COMMENT 'JSON data containing template fields',
    `createdDate` datetime NOT NULL COMMENT 'When the template was created',
    `modifiedDate` datetime DEFAULT NULL COMMENT 'Last modification date',
    `Suspended` char(1) DEFAULT 'N' COMMENT 'Y/N - Is template active?',
    PRIMARY KEY (`templateID`),
    KEY `idx_userID` (`userID`),
    KEY `idx_suspended` (`Suspended`),
    KEY `idx_user_suspended` (`userID`, `Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores reusable time entry templates for quick data entry';

-- =============================================
-- Sample Data (Optional - for testing)
-- =============================================
-- Uncomment the following to insert sample templates


INSERT INTO `time_entry_templates`
    (`userID`, `templateName`, `templateData`, `createdDate`, `Suspended`)
VALUES
    (1, 'Daily Standup', '{"projectID":"5","workTypeID":"1","taskDuration":"00:15","taskStatusID":"2","taskNarrative":"Daily standup meeting"}', NOW(), 'N'),
    (1, 'Code Review', '{"projectID":"5","workTypeID":"2","taskDuration":"01:00","taskStatusID":"2","taskNarrative":"Code review session"}', NOW(), 'N'),
    (1, 'Documentation', '{"projectID":"5","workTypeID":"3","taskDuration":"02:00","taskStatusID":"2","taskNarrative":"Writing technical documentation"}', NOW(), 'N');z

-- =============================================
-- Verification Query
-- =============================================
-- Run this query to verify the table was created successfully

-- SELECT TABLE_NAME, TABLE_ROWS, CREATE_TIME
-- FROM information_schema.TABLES
-- WHERE TABLE_SCHEMA = DATABASE()
-- AND TABLE_NAME = 'time_entry_templates';

-- =============================================
-- Rollback/Drop Table (Use with caution!)
-- =============================================
-- Uncomment to drop the table if needed

-- DROP TABLE IF EXISTS `time_entry_templates`;

-- =============================================
-- END OF MIGRATION
-- =============================================

