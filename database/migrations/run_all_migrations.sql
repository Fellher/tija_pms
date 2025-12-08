-- ============================================================================
-- Activity Wizard - Complete Migration Script
-- Run this entire script in phpMyAdmin or MySQL Workbench
-- ============================================================================
-- Database: pms_sbsl_deploy
-- Date: 2025-12-02
-- ============================================================================

USE pms_sbsl_deploy;

-- ============================================================================
-- MIGRATION 1: Activity Wizard Fields
-- ============================================================================

-- Set up variables for conditional column/index creation
SET @dbname = DATABASE();
SET @tablename = 'tija_activities';

-- Add columns conditionally (meetingLink)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'meetingLink');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `meetingLink` VARCHAR(500) NULL',
    'SELECT "Column meetingLink already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- activityNotes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityNotes');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityNotes` TEXT NULL',
    'SELECT "Column activityNotes already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- activityOutcome
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityOutcome');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityOutcome` VARCHAR(100) NULL',
    'SELECT "Column activityOutcome already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- activityResult
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityResult');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityResult` TEXT NULL',
    'SELECT "Column activityResult already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- activityCost
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityCost');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `activityCost` DECIMAL(15,2) NULL DEFAULT 0.00',
    'SELECT "Column activityCost already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- costCategory
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'costCategory');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `costCategory` VARCHAR(100) NULL',
    'SELECT "Column costCategory already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- costNotes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'costNotes');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `costNotes` TEXT NULL',
    'SELECT "Column costNotes already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- followUpNotes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'followUpNotes');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `followUpNotes` TEXT NULL',
    'SELECT "Column followUpNotes already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- requiresFollowUp
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'requiresFollowUp');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `requiresFollowUp` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column requiresFollowUp already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sendReminder
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'sendReminder');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `sendReminder` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column sendReminder already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- reminderTime
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'reminderTime');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `reminderTime` INT NULL COMMENT \'Minutes before activity to send reminder\'',
    'SELECT "Column reminderTime already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- allDayEvent
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'allDayEvent');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `allDayEvent` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column allDayEvent already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- duration
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'duration');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activities` ADD COLUMN `duration` INT NULL COMMENT \'Duration in minutes\'',
    'SELECT "Column duration already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add indexes conditionally
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_outcome');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_outcome ON `tija_activities`(`activityOutcome`)',
    'SELECT "Index idx_activities_outcome already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_status');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_status ON `tija_activities`(`activityStatus`)',
    'SELECT "Index idx_activities_status already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_date');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_date ON `tija_activities`(`activityDate`)',
    'SELECT "Index idx_activities_date already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_owner');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_owner ON `tija_activities`(`activityOwnerID`)',
    'SELECT "Index idx_activities_owner already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_activities_sales');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_activities_sales ON `tija_activities`(`salesCaseID`)',
    'SELECT "Index idx_activities_sales already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Activity History Table
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

-- Activity Attachments Table
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

-- Activity Reminders Table
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

-- Activity Comments Table
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

SELECT 'Migration 1 completed: Activity Wizard Fields' AS Status;

-- ============================================================================
-- MIGRATION 2: Multi-Expense System
-- ============================================================================

-- Activity Expenses Table
CREATE TABLE IF NOT EXISTS `tija_activity_expenses` (
   `expenseID` INT NOT NULL AUTO_INCREMENT,
   `activityID` INT NOT NULL,
   `expenseDate` DATE NOT NULL,
   `expenseCategory` VARCHAR(100) NOT NULL,
   `expenseAmount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
   `expenseDescription` TEXT NULL,
   `expenseCurrency` VARCHAR(10) NOT NULL DEFAULT 'KES',
   `receiptNumber` VARCHAR(100) NULL,
   `receiptAttached` ENUM('Y','N') NOT NULL DEFAULT 'N',
   `receiptPath` VARCHAR(500) NULL,
   `paymentMethod` VARCHAR(50) NULL COMMENT 'Cash, Card, Mpesa, etc.',
   `reimbursable` ENUM('Y','N') NOT NULL DEFAULT 'Y',
   `reimbursementStatus` ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
   `approvedBy` INT NULL,
   `approvedOn` DATETIME NULL,
   `paidOn` DATETIME NULL,
   `addedBy` INT NOT NULL,
   `addedOn` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   `LastUpdatedByID` INT NOT NULL,
   `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
   PRIMARY KEY (`expenseID`),
   INDEX idx_activity_expenses (`activityID`),
   INDEX idx_expense_date (`expenseDate`),
   INDEX idx_expense_category (`expenseCategory`),
   INDEX idx_reimbursement_status (`reimbursementStatus`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to tija_activity_expenses if they don't exist
SET @tablename3 = 'tija_activity_expenses';

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'expenseCurrency');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `expenseCurrency` VARCHAR(10) NOT NULL DEFAULT \'KES\'',
    'SELECT "Column expenseCurrency already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'receiptNumber');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `receiptNumber` VARCHAR(100) NULL',
    'SELECT "Column receiptNumber already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'receiptAttached');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `receiptAttached` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column receiptAttached already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'receiptPath');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `receiptPath` VARCHAR(500) NULL',
    'SELECT "Column receiptPath already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'paymentMethod');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `paymentMethod` VARCHAR(50) NULL COMMENT \'Cash, Card, Mpesa, etc.\'',
    'SELECT "Column paymentMethod already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'reimbursable');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `reimbursable` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'Y\'',
    'SELECT "Column reimbursable already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'reimbursementStatus');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `reimbursementStatus` ENUM(\'pending\',\'approved\',\'rejected\',\'paid\') NOT NULL DEFAULT \'pending\'',
    'SELECT "Column reimbursementStatus already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'approvedBy');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `approvedBy` INT NULL',
    'SELECT "Column approvedBy already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'approvedOn');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `approvedOn` DATETIME NULL',
    'SELECT "Column approvedOn already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename3 AND COLUMN_NAME = 'paidOn');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_activity_expenses` ADD COLUMN `paidOn` DATETIME NULL',
    'SELECT "Column paidOn already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Expense Categories Table
CREATE TABLE IF NOT EXISTS `tija_expense_categories` (
   `expenseCategoryID` INT NOT NULL AUTO_INCREMENT,
   `categoryName` VARCHAR(100) NOT NULL,
   `categoryDescription` TEXT NULL,
   `categoryIcon` VARCHAR(100) NULL,
   `categoryColor` VARCHAR(20) NULL,
   `requiresReceipt` ENUM('Y','N') NOT NULL DEFAULT 'Y',
   `maxAmount` DECIMAL(15,2) NULL COMMENT 'Maximum allowed per transaction',
   `orgDataID` INT NULL,
   `entityID` INT NULL,
   `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
   PRIMARY KEY (`expenseCategoryID`),
   UNIQUE KEY unique_category (`categoryName`, `orgDataID`, `entityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to tija_expense_categories if they don't exist
SET @tablename2 = 'tija_expense_categories';

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename2 AND COLUMN_NAME = 'categoryIcon');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_expense_categories` ADD COLUMN `categoryIcon` VARCHAR(100) NULL',
    'SELECT "Column categoryIcon already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename2 AND COLUMN_NAME = 'categoryColor');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_expense_categories` ADD COLUMN `categoryColor` VARCHAR(20) NULL',
    'SELECT "Column categoryColor already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename2 AND COLUMN_NAME = 'requiresReceipt');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_expense_categories` ADD COLUMN `requiresReceipt` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'Y\'',
    'SELECT "Column requiresReceipt already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename2 AND COLUMN_NAME = 'maxAmount');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_expense_categories` ADD COLUMN `maxAmount` DECIMAL(15,2) NULL COMMENT \'Maximum allowed per transaction\'',
    'SELECT "Column maxAmount already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Insert default expense categories
INSERT IGNORE INTO `tija_expense_categories` (`categoryName`, `categoryDescription`, `categoryIcon`, `categoryColor`, `requiresReceipt`, `maxAmount`) VALUES
('Travel', 'Transportation and mileage', 'ri-taxi-line', '#007bff', 'Y', NULL),
('Meals', 'Client entertainment and meals', 'ri-restaurant-line', '#28a745', 'Y', 5000.00),
('Materials', 'Sales collateral and materials', 'ri-file-copy-line', '#6c757d', 'Y', NULL),
('Accommodation', 'Hotel and lodging', 'ri-hotel-line', '#17a2b8', 'Y', NULL),
('Technology', 'Software, tools, subscriptions', 'ri-macbook-line', '#6f42c1', 'N', NULL),
('Communication', 'Phone, internet, data', 'ri-phone-line', '#fd7e14', 'Y', 2000.00),
('Parking', 'Parking fees', 'ri-parking-box-line', '#20c997', 'Y', 500.00),
('Fuel', 'Vehicle fuel', 'ri-gas-station-line', '#ffc107', 'Y', NULL),
('Gifts', 'Client gifts and giveaways', 'ri-gift-line', '#e83e8c', 'Y', 10000.00),
('Other', 'Other miscellaneous expenses', 'ri-more-line', '#6c757d', 'N', NULL);

-- Migrate existing data (only if source columns exist)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME IN ('activityCost','costCategory','costNotes'));
SET @sql = IF(@col_exists >= 3,
    'INSERT IGNORE INTO `tija_activity_expenses` (`activityID`, `expenseDate`, `expenseCategory`, `expenseAmount`, `expenseDescription`, `addedBy`, `addedOn`, `LastUpdatedByID`) SELECT `activityID`, `activityDate` as expenseDate, COALESCE(`costCategory`, \'Other\') as expenseCategory, COALESCE(`activityCost`, 0.00) as expenseAmount, `costNotes` as expenseDescription, COALESCE(`activityOwnerID`, 1) as addedBy, `DateAdded` as addedOn, COALESCE(`LastUpdateByID`, 1) as LastUpdatedByID FROM `tija_activities` WHERE `activityCost` > 0 AND `activityCost` IS NOT NULL',
    'SELECT "Skipping data migration - source columns not ready" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Update deprecated field comments (only if columns exist)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'activityCost');
SET @sql = IF(@col_exists > 0,
    'ALTER TABLE `tija_activities` MODIFY COLUMN `activityCost` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT \'Deprecated: Use tija_activity_expenses table\'',
    'SELECT "Skipping activityCost comment update" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'costCategory');
SET @sql = IF(@col_exists > 0,
    'ALTER TABLE `tija_activities` MODIFY COLUMN `costCategory` VARCHAR(100) NULL COMMENT \'Deprecated: Use tija_activity_expenses table\'',
    'SELECT "Skipping costCategory comment update" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'costNotes');
SET @sql = IF(@col_exists > 0,
    'ALTER TABLE `tija_activities` MODIFY COLUMN `costNotes` TEXT NULL COMMENT \'Deprecated: Use tija_activity_expenses table\'',
    'SELECT "Skipping costNotes comment update" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Create view for expense totals
CREATE OR REPLACE VIEW `view_activity_expense_totals` AS
SELECT
   ae.activityID,
   COUNT(ae.expenseID) as expenseCount,
   SUM(ae.expenseAmount) as totalExpenses,
   SUM(CASE WHEN ae.reimbursable = 'Y' THEN ae.expenseAmount ELSE 0 END) as totalReimbursable,
   SUM(CASE WHEN ae.reimbursable = 'N' THEN ae.expenseAmount ELSE 0 END) as totalNonReimbursable,
   SUM(CASE WHEN ae.reimbursementStatus = 'pending' THEN ae.expenseAmount ELSE 0 END) as pendingReimbursement,
   SUM(CASE WHEN ae.reimbursementStatus = 'approved' THEN ae.expenseAmount ELSE 0 END) as approvedReimbursement,
   SUM(CASE WHEN ae.reimbursementStatus = 'paid' THEN ae.expenseAmount ELSE 0 END) as paidReimbursement
FROM `tija_activity_expenses` ae
WHERE ae.Suspended = 'N'
GROUP BY ae.activityID;

SELECT 'Migration 2 completed: Multi-Expense System' AS Status;

-- ============================================================================
-- MIGRATION 3: Sales Documents Enhancement
-- ============================================================================

-- Add stage tracking fields
SET @tablename_doc = 'tija_sales_documents';

-- salesStage
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'salesStage');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `salesStage` VARCHAR(50) NULL COMMENT \'Lead, Opportunity, Proposal, Closed-Won, Closed-Lost\'',
    'SELECT "Column salesStage already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- saleStatusLevelID
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'saleStatusLevelID');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `saleStatusLevelID` INT NULL COMMENT \'FK to tija_sales_status_levels\'',
    'SELECT "Column saleStatusLevelID already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- documentStage
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'documentStage');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `documentStage` ENUM(\'draft\',\'final\',\'revision\',\'approved\',\'signed\') NULL',
    'SELECT "Column documentStage already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sharedWithClient
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'sharedWithClient');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `sharedWithClient` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\'',
    'SELECT "Column sharedWithClient already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sharedDate
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'sharedDate');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `sharedDate` DATETIME NULL',
    'SELECT "Column sharedDate already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tags
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'tags');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `tags` TEXT NULL',
    'SELECT "Column tags already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- expiryDate
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'expiryDate');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `expiryDate` DATE NULL',
    'SELECT "Column expiryDate already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- linkedActivityID
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'linkedActivityID');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `linkedActivityID` INT NULL',
    'SELECT "Column linkedActivityID already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- viewCount
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'viewCount');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `viewCount` INT NOT NULL DEFAULT 0',
    'SELECT "Column viewCount already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- lastAccessedDate
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND COLUMN_NAME = 'lastAccessedDate');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `lastAccessedDate` DATETIME NULL',
    'SELECT "Column lastAccessedDate already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add indexes
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_doc AND INDEX_NAME = 'idx_sales_stage');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_sales_stage ON `tija_sales_documents`(`salesStage`)',
    'SELECT "Index idx_sales_stage already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Document Access Log Table
CREATE TABLE IF NOT EXISTS `tija_sales_document_access_log` (
   `accessID` INT NOT NULL AUTO_INCREMENT,
   `documentID` INT NOT NULL,
   `accessedBy` INT NOT NULL,
   `accessType` ENUM('view','download','share','edit') NOT NULL DEFAULT 'view',
   `accessDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `ipAddress` VARCHAR(45) NULL,
   `userAgent` TEXT NULL,
   PRIMARY KEY (`accessID`),
   INDEX idx_document_access (`documentID`),
   INDEX idx_accessed_by (`accessedBy`),
   INDEX idx_access_date (`accessDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document Versions Table
CREATE TABLE IF NOT EXISTS `tija_sales_document_versions` (
   `versionID` INT NOT NULL AUTO_INCREMENT,
   `documentID` INT NOT NULL,
   `versionNumber` VARCHAR(20) NOT NULL,
   `fileName` VARCHAR(255) NOT NULL,
   `fileURL` VARCHAR(500) NOT NULL,
   `fileSize` BIGINT NULL,
   `versionNotes` TEXT NULL,
   `uploadedBy` INT NOT NULL,
   `uploadedOn` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `isCurrent` ENUM('Y','N') NOT NULL DEFAULT 'N',
   PRIMARY KEY (`versionID`),
   INDEX idx_document_versions (`documentID`),
   INDEX idx_version_current (`isCurrent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document Shares Table
CREATE TABLE IF NOT EXISTS `tija_sales_document_shares` (
   `shareID` INT NOT NULL AUTO_INCREMENT,
   `documentID` INT NOT NULL,
   `sharedWith` VARCHAR(255) NOT NULL,
   `sharedBy` INT NOT NULL,
   `sharedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `shareMethod` ENUM('email','link','portal') NOT NULL DEFAULT 'email',
   `accessLink` VARCHAR(500) NULL,
   `accessExpiry` DATETIME NULL,
   `accessCount` INT NOT NULL DEFAULT 0,
   `lastAccessedDate` DATETIME NULL,
   PRIMARY KEY (`shareID`),
   INDEX idx_document_shares (`documentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update existing documents with current stage
UPDATE `tija_sales_documents` sd
INNER JOIN `tija_sales_cases` sc ON sd.salesCaseID = sc.salesCaseID
SET sd.salesStage = sc.saleStage,
    sd.saleStatusLevelID = sc.saleStatusLevelID
WHERE sd.salesStage IS NULL;

-- Create document summary view
CREATE OR REPLACE VIEW `view_sales_document_summary` AS
SELECT
   sd.documentID,
   sd.salesCaseID,
   sd.documentName,
   sd.documentCategory,
   sd.salesStage,
   sd.saleStatusLevelID,
   sd.documentStage,
   sd.fileType,
   sd.fileSize,
   sd.uploadedBy,
   sd.DateAdded,
   sd.downloadCount,
   sd.viewCount,
   CONCAT(u.FirstName, ' ', u.Surname) as uploadedByName,
   sc.salesCaseName,
   sc.clientName,
   ssl.levelName as statusLevelName,
   CASE
      WHEN sd.expiryDate IS NOT NULL AND sd.expiryDate < CURDATE() THEN 'Expired'
      WHEN sd.approvalStatus = 'pending' THEN 'Pending Approval'
      WHEN sd.approvalStatus = 'approved' THEN 'Approved'
      WHEN sd.approvalStatus = 'rejected' THEN 'Rejected'
      ELSE 'Active'
   END as documentStatus
FROM `tija_sales_documents` sd
LEFT JOIN `people` u ON sd.uploadedBy = u.ID
LEFT JOIN `tija_sales_cases` sc ON sd.salesCaseID = sc.salesCaseID
LEFT JOIN `tija_sales_status_levels` ssl ON sd.saleStatusLevelID = ssl.saleStatusLevelID
WHERE sd.DateAdded IS NOT NULL;

SELECT 'Migration 3 completed: Sales Documents Enhancement' AS Status;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

SELECT '============================================================' AS '';
SELECT 'MIGRATION VERIFICATION' AS '';
SELECT '============================================================' AS '';

-- Count new tables
SELECT
   'New Tables Created' AS Info,
   (SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = 'pms_sbsl_deploy'
    AND TABLE_NAME LIKE 'tija_activity_%') AS Count;

-- Count new fields in tija_activities
SELECT
   'New Fields in tija_activities' AS Info,
   (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'pms_sbsl_deploy'
    AND TABLE_NAME = 'tija_activities'
    AND COLUMN_NAME IN ('meetingLink', 'activityNotes', 'activityOutcome', 'activityResult',
                        'followUpNotes', 'requiresFollowUp', 'sendReminder', 'reminderTime',
                        'allDayEvent', 'duration')) AS Count;

-- Count migrated expenses
SELECT
   'Expenses Migrated' AS Info,
   COUNT(*) AS Count
FROM tija_activity_expenses;

-- Count expense categories
SELECT
   'Expense Categories' AS Info,
   COUNT(*) AS Count
FROM tija_expense_categories;

-- Count sales documents fields
SELECT
   'New Fields in tija_sales_documents' AS Info,
   (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'pms_sbsl_deploy'
    AND TABLE_NAME = 'tija_sales_documents'
    AND COLUMN_NAME IN ('salesStage', 'saleStatusLevelID', 'documentStage',
                        'sharedWithClient', 'sharedDate', 'tags', 'expiryDate',
                        'linkedActivityID', 'viewCount', 'lastAccessedDate')) AS Count;

-- Count document-related tables
SELECT
   'Document Management Tables' AS Info,
   (SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = 'pms_sbsl_deploy'
    AND TABLE_NAME LIKE 'tija%document%') AS Count;

SELECT '============================================================' AS '';
SELECT 'ALL MIGRATIONS COMPLETED SUCCESSFULLY!' AS Status;
SELECT '============================================================' AS '';
SELECT '✅ Activity Wizard with Multi-Expense Tracking' AS Feature1;
SELECT '✅ Sales Documents with Stage Tracking' AS Feature2;
SELECT '✅ Complete Sales Lifecycle Management' AS Feature3;
SELECT '============================================================' AS '';
SELECT 'You can now use all enhanced features!' AS Message;

