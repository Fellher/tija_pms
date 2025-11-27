-- ============================================================================
-- Holidays Multi-Jurisdiction Enhancement - Database Migration
-- Date: 2025-11-06
-- Purpose: Add multi-jurisdiction support to holidays system
-- Compatible with MySQL 8.0+
-- ============================================================================

-- Create table if it doesn't exist
CREATE TABLE IF NOT EXISTS `tija_holidays` (
  `holidayID` int(11) NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `holidayName` varchar(255) NOT NULL,
  `holidayDate` date NOT NULL,
  `holidayType` varchar(50) NOT NULL DEFAULT 'full_day' COMMENT 'full_day, half_day',
  `countryID` varchar(10) DEFAULT NULL,
  `repeatsAnnually` char(1) DEFAULT 'N' COMMENT 'Y or N',
  `jurisdictionLevel` varchar(20) DEFAULT 'country' COMMENT 'global, country, region, city, entity',
  `regionID` varchar(100) DEFAULT NULL COMMENT 'Region/State identifier',
  `cityID` varchar(100) DEFAULT NULL COMMENT 'City identifier',
  `entitySpecific` varchar(1000) DEFAULT NULL COMMENT 'Comma-separated entity IDs',
  `applyToEmploymentTypes` varchar(500) DEFAULT 'all' COMMENT 'Comma-separated employment types',
  `excludeBusinessUnits` varchar(1000) DEFAULT NULL COMMENT 'Comma-separated business unit IDs to exclude',
  `affectsLeaveBalance` char(1) DEFAULT 'Y' COMMENT 'Whether holiday affects leave calculations',
  `holidayNotes` text DEFAULT NULL COMMENT 'Additional notes or observance details',
  `CreatedByID` int(11) DEFAULT NULL COMMENT 'User ID who created the holiday',
  `LastUpdateByID` int(11) DEFAULT NULL COMMENT 'User ID who last updated',
  `CreateDate` datetime DEFAULT NULL COMMENT 'Creation timestamp',
  `generatedFrom` int(11) DEFAULT NULL COMMENT 'Source holiday ID if auto-generated',
  `LastUpdate` datetime DEFAULT NULL,
  `Lapsed` char(1) DEFAULT 'N',
  `Suspended` char(1) DEFAULT 'N',
  PRIMARY KEY (`holidayID`),
  KEY `idx_holiday_date` (`holidayDate`),
  KEY `idx_country` (`countryID`),
  KEY `idx_repeats` (`repeatsAnnually`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Add new columns (one at a time for MySQL compatibility)
-- ============================================================================

-- jurisdictionLevel
SET @dbname = DATABASE();
SET @tablename = "tija_holidays";
SET @columnname = "jurisdictionLevel";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN jurisdictionLevel varchar(20) DEFAULT 'country' COMMENT 'global, country, region, city, entity'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- regionID
SET @columnname = "regionID";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN regionID varchar(100) DEFAULT NULL COMMENT 'Region/State identifier'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- cityID
SET @columnname = "cityID";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN cityID varchar(100) DEFAULT NULL COMMENT 'City identifier'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- entitySpecific
SET @columnname = "entitySpecific";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN entitySpecific varchar(1000) DEFAULT NULL COMMENT 'Comma-separated entity IDs'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- applyToEmploymentTypes
SET @columnname = "applyToEmploymentTypes";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN applyToEmploymentTypes varchar(500) DEFAULT 'all' COMMENT 'Comma-separated employment types'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- excludeBusinessUnits
SET @columnname = "excludeBusinessUnits";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN excludeBusinessUnits varchar(1000) DEFAULT NULL COMMENT 'Comma-separated business unit IDs to exclude'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- affectsLeaveBalance
SET @columnname = "affectsLeaveBalance";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN affectsLeaveBalance char(1) DEFAULT 'Y' COMMENT 'Whether holiday affects leave calculations'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- holidayNotes
SET @columnname = "holidayNotes";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN holidayNotes text DEFAULT NULL COMMENT 'Additional notes or observance details'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- CreatedByID
SET @columnname = "CreatedByID";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN CreatedByID int(11) DEFAULT NULL COMMENT 'User ID who created the holiday'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- LastUpdateByID
SET @columnname = "LastUpdateByID";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN LastUpdateByID int(11) DEFAULT NULL COMMENT 'User ID who last updated'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- CreateDate
SET @columnname = "CreateDate";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN CreateDate datetime DEFAULT NULL COMMENT 'Creation timestamp'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- generatedFrom
SET @columnname = "generatedFrom";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD COLUMN generatedFrom int(11) DEFAULT NULL COMMENT 'Source holiday ID if auto-generated'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- Add indexes for new columns (safe approach)
-- ============================================================================

-- Check and add index for jurisdictionLevel
SET @indexname = "idx_jurisdiction";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (index_name = @indexname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD INDEX idx_jurisdiction (jurisdictionLevel)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add index for affectsLeaveBalance
SET @indexname = "idx_affects_balance";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (index_name = @indexname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD INDEX idx_affects_balance (affectsLeaveBalance)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add index for generatedFrom
SET @indexname = "idx_generated_from";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (index_name = @indexname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tija_holidays ADD INDEX idx_generated_from (generatedFrom)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- Holiday Audit Log Table (Optional - for tracking changes)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_holiday_audit_log` (
  `auditID` int(11) NOT NULL AUTO_INCREMENT,
  `holidayID` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'created, updated, deleted, generated',
  `performedByID` int(11) NOT NULL,
  `performedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changeDetails` text DEFAULT NULL COMMENT 'JSON of what changed',
  `ipAddress` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`auditID`),
  KEY `idx_holiday` (`holidayID`),
  KEY `idx_performed_by` (`performedByID`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Update existing holidays to default values
-- ============================================================================

-- Set default jurisdiction level for existing holidays
UPDATE `tija_holidays`
SET `jurisdictionLevel` = 'country'
WHERE `jurisdictionLevel` IS NULL OR `jurisdictionLevel` = '';

-- Set default affects balance for existing holidays
UPDATE `tija_holidays`
SET `affectsLeaveBalance` = 'Y'
WHERE `affectsLeaveBalance` IS NULL OR `affectsLeaveBalance` = '';

-- Set default employment types for existing holidays
UPDATE `tija_holidays`
SET `applyToEmploymentTypes` = 'all'
WHERE `applyToEmploymentTypes` IS NULL OR `applyToEmploymentTypes` = '';

-- ============================================================================
-- Verification Query - Run this to verify migration success
-- ============================================================================

SELECT 'Migration completed successfully! New columns added:' as Status;

SHOW COLUMNS FROM `tija_holidays`;

-- ============================================================================
-- Sample Data: Common Recurring Holidays (Optional - Uncomment to use)
-- ============================================================================

/*
-- Global/International Holidays
INSERT INTO `tija_holidays` (`holidayName`, `holidayDate`, `holidayType`, `countryID`, `repeatsAnnually`, `jurisdictionLevel`, `affectsLeaveBalance`, `applyToEmploymentTypes`, `CreateDate`) VALUES
('New Year\'s Day', '2025-01-01', 'full_day', 'all', 'Y', 'global', 'Y', 'all', NOW()),
('Christmas Day', '2025-12-25', 'full_day', 'all', 'Y', 'global', 'Y', 'all', NOW());

-- Kenya National Holidays (countryID 114 is Kenya)
INSERT INTO `tija_holidays` (`holidayName`, `holidayDate`, `holidayType`, `countryID`, `repeatsAnnually`, `jurisdictionLevel`, `affectsLeaveBalance`, `applyToEmploymentTypes`, `CreateDate`) VALUES
('Madaraka Day', '2025-06-01', 'full_day', '114', 'Y', 'country', 'Y', 'all', NOW()),
('Mashujaa Day', '2025-10-20', 'full_day', '114', 'Y', 'country', 'Y', 'all', NOW()),
('Jamhuri Day', '2025-12-12', 'full_day', '114', 'Y', 'country', 'Y', 'all', NOW()),
('Labour Day', '2025-05-01', 'full_day', '114', 'Y', 'country', 'Y', 'all', NOW()),
('Good Friday', '2025-04-18', 'full_day', '114', 'N', 'country', 'Y', 'all', NOW()),
('Easter Monday', '2025-04-21', 'full_day', '114', 'N', 'country', 'Y', 'all', NOW()),
('Idd-ul-Fitr', '2025-04-10', 'full_day', '114', 'N', 'country', 'Y', 'all', NOW()),
('Idd-ul-Adha', '2025-06-17', 'full_day', '114', 'N', 'country', 'Y', 'all', NOW());
*/

-- ============================================================================
-- Migration Complete - Summary
-- ============================================================================

SELECT
  COUNT(*) as total_holidays,
  SUM(CASE WHEN repeatsAnnually = 'Y' THEN 1 ELSE 0 END) as recurring_holidays,
  SUM(CASE WHEN jurisdictionLevel = 'country' THEN 1 ELSE 0 END) as country_level,
  SUM(CASE WHEN jurisdictionLevel = 'global' THEN 1 ELSE 0 END) as global_level
FROM `tija_holidays`
WHERE Lapsed = 'N';

-- ============================================================================
-- End of Migration
-- ============================================================================
