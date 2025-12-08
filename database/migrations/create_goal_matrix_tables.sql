-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Goal Matrix Tables
-- Purpose: Support matrix organization and cross-border goal assignments
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_goal_matrix_assignments
-- Cross-border and matrix goal assignments
CREATE TABLE IF NOT EXISTS `tija_goal_matrix_assignments` (
    `assignmentID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID',
    `employeeUserID` INT UNSIGNED NOT NULL COMMENT 'FK to people.ID - employee receiving goal',
    `matrixManagerID` INT UNSIGNED NOT NULL COMMENT 'FK to people.ID - functional/matrix manager',
    `administrativeManagerID` INT UNSIGNED NULL COMMENT 'FK to people.ID - legal entity manager',
    `assignmentType` ENUM('Functional', 'Project', 'Matrix', 'Temporary') NOT NULL DEFAULT 'Matrix' COMMENT 'Type of assignment',
    `allocationPercent` DECIMAL(5,2) DEFAULT 100.00 COMMENT 'Percentage allocation if partial (0.00-100.00)',
    `projectID` INT UNSIGNED NULL COMMENT 'FK to tija_projects.projectID if project-based',
    `startDate` DATE NOT NULL COMMENT 'Assignment start date',
    `endDate` DATE NULL COMMENT 'Assignment end date (NULL = ongoing)',
    `status` ENUM('Active', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Active',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL,
    INDEX `idx_goalUUID` (`goalUUID`),
    INDEX `idx_employee` (`employeeUserID`),
    INDEX `idx_matrixManager` (`matrixManagerID`),
    INDEX `idx_adminManager` (`administrativeManagerID`),
    INDEX `idx_assignmentType` (`assignmentType`),
    INDEX `idx_projectID` (`projectID`),
    INDEX `idx_status` (`status`),
    INDEX `idx_dates` (`startDate`, `endDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Matrix Assignments - Cross-border and matrix goal assignments';

-- Add foreign key constraints after table creation
-- Note: These will only work if the referenced tables exist
-- FK to goals (should exist if goals tables are created first)
-- ALTER TABLE `tija_goal_matrix_assignments`
--     ADD CONSTRAINT `fk_matrix_goal`
--     FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE CASCADE;

-- FK to people (if people table exists)
-- ALTER TABLE `tija_goal_matrix_assignments`
--     ADD CONSTRAINT `fk_matrix_employee`
--     FOREIGN KEY (`employeeUserID`) REFERENCES `people`(`ID`) ON DELETE RESTRICT;

-- ALTER TABLE `tija_goal_matrix_assignments`
--     ADD CONSTRAINT `fk_matrix_manager`
--     FOREIGN KEY (`matrixManagerID`) REFERENCES `people`(`ID`) ON DELETE RESTRICT;

-- ALTER TABLE `tija_goal_matrix_assignments`
--     ADD CONSTRAINT `fk_matrix_adminManager`
--     FOREIGN KEY (`administrativeManagerID`) REFERENCES `people`(`ID`) ON DELETE SET NULL;

-- ALTER TABLE `tija_goal_matrix_assignments`
--     ADD CONSTRAINT `fk_matrix_lastUpdated`
--     FOREIGN KEY (`LastUpdatedByID`) REFERENCES `people`(`ID`) ON DELETE SET NULL;

-- FK to projects (if tija_projects exists)
-- ALTER TABLE `tija_goal_matrix_assignments`
--     ADD CONSTRAINT `fk_matrix_project`
--     FOREIGN KEY (`projectID`) REFERENCES `tija_projects`(`projectID`) ON DELETE SET NULL;

-- Note: CHECK constraints removed for compatibility with MySQL < 8.0.16
-- For data validation, consider:
-- 1. Application-level validation
-- 2. Triggers (BEFORE INSERT/UPDATE)
-- 3. Or upgrade to MySQL 8.0.16+ and add:
--    CHECK (`allocationPercent` >= 0.00 AND `allocationPercent` <= 100.00),
--    CHECK (`endDate` IS NULL OR `endDate` >= `startDate`)

-- Table: tija_goal_cascade_log
-- Cascade audit trail and workflow tracking
CREATE TABLE IF NOT EXISTS `tija_goal_cascade_log` (
    `logID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `parentGoalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID - parent goal',
    `childGoalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID - child goal created',
    `cascadeMode` ENUM('Strict', 'Aligned', 'Hybrid') NOT NULL COMMENT 'Mode used for cascade',
    `targetEntityID` INT UNSIGNED NULL COMMENT 'FK to tija_entities.entityID - where cascaded to',
    `targetUserID` INT UNSIGNED NULL COMMENT 'FK to people.ID - individual target if applicable',
    `cascadeDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When cascade was executed',
    `cascadedByUserID` INT UNSIGNED NOT NULL COMMENT 'FK to people.ID - who executed cascade',
    `status` ENUM('Pending', 'Accepted', 'Rejected', 'Modified', 'AutoCreated') NOT NULL DEFAULT 'Pending' COMMENT 'Cascade status',
    `modificationNotes` TEXT NULL COMMENT 'Notes if status is Modified',
    `responseDate` DATETIME NULL COMMENT 'When target responded (accepted/rejected)',
    `respondedByUserID` INT UNSIGNED NULL COMMENT 'FK to people.ID - who responded',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_parentGoal` (`parentGoalUUID`),
    INDEX `idx_childGoal` (`childGoalUUID`),
    INDEX `idx_cascadeMode` (`cascadeMode`),
    INDEX `idx_targetEntity` (`targetEntityID`),
    INDEX `idx_targetUser` (`targetUserID`),
    INDEX `idx_status` (`status`),
    INDEX `idx_cascadeDate` (`cascadeDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Cascade Log - Audit trail for goal cascading operations';

-- Add foreign key constraints after table creation
-- Note: These will only work if the referenced tables exist
-- FK to goals (should exist if goals tables are created first)
-- ALTER TABLE `tija_goal_cascade_log`
--     ADD CONSTRAINT `fk_cascade_parentGoal`
--     FOREIGN KEY (`parentGoalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE RESTRICT;

-- ALTER TABLE `tija_goal_cascade_log`
--     ADD CONSTRAINT `fk_cascade_childGoal`
--     FOREIGN KEY (`childGoalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE RESTRICT;

-- FK to entities (if tija_entities exists)
-- ALTER TABLE `tija_goal_cascade_log`
--     ADD CONSTRAINT `fk_cascade_targetEntity`
--     FOREIGN KEY (`targetEntityID`) REFERENCES `tija_entities`(`entityID`) ON DELETE SET NULL;

-- FK to people (if people table exists)
-- ALTER TABLE `tija_goal_cascade_log`
--     ADD CONSTRAINT `fk_cascade_targetUser`
--     FOREIGN KEY (`targetUserID`) REFERENCES `people`(`ID`) ON DELETE SET NULL;

-- ALTER TABLE `tija_goal_cascade_log`
--     ADD CONSTRAINT `fk_cascade_cascadedBy`
--     FOREIGN KEY (`cascadedByUserID`) REFERENCES `people`(`ID`) ON DELETE RESTRICT;

-- ALTER TABLE `tija_goal_cascade_log`
--     ADD CONSTRAINT `fk_cascade_respondedBy`
--     FOREIGN KEY (`respondedByUserID`) REFERENCES `people`(`ID`) ON DELETE SET NULL;

