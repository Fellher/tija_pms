-- ============================================================================
-- Migrate to Parallel Workflow - MySQL Script for phpMyAdmin
-- ============================================================================
-- This script adds columns and indexes to support parallel/independent
-- approval workflow. Run this script in phpMyAdmin.
--
-- IMPORTANT: Run each section separately and check for errors.
-- If a column/index already exists, you'll get an error - that's OK, skip it.
-- ============================================================================

-- ============================================================================
-- 1. Add approvalType column to tija_leave_approval_policies
-- ============================================================================
-- Check if column exists first (optional - MySQL will error if it exists)
ALTER TABLE `tija_leave_approval_policies`
ADD COLUMN `approvalType` VARCHAR(20) NOT NULL DEFAULT 'parallel'
AFTER `isActive`;

-- ============================================================================
-- 2. Add approvalRequired column to tija_leave_approval_steps
-- ============================================================================
ALTER TABLE `tija_leave_approval_steps`
ADD COLUMN `approvalRequired` VARCHAR(10) NOT NULL DEFAULT 'all'
AFTER `isRequired`;

-- ============================================================================
-- 3. Add stepName column to tija_leave_approval_steps
-- ============================================================================
ALTER TABLE `tija_leave_approval_steps`
ADD COLUMN `stepName` VARCHAR(150) DEFAULT NULL
AFTER `stepOrder`;

-- ============================================================================
-- 4. Add approverUserID column to tija_leave_approval_actions
-- ============================================================================
ALTER TABLE `tija_leave_approval_actions`
ADD COLUMN `approverUserID` INT(11) DEFAULT NULL
AFTER `approverID`;

-- Update existing records to populate approverUserID from approverID
UPDATE `tija_leave_approval_actions`
SET `approverUserID` = `approverID`
WHERE `approverUserID` IS NULL AND `approverID` IS NOT NULL;

-- ============================================================================
-- 5. Add composite index to tija_leave_approval_actions
-- ============================================================================
-- Note: This requires approverUserID column to exist first
ALTER TABLE `tija_leave_approval_actions`
ADD INDEX `idx_actions_instance_step_approver` (`instanceID`, `stepID`, `approverUserID`);

-- ============================================================================
-- 6. Update existing records with default values
-- ============================================================================

-- Update policies to default to parallel
UPDATE `tija_leave_approval_policies`
SET `approvalType` = 'parallel'
WHERE `approvalType` IS NULL OR `approvalType` = '';

-- Update steps to default to 'all'
UPDATE `tija_leave_approval_steps`
SET `approvalRequired` = 'all'
WHERE `approvalRequired` IS NULL OR `approvalRequired` = '';

-- ============================================================================
-- VERIFICATION QUERIES (Run these to verify the migration)
-- ============================================================================
-- Note: These use SHOW statements which don't require INFORMATION_SCHEMA access

-- Check if approvalType column exists in tija_leave_approval_policies
SHOW COLUMNS FROM `tija_leave_approval_policies` LIKE 'approvalType';

-- Check if approvalRequired column exists in tija_leave_approval_steps
SHOW COLUMNS FROM `tija_leave_approval_steps` LIKE 'approvalRequired';

-- Check if stepName column exists in tija_leave_approval_steps
SHOW COLUMNS FROM `tija_leave_approval_steps` LIKE 'stepName';

-- Check if approverUserID column exists in tija_leave_approval_actions
SHOW COLUMNS FROM `tija_leave_approval_actions` LIKE 'approverUserID';

-- Check all indexes on tija_leave_approval_actions (look for idx_actions_instance_step_approver)
SHOW INDEX FROM `tija_leave_approval_actions`;

-- Alternative: View all columns in each table to verify
-- SHOW COLUMNS FROM `tija_leave_approval_policies`;
-- SHOW COLUMNS FROM `tija_leave_approval_steps`;
-- SHOW COLUMNS FROM `tija_leave_approval_actions`;

