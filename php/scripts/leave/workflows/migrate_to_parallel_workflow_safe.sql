-- ============================================================================
-- Migrate to Parallel Workflow - Safe MySQL Script (with IF NOT EXISTS checks)
-- ============================================================================
-- This version uses stored procedures to safely check before adding columns.
-- Run this entire script in phpMyAdmin.
-- ============================================================================

DELIMITER $$

-- ============================================================================
-- Procedure to safely add column if it doesn't exist
-- ============================================================================
DROP PROCEDURE IF EXISTS AddColumnIfNotExists$$
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(128),
    IN columnName VARCHAR(128),
    IN columnDefinition TEXT
)
BEGIN
    DECLARE columnExists INT DEFAULT 0;
    DECLARE dbName VARCHAR(128);

    -- Get current database name
    SELECT DATABASE() INTO dbName;

    -- Check if column exists using INFORMATION_SCHEMA (accessible from stored procedure)
    SELECT COUNT(*) INTO columnExists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = dbName
    AND TABLE_NAME = tableName
    AND COLUMN_NAME = columnName;

    IF columnExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', tableName, '` ADD COLUMN `', columnName, '` ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('✅ Added column ', columnName, ' to ', tableName) AS result;
    ELSE
        SELECT CONCAT('ℹ️ Column ', columnName, ' already exists in ', tableName) AS result;
    END IF;
END$$

-- ============================================================================
-- Procedure to safely add index if it doesn't exist
-- ============================================================================
DROP PROCEDURE IF EXISTS AddIndexIfNotExists$$
CREATE PROCEDURE AddIndexIfNotExists(
    IN tableName VARCHAR(128),
    IN indexName VARCHAR(128),
    IN indexDefinition TEXT
)
BEGIN
    DECLARE indexExists INT DEFAULT 0;
    DECLARE dbName VARCHAR(128);

    -- Get current database name
    SELECT DATABASE() INTO dbName;

    -- Check if index exists using INFORMATION_SCHEMA (accessible from stored procedure)
    SELECT COUNT(*) INTO indexExists
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = dbName
    AND TABLE_NAME = tableName
    AND INDEX_NAME = indexName;

    IF indexExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', tableName, '` ADD INDEX `', indexName, '` ', indexDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('✅ Added index ', indexName, ' to ', tableName) AS result;
    ELSE
        SELECT CONCAT('ℹ️ Index ', indexName, ' already exists on ', tableName) AS result;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- 1. Add approvalType column to tija_leave_approval_policies
-- ============================================================================
CALL AddColumnIfNotExists(
    'tija_leave_approval_policies',
    'approvalType',
    'VARCHAR(20) NOT NULL DEFAULT \'parallel\' AFTER `isActive`'
);

-- ============================================================================
-- 2. Add approvalRequired column to tija_leave_approval_steps
-- ============================================================================
CALL AddColumnIfNotExists(
    'tija_leave_approval_steps',
    'approvalRequired',
    'VARCHAR(10) NOT NULL DEFAULT \'all\' AFTER `isRequired`'
);

-- ============================================================================
-- 3. Add stepName column to tija_leave_approval_steps
-- ============================================================================
CALL AddColumnIfNotExists(
    'tija_leave_approval_steps',
    'stepName',
    'VARCHAR(150) DEFAULT NULL AFTER `stepOrder`'
);

-- ============================================================================
-- 4. Add approverUserID column to tija_leave_approval_actions
-- ============================================================================
CALL AddColumnIfNotExists(
    'tija_leave_approval_actions',
    'approverUserID',
    'INT(11) DEFAULT NULL AFTER `approverID`'
);

-- Update existing records to populate approverUserID from approverID
UPDATE `tija_leave_approval_actions`
SET `approverUserID` = `approverID`
WHERE `approverUserID` IS NULL AND `approverID` IS NOT NULL;

-- ============================================================================
-- 5. Add composite index to tija_leave_approval_actions
-- ============================================================================
CALL AddIndexIfNotExists(
    'tija_leave_approval_actions',
    'idx_actions_instance_step_approver',
    '(`instanceID`, `stepID`, `approverUserID`)'
);

-- ============================================================================
-- 6. Update existing records with default values
-- ============================================================================

-- Update policies to default to parallel
-- Note: This will only work if the column was successfully added above
UPDATE `tija_leave_approval_policies`
SET `approvalType` = 'parallel'
WHERE `approvalType` IS NULL OR `approvalType` = '';

-- Update steps to default to 'all'
-- Note: This will only work if the column was successfully added above
UPDATE `tija_leave_approval_steps`
SET `approvalRequired` = 'all'
WHERE `approvalRequired` IS NULL OR `approvalRequired` = '';

-- ============================================================================
-- Cleanup: Drop temporary procedures
-- ============================================================================
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
DROP PROCEDURE IF EXISTS AddIndexIfNotExists;

-- ============================================================================
-- VERIFICATION QUERIES (Using SHOW statements - no INFORMATION_SCHEMA needed)
-- ============================================================================

-- Check if approvalType column exists
SHOW COLUMNS FROM `tija_leave_approval_policies` LIKE 'approvalType';

-- Check if approvalRequired column exists
SHOW COLUMNS FROM `tija_leave_approval_steps` LIKE 'approvalRequired';

-- Check if stepName column exists
SHOW COLUMNS FROM `tija_leave_approval_steps` LIKE 'stepName';

-- Check if approverUserID column exists
SHOW COLUMNS FROM `tija_leave_approval_actions` LIKE 'approverUserID';

-- Check all indexes on tija_leave_approval_actions (look for idx_actions_instance_step_approver)
SHOW INDEX FROM `tija_leave_approval_actions`;

-- View all columns in each table (optional - for full verification)
-- SHOW COLUMNS FROM `tija_leave_approval_policies`;
-- SHOW COLUMNS FROM `tija_leave_approval_steps`;
-- SHOW COLUMNS FROM `tija_leave_approval_actions`;

