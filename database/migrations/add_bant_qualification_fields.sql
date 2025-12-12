-- Migration: Add BANT Qualification Fields
-- Date: 2025-12-11
-- Description: Adds Budget, Authority, Need, Timeline tracking fields to prospects and decision maker flag to client contacts

-- Add BANT fields to prospects table (only if they don't exist)
SET @dbname = DATABASE();
SET @tablename = 'tija_sales_prospects';

-- Check and add confirmedBudget
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'confirmedBudget';
SET @query = IF(@col_exists = 0,
    'ALTER TABLE tija_sales_prospects ADD COLUMN confirmedBudget DECIMAL(15,2) NULL COMMENT "Confirmed budget amount"',
    'SELECT "Column confirmedBudget already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add budgetConfirmedDate
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'budgetConfirmedDate';
SET @query = IF(@col_exists = 0,
    'ALTER TABLE tija_sales_prospects ADD COLUMN budgetConfirmedDate DATE NULL COMMENT "Date budget was confirmed"',
    'SELECT "Column budgetConfirmedDate already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add identifiedNeed
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'identifiedNeed';
SET @query = IF(@col_exists = 0,
    'ALTER TABLE tija_sales_prospects ADD COLUMN identifiedNeed TEXT NULL COMMENT "Description of identified need"',
    'SELECT "Column identifiedNeed already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add needIdentifiedDate
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'needIdentifiedDate';
SET @query = IF(@col_exists = 0,
    'ALTER TABLE tija_sales_prospects ADD COLUMN needIdentifiedDate DATE NULL COMMENT "Date need was identified"',
    'SELECT "Column needIdentifiedDate already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add expectedTimeline
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'expectedTimeline';
SET @query = IF(@col_exists = 0,
    'ALTER TABLE tija_sales_prospects ADD COLUMN expectedTimeline DATE NULL COMMENT "Expected decision/purchase timeline"',
    'SELECT "Column expectedTimeline already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add timelineDefinedDate
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'timelineDefinedDate';
SET @query = IF(@col_exists = 0,
    'ALTER TABLE tija_sales_prospects ADD COLUMN timelineDefinedDate DATE NULL COMMENT "Date timeline was defined"',
    'SELECT "Column timelineDefinedDate already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add decision maker flag to client contacts table (only if it doesn't exist)
SET @tablename = 'tija_client_contacts';

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'isDecisionMaker';
SET @query = IF(@col_exists = 0,
    'ALTER TABLE tija_client_contacts ADD COLUMN isDecisionMaker ENUM("Y", "N") DEFAULT "N" COMMENT "Indicates if contact is a decision maker"',
    'SELECT "Column isDecisionMaker already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for decision maker (only if it doesn't exist)
SET @index_exists = 0;
SELECT COUNT(*) INTO @index_exists FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = 'idx_decision_maker';
SET @query = IF(@index_exists = 0,
    'ALTER TABLE tija_client_contacts ADD INDEX idx_decision_maker (isDecisionMaker)',
    'SELECT "Index idx_decision_maker already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the changes
SELECT 'BANT fields migration completed successfully' AS status;

SELECT
    'Prospects BANT fields' as table_name,
    COUNT(*) as prospect_count
FROM tija_sales_prospects;

SELECT
    'Client contacts decision maker field' as table_name,
    COUNT(*) as contact_count
FROM tija_client_contacts;
