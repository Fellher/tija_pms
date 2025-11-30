-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Operational Task Dependencies Table
-- Purpose: Store task dependencies for operational workflows
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_operational_task_dependencies` (
    `dependencyID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `predecessorTaskID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_tasks or templateID',
    `predecessorTemplateID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_task_templates',
    `successorTaskID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_tasks or templateID',
    `successorTemplateID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_task_templates',
    `dependencyType` ENUM('finish_to_start','start_to_start','finish_to_finish') DEFAULT 'finish_to_start',
    `lagDays` INT DEFAULT 0 COMMENT 'Delay in days',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_predecessorTask` (`predecessorTaskID`),
    INDEX `idx_predecessorTemplate` (`predecessorTemplateID`),
    INDEX `idx_successorTask` (`successorTaskID`),
    INDEX `idx_successorTemplate` (`successorTemplateID`),
    FOREIGN KEY (`predecessorTaskID`) REFERENCES `tija_operational_tasks`(`operationalTaskID`) ON DELETE CASCADE,
    FOREIGN KEY (`predecessorTemplateID`) REFERENCES `tija_operational_task_templates`(`templateID`) ON DELETE CASCADE,
    FOREIGN KEY (`successorTaskID`) REFERENCES `tija_operational_tasks`(`operationalTaskID`) ON DELETE CASCADE,
    FOREIGN KEY (`successorTemplateID`) REFERENCES `tija_operational_task_templates`(`templateID`) ON DELETE CASCADE,
    CHECK (
        (`predecessorTaskID` IS NOT NULL) OR (`predecessorTemplateID` IS NOT NULL)
    ),
    CHECK (
        (`successorTaskID` IS NOT NULL) OR (`successorTemplateID` IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Task dependencies for operational workflows';

