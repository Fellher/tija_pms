-- ============================================================================
-- Migration: Add Activity Wizard Fields
-- Description: Adds fields for enhanced activity management including outcomes,
--              costs, meeting links, and follow-up tracking
-- Date: 2025-12-02
-- ============================================================================

USE pms_sbsl;

-- Add columns only if they don't exist
SET @dbname = DATABASE();
SET @tablename = 'tija_activities';

-- meetingLink
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'meetingLink');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `meetingLink` VARCHAR(500) NULL',
    'SELECT "Column meetingLink already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- activityNotes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityNotes');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityNotes` TEXT NULL',
    'SELECT "Column activityNotes already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- activityOutcome
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityOutcome');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityOutcome` VARCHAR(100) NULL',
    'SELECT "Column activityOutcome already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- activityResult
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityResult');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityResult` TEXT NULL',
    'SELECT "Column activityResult already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- activityCost
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityCost');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityCost` DECIMAL(15,2) NULL DEFAULT 0.00',
    'SELECT "Column activityCost already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- costCategory
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'costCategory');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `costCategory` VARCHAR(100) NULL',
    'SELECT "Column costCategory already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- costNotes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'costNotes');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `costNotes` TEXT NULL',
    'SELECT "Column costNotes already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- followUpNotes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'followUpNotes');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `followUpNotes` TEXT NULL',
    'SELECT "Column followUpNotes already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- requiresFollowUp
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'requiresFollowUp');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `requiresFollowUp` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column requiresFollowUp already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- sendReminder
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'sendReminder');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `sendReminder` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column sendReminder already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- reminderTime
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'reminderTime');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `reminderTime` INT NULL COMMENT \'Minutes before activity to send reminder\'',
    'SELECT "Column reminderTime already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- allDayEvent
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'allDayEvent');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `allDayEvent` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column allDayEvent already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- duration
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'duration');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `duration` INT NULL COMMENT \'Duration in minutes\'',
    'SELECT "Column duration already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes only if they don't exist
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_outcome');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_outcome ON `tija_activities`(`activityOutcome`)',
    'SELECT "Index idx_activities_outcome already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_status');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_status ON `tija_activities`(`activityStatus`)',
    'SELECT "Index idx_activities_status already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_date');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_date ON `tija_activities`(`activityDate`)',
    'SELECT "Index idx_activities_date already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_owner');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_owner ON `tija_activities`(`activityOwnerID`)',
    'SELECT "Index idx_activities_owner already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_sales');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_sales ON `tija_activities`(`salesCaseID`)',
    'SELECT "Index idx_activities_sales already exists" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing records with default values (only if columns exist)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityCost');
SET @sql = IF(@col_exists > 0,
    'UPDATE `tija_activities` SET `activityCost` = 0.00 WHERE `activityCost` IS NULL',
    'SELECT "Skipping activityCost update" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'requiresFollowUp');
SET @sql = IF(@col_exists > 0,
    'UPDATE `tija_activities` SET `requiresFollowUp` = \'N\' WHERE `requiresFollowUp` IS NULL',
    'SELECT "Skipping requiresFollowUp update" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'sendReminder');
SET @sql = IF(@col_exists > 0,
    'UPDATE `tija_activities` SET `sendReminder` = \'N\' WHERE `sendReminder` IS NULL',
    'SELECT "Skipping sendReminder update" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'allDayEvent');
SET @sql = IF(@col_exists > 0,
    'UPDATE `tija_activities` SET `allDayEvent` = \'N\' WHERE `allDayEvent` IS NULL',
    'SELECT "Skipping allDayEvent update" AS Info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- Create Activity Audit/History Table for tracking changes
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tija_activity_history` (
   `historyID` INT NOT NULL AUTO_INCREMENT,
   `activityID` INT NOT NULL,
   `fieldChanged` VARCHAR(100) NOT NULL,
   `oldValue` TEXT NULL,
   `newValue` TEXT NULL,
   `changedBy` INT NOT NULL,
   `changedOn` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `changeNote` VARCHAR(500) NULL,
   PRIMARY KEY (`historyID`),
   INDEX idx_activity_history (`activityID`),
   INDEX idx_changed_on (`changedOn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Activity Attachments Table
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tija_activity_attachments` (
   `attachmentID` INT NOT NULL AUTO_INCREMENT,
   `activityID` INT NOT NULL,
   `fileName` VARCHAR(255) NOT NULL,
   `filePath` VARCHAR(500) NOT NULL,
   `fileType` VARCHAR(50) NULL,
   `fileSize` INT NULL COMMENT 'Size in bytes',
   `uploadedBy` INT NOT NULL,
   `uploadedOn` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `description` TEXT NULL,
   `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
   PRIMARY KEY (`attachmentID`),
   INDEX idx_activity_attachments (`activityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Activity Reminders Table
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tija_activity_reminders` (
   `reminderID` INT NOT NULL AUTO_INCREMENT,
   `activityID` INT NOT NULL,
   `reminderTime` DATETIME NOT NULL,
   `reminderType` ENUM('email','sms','notification','all') NOT NULL DEFAULT 'notification',
   `recipientID` INT NOT NULL,
   `reminderSent` ENUM('Y','N') NOT NULL DEFAULT 'N',
   `sentOn` DATETIME NULL,
   `reminderNote` TEXT NULL,
   PRIMARY KEY (`reminderID`),
   INDEX idx_activity_reminders (`activityID`),
   INDEX idx_reminder_time (`reminderTime`),
   INDEX idx_reminder_sent (`reminderSent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Add Comments/Notes to track communication
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tija_activity_comments` (
   `commentID` INT NOT NULL AUTO_INCREMENT,
   `activityID` INT NOT NULL,
   `commentText` TEXT NOT NULL,
   `commentBy` INT NOT NULL,
   `commentOn` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `parentCommentID` INT NULL COMMENT 'For threaded comments',
   `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
   PRIMARY KEY (`commentID`),
   INDEX idx_activity_comments (`activityID`),
   INDEX idx_comment_parent (`parentCommentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SUCCESS MESSAGE
-- ============================================================================
SELECT 'Migration completed successfully! Activity wizard fields added.' AS Status;

