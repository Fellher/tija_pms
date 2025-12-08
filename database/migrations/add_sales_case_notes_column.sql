-- ============================================================================
-- Migration: Add salesCaseNotes Column
-- Description: Adds a notes column to tija_sales_cases table for storing
--              general notes and descriptions about sales opportunities
-- Date: 2025-12-02
-- ============================================================================

USE pms_sbsl;

-- Check if column exists before adding
SET @dbname = DATABASE();
SET @tablename = 'tija_sales_cases';
SET @columnname = 'salesCaseNotes';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT ''Column salesCaseNotes already exists'' AS Info',
  'ALTER TABLE `tija_sales_cases` ADD COLUMN `salesCaseNotes` TEXT NULL COMMENT ''General notes and description about the sales opportunity'''
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Success message
SELECT 'Migration completed successfully! salesCaseNotes column added to tija_sales_cases table.' AS Status;

