-- ============================================================================
-- Migration: Enhance Sales Documents with Stage Tracking
-- Description: Adds sales stage/status tracking and additional metadata
-- Date: 2025-12-02
-- ============================================================================

USE pms_sbsl_deploy;

-- ============================================================================
-- Add Stage Tracking Fields to Sales Documents
-- ============================================================================

SET @dbname = DATABASE();
SET @tablename = 'tija_sales_documents';

-- salesStage - Track which stage document was added
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'salesStage');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `salesStage` VARCHAR(50) NULL COMMENT \'Lead, Opportunity, Proposal, Closed-Won, Closed-Lost\' AFTER `documentCategory`',
    'SELECT "Column salesStage already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- saleStatusLevelID - Link to specific status level
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'saleStatusLevelID');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `saleStatusLevelID` INT NULL COMMENT \'FK to tija_sales_status_levels\' AFTER `salesStage`',
    'SELECT "Column saleStatusLevelID already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- documentStage - More detailed stage tracking
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'documentStage');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `documentStage` ENUM(\'draft\',\'final\',\'revision\',\'approved\',\'signed\') NULL COMMENT \'Document maturity stage\' AFTER `saleStatusLevelID`',
    'SELECT "Column documentStage already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sharedWithClient - Flag if shared with client
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'sharedWithClient');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `sharedWithClient` ENUM(\'Y\',\'N\') NOT NULL DEFAULT \'N\' AFTER `isPublic`',
    'SELECT "Column sharedWithClient already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sharedDate - When shared with client
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'sharedDate');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `sharedDate` DATETIME NULL AFTER `sharedWithClient`',
    'SELECT "Column sharedDate already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tags - Document tags for easy searching
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'tags');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `tags` TEXT NULL COMMENT \'Comma-separated tags\' AFTER `description`',
    'SELECT "Column tags already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- expiryDate - Document expiry (for proposals, quotes, etc.)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'expiryDate');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `expiryDate` DATE NULL COMMENT \'For time-sensitive documents\' AFTER `approvedDate`',
    'SELECT "Column expiryDate already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- linkedActivityID - Link to activity if document created during activity
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'linkedActivityID');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `linkedActivityID` INT NULL COMMENT \'FK to tija_activities\' AFTER `expenseID`',
    'SELECT "Column linkedActivityID already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- viewCount - Track document views
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'viewCount');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `viewCount` INT NOT NULL DEFAULT 0 COMMENT \'Number of times viewed\' AFTER `downloadCount`',
    'SELECT "Column viewCount already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- lastAccessedDate - Last time document was accessed
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'lastAccessedDate');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_sales_documents` ADD COLUMN `lastAccessedDate` DATETIME NULL AFTER `viewCount`',
    'SELECT "Column lastAccessedDate already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add indexes for better performance
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_sales_stage');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_sales_stage ON `tija_sales_documents`(`salesStage`)',
    'SELECT "Index idx_sales_stage already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_document_category');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_document_category ON `tija_sales_documents`(`documentCategory`)',
    'SELECT "Index idx_document_category already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_uploaded_by');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_uploaded_by ON `tija_sales_documents`(`uploadedBy`)',
    'SELECT "Index idx_uploaded_by already exists" AS Info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- Create Document Access Log Table
-- ============================================================================
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

-- ============================================================================
-- Create Document Versions Table
-- ============================================================================
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

-- ============================================================================
-- Create Document Sharing Table (Track who document was shared with)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tija_sales_document_shares` (
   `shareID` INT NOT NULL AUTO_INCREMENT,
   `documentID` INT NOT NULL,
   `sharedWith` VARCHAR(255) NOT NULL COMMENT 'Email or user ID',
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

-- ============================================================================
-- Update existing documents with current sales stage
-- ============================================================================
UPDATE `tija_sales_documents` sd
INNER JOIN `tija_sales_cases` sc ON sd.salesCaseID = sc.salesCaseID
SET sd.salesStage = sc.saleStage,
    sd.saleStatusLevelID = sc.saleStatusLevelID
WHERE sd.salesStage IS NULL;

-- ============================================================================
-- Create View for Document Summary
-- ============================================================================
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

-- ============================================================================
-- SUCCESS MESSAGE
-- ============================================================================
SELECT 'Sales Documents Enhancement Migration Completed!' AS Status;
SELECT COUNT(*) AS 'Total Documents' FROM tija_sales_documents;
SELECT COUNT(DISTINCT salesStage) AS 'Unique Stages' FROM tija_sales_documents WHERE salesStage IS NOT NULL;


