-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Operational Task Checklists Table
-- Purpose: Store checklist items for operational tasks
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_operational_task_checklists` (
    `checklistItemID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `templateID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_task_templates - Template-level',
    `operationalTaskID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_tasks - Instance-level',
    `itemOrder` INT NOT NULL,
    `itemDescription` TEXT NOT NULL,
    `isMandatory` ENUM('Y','N') DEFAULT 'N',
    `isCompleted` ENUM('Y','N') DEFAULT 'N',
    `completedByID` INT NULL COMMENT 'FK to people',
    `completedDate` DATETIME NULL,
    `validationRule` JSON NULL COMMENT 'Optional validation logic',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_template` (`templateID`),
    INDEX `idx_operationalTask` (`operationalTaskID`),
    INDEX `idx_itemOrder` (`itemOrder`),
    FOREIGN KEY (`templateID`) REFERENCES `tija_operational_task_templates`(`templateID`) ON DELETE CASCADE,
    FOREIGN KEY (`operationalTaskID`) REFERENCES `tija_operational_tasks`(`operationalTaskID`) ON DELETE CASCADE,
    CHECK ((`templateID` IS NOT NULL) OR (`operationalTaskID` IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Checklist items for operational tasks';

