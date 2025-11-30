-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Extend Time Logs Table for BAU
-- Purpose: Add BAU-specific fields to existing time logs table
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Add operational task fields to tija_tasks_time_logs
ALTER TABLE `tija_tasks_time_logs`
    ADD COLUMN `operationalTaskID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_tasks' AFTER `billingCycleID`,
    ADD COLUMN `operationalProjectID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_projects' AFTER `operationalTaskID`,
    ADD COLUMN `processID` VARCHAR(20) NULL COMMENT 'APQC process identifier' AFTER `operationalProjectID`,
    ADD COLUMN `workflowStepID` INT UNSIGNED NULL COMMENT 'FK to tija_workflow_steps - If part of workflow' AFTER `processID`;

-- Extend taskType enum to include 'operational'
-- Note: MySQL doesn't support direct enum modification, so we need to use ALTER with MODIFY
ALTER TABLE `tija_tasks_time_logs`
    MODIFY COLUMN `taskType` ENUM('adhoc','project','sales','activity','proposal','operational') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'project';

-- Add indexes for performance
ALTER TABLE `tija_tasks_time_logs`
    ADD INDEX `idx_operationalTask` (`operationalTaskID`),
    ADD INDEX `idx_operationalProject` (`operationalProjectID`),
    ADD INDEX `idx_processID` (`processID`),
    ADD INDEX `idx_workflowStep` (`workflowStepID`);

