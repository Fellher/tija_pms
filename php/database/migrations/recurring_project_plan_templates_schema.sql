-- ============================================================================
-- Recurring Project Plan Templates Database Schema Migration
-- ============================================================================
-- This migration creates tables to store plan templates for recurring projects
-- and manage plan replication across billing cycles
--
-- IMPORTANT: Backup your database before running this script!
-- Run Date: 2025-11-XX
-- ============================================================================

-- ============================================================================
-- SECTION 1: Create tija_recurring_project_plan_templates Table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_templates` (
    `templatePhaseID` INT(11) NOT NULL AUTO_INCREMENT,
    `projectID` INT(11) NOT NULL COMMENT 'FK to tija_projects',
    `originalPhaseID` INT(11) NULL COMMENT 'FK to original phase in tija_project_phases',
    `phaseName` VARCHAR(200) NOT NULL,
    `phaseDescription` TEXT NULL,
    `phaseOrder` INT(11) NOT NULL DEFAULT 0 COMMENT 'Order of phase in template',
    `phaseDuration` INT(11) NULL COMMENT 'Duration in days',
    `phaseWorkHrs` DECIMAL(10,2) NULL,
    `phaseWeighting` DECIMAL(10,2) NULL,
    `billingMilestone` ENUM('Y','N') DEFAULT 'N',
    `relativeStartDay` INT(11) DEFAULT 0 COMMENT 'Days from cycle start (0 = start of cycle)',
    `relativeEndDay` INT(11) DEFAULT 0 COMMENT 'Days from cycle start',
    `applyToAllCycles` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Apply to all cycles or specific cycles',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    PRIMARY KEY (`templatePhaseID`),
    INDEX `idx_project` (`projectID`),
    INDEX `idx_original_phase` (`originalPhaseID`),
    INDEX `idx_order` (`projectID`, `phaseOrder`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores phase templates for recurring projects';

-- ============================================================================
-- SECTION 2: Create tija_recurring_project_plan_task_templates Table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_task_templates` (
    `templateTaskID` INT(11) NOT NULL AUTO_INCREMENT,
    `templatePhaseID` INT(11) NOT NULL COMMENT 'FK to tija_recurring_project_plan_templates',
    `originalTaskID` INT(11) NULL COMMENT 'FK to original task in tija_project_tasks',
    `taskName` VARCHAR(256) NOT NULL,
    `taskCode` VARCHAR(30) NOT NULL,
    `taskDescription` TEXT NULL,
    `relativeStartDay` INT(11) DEFAULT 0 COMMENT 'Days from phase start',
    `relativeEndDay` INT(11) DEFAULT 0 COMMENT 'Days from phase start',
    `hoursAllocated` DECIMAL(10,2) NULL,
    `taskWeighting` DECIMAL(10,2) NULL,
    `assigneeID` INT(11) NULL COMMENT 'FK to people table',
    `applyToAllCycles` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Apply to all cycles or specific cycles',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    PRIMARY KEY (`templateTaskID`),
    INDEX `idx_template_phase` (`templatePhaseID`),
    INDEX `idx_original_task` (`originalTaskID`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores task templates for recurring project phases';

-- ============================================================================
-- SECTION 3: Create tija_recurring_project_plan_cycle_config Table
-- ============================================================================
-- This table allows configuration of which phases/tasks apply to which cycles

CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_cycle_config` (
    `configID` INT(11) NOT NULL AUTO_INCREMENT,
    `projectID` INT(11) NOT NULL COMMENT 'FK to tija_projects',
    `billingCycleID` INT(11) NOT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
    `templatePhaseID` INT(11) NULL COMMENT 'FK to tija_recurring_project_plan_templates (if phase-specific)',
    `templateTaskID` INT(11) NULL COMMENT 'FK to tija_recurring_project_plan_task_templates (if task-specific)',
    `isEnabled` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Enable/disable this phase/task for this cycle',
    `customStartDate` DATE NULL COMMENT 'Override start date for this cycle',
    `customEndDate` DATE NULL COMMENT 'Override end date for this cycle',
    `customDuration` INT(11) NULL COMMENT 'Override duration in days',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    PRIMARY KEY (`configID`),
    INDEX `idx_project_cycle` (`projectID`, `billingCycleID`),
    INDEX `idx_template_phase` (`templatePhaseID`),
    INDEX `idx_template_task` (`templateTaskID`),
    INDEX `idx_suspended` (`Suspended`),
    UNIQUE KEY `idx_unique_phase_cycle` (`templatePhaseID`, `billingCycleID`),
    UNIQUE KEY `idx_unique_task_cycle` (`templateTaskID`, `billingCycleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuration for cycle-specific plan customization';

-- ============================================================================
-- SECTION 4: Add billingCycleID to tija_project_phases Table
-- ============================================================================

-- Check if column exists before adding
SET @dbname = DATABASE();
SET @tablename = 'tija_project_phases';
SET @columnname = 'billingCycleID';

SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` INT(11) NULL COMMENT ''FK to tija_recurring_project_billing_cycles'' AFTER `projectID`')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for billingCycleID
SET @indexname = 'idx_billing_cycle';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND INDEX_NAME = @indexname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`billingCycleID`)')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- SECTION 5: Add billingCycleID to tija_project_tasks Table
-- ============================================================================

SET @tablename = 'tija_project_tasks';
SET @columnname = 'billingCycleID';

SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` INT(11) NULL COMMENT ''FK to tija_recurring_project_billing_cycles'' AFTER `projectPhaseID`')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for billingCycleID
SET @indexname = 'idx_billing_cycle';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND INDEX_NAME = @indexname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`billingCycleID`)')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- Migration Complete
-- ============================================================================
--
-- IMPORTANT: After running this migration successfully, verify that:
-- 1. All tables were created correctly
-- 2. The billingCycleID columns were added to tija_project_phases and tija_project_tasks
-- 3. Indexes were created successfully
-- ============================================================================

