-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Operational Tasks Table
-- Purpose: Store operational task instances
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_operational_tasks` (
    `operationalTaskID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `templateID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_task_templates',
    `workflowInstanceID` INT UNSIGNED NULL COMMENT 'FK to tija_workflow_instances - If workflow-enabled',
    `instanceNumber` INT DEFAULT 1 COMMENT 'Cycle number',
    `dueDate` DATE NOT NULL,
    `startDate` DATE NULL,
    `completedDate` DATETIME NULL,
    `status` ENUM('pending','in_progress','completed','overdue','cancelled','blocked') DEFAULT 'pending',
    `assigneeID` INT NOT NULL COMMENT 'FK to people',
    `processID` INT UNSIGNED NULL COMMENT 'FK to tija_bau_processes',
    `actualDuration` DECIMAL(10,2) NULL COMMENT 'Actual hours spent',
    `nextInstanceDueDate` DATE NULL COMMENT 'For regeneration',
    `parentInstanceID` INT UNSIGNED NULL COMMENT 'Links to previous cycle',
    `blockedByTaskID` INT UNSIGNED NULL COMMENT 'Dependency blocker',
    `sopReviewed` ENUM('Y','N') DEFAULT 'N' COMMENT 'SOP review status',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_template` (`templateID`),
    INDEX `idx_workflowInstance` (`workflowInstanceID`),
    INDEX `idx_assignee` (`assigneeID`),
    INDEX `idx_process` (`processID`),
    INDEX `idx_status` (`status`),
    INDEX `idx_dueDate` (`dueDate`),
    INDEX `idx_parentInstance` (`parentInstanceID`),
    FOREIGN KEY (`templateID`) REFERENCES `tija_operational_task_templates`(`templateID`) ON DELETE SET NULL,
    FOREIGN KEY (`workflowInstanceID`) REFERENCES `tija_workflow_instances`(`instanceID`) ON DELETE SET NULL,
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE SET NULL,
    FOREIGN KEY (`parentInstanceID`) REFERENCES `tija_operational_tasks`(`operationalTaskID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Operational task instances';

