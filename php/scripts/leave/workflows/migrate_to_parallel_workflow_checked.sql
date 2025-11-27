-- ============================================================================
-- Migrate to Parallel Workflow - Checked MySQL Script for phpMyAdmin
-- ============================================================================
-- This version checks if columns exist before adding them.
-- Run this entire script in phpMyAdmin - it will skip existing columns.
-- ============================================================================

-- ============================================================================
-- STEP 1: Add approvalType column (if it doesn't exist)
-- ============================================================================
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tija_leave_approval_policies'
AND COLUMN_NAME = 'approvalType';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_leave_approval_policies` ADD COLUMN `approvalType` VARCHAR(20) NOT NULL DEFAULT ''parallel'' AFTER `isActive`',
    'SELECT ''Column approvalType already exists in tija_leave_approval_policies'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 2: Add approvalRequired column (if it doesn't exist)
-- ============================================================================
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tija_leave_approval_steps'
AND COLUMN_NAME = 'approvalRequired';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_leave_approval_steps` ADD COLUMN `approvalRequired` VARCHAR(10) NOT NULL DEFAULT ''all'' AFTER `isRequired`',
    'SELECT ''Column approvalRequired already exists in tija_leave_approval_steps'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 3: Add stepName column (if it doesn't exist)
-- ============================================================================
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tija_leave_approval_steps'
AND COLUMN_NAME = 'stepName';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_leave_approval_steps` ADD COLUMN `stepName` VARCHAR(150) DEFAULT NULL AFTER `stepOrder`',
    'SELECT ''Column stepName already exists in tija_leave_approval_steps'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 4: Add approverUserID column (if it doesn't exist)
-- ============================================================================
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tija_leave_approval_actions'
AND COLUMN_NAME = 'approverUserID';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `tija_leave_approval_actions` ADD COLUMN `approverUserID` INT(11) DEFAULT NULL AFTER `approverID`',
    'SELECT ''Column approverUserID already exists in tija_leave_approval_actions'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing records to populate approverUserID from approverID
UPDATE `tija_leave_approval_actions`
SET `approverUserID` = `approverID`
WHERE `approverUserID` IS NULL AND `approverID` IS NOT NULL;

-- ============================================================================
-- STEP 5: Add composite index (if it doesn't exist)
-- ============================================================================
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tija_leave_approval_actions'
AND INDEX_NAME = 'idx_actions_instance_step_approver';

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `tija_leave_approval_actions` ADD INDEX `idx_actions_instance_step_approver` (`instanceID`, `stepID`, `approverUserID`)',
    'SELECT ''Index idx_actions_instance_step_approver already exists on tija_leave_approval_actions'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 6: Update existing records with default values
-- ============================================================================

-- Update policies to default to parallel (only if column exists)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tija_leave_approval_policies'
AND COLUMN_NAME = 'approvalType';

SET @sql = IF(@col_exists > 0,
    'UPDATE `tija_leave_approval_policies` SET `approvalType` = ''parallel'' WHERE `approvalType` IS NULL OR `approvalType` = ''''',
    'SELECT ''Skipping update - approvalType column does not exist'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update steps to default to 'all' (only if column exists)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tija_leave_approval_steps'
AND COLUMN_NAME = 'approvalRequired';

SET @sql = IF(@col_exists > 0,
    'UPDATE `tija_leave_approval_steps` SET `approvalRequired` = ''all'' WHERE `approvalRequired` IS NULL OR `approvalRequired` = ''''',
    'SELECT ''Skipping update - approvalRequired column does not exist'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- VERIFICATION (Run these to check if migration was successful)
-- ============================================================================

-- Check columns in tija_leave_approval_policies (look for approvalType)
SHOW COLUMNS FROM `tija_leave_approval_policies`;

-- Check columns in tija_leave_approval_steps (look for approvalRequired and stepName)
SHOW COLUMNS FROM `tija_leave_approval_steps`;

-- Check columns in tija_leave_approval_actions (look for approverUserID)
SHOW COLUMNS FROM `tija_leave_approval_actions`;

-- Check indexes on tija_leave_approval_actions (look for idx_actions_instance_step_approver)
SHOW INDEX FROM `tija_leave_approval_actions`;

