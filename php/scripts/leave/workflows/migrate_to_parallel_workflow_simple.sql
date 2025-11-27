-- ============================================================================
-- Migrate to Parallel Workflow - Simple MySQL Script for phpMyAdmin
-- ============================================================================
-- This version uses simple ALTER TABLE statements.
--
-- IMPORTANT: If you get "Duplicate column name" or "Duplicate key name" error,
-- that means the column/index already exists - this is OK! Just skip that step
-- and continue with the next one.
--
-- Run each section separately in phpMyAdmin.
-- ============================================================================

-- ============================================================================
-- STEP 1: Add approvalType column to tija_leave_approval_policies
-- ============================================================================


-- ============================================================================
-- STEP 2: Add approvalRequired column to tija_leave_approval_steps
-- ============================================================================





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

