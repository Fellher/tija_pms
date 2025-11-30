-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Workflow Definition Tables
-- Purpose: Support workflow definition and execution for operational tasks
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_workflows
-- Master workflow definitions
CREATE TABLE IF NOT EXISTS `tija_workflows` (
    `workflowID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflowCode` VARCHAR(50) NOT NULL,
    `workflowName` VARCHAR(255) NOT NULL,
    `workflowDescription` TEXT,
    `processID` INT UNSIGNED NULL COMMENT 'FK to tija_bau_processes',
    `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') NOT NULL,
    `workflowType` ENUM('sequential','parallel','conditional','state_machine') DEFAULT 'sequential',
    `version` INT DEFAULT 1 COMMENT 'Version control',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `workflowDefinition` JSON NULL COMMENT 'Workflow structure (nodes, edges, conditions)',
    `createdByID` INT NULL COMMENT 'FK to people',
    `functionalAreaOwnerID` INT NULL COMMENT 'FK to people - Function head',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_workflowCode` (`workflowCode`),
    INDEX `idx_process` (`processID`),
    INDEX `idx_functionalArea` (`functionalArea`),
    INDEX `idx_isActive` (`isActive`),
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Master workflow definitions';

-- Table: tija_workflow_steps
-- Individual steps in workflow
CREATE TABLE IF NOT EXISTS `tija_workflow_steps` (
    `workflowStepID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflowID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
    `stepOrder` INT NOT NULL,
    `stepName` VARCHAR(255) NOT NULL,
    `stepDescription` TEXT,
    `stepType` ENUM('task','approval','decision','notification','automation','subprocess') DEFAULT 'task',
    `assigneeType` ENUM('role','employee','function_head','auto') DEFAULT 'auto',
    `assigneeRoleID` INT NULL COMMENT 'FK to permission roles',
    `assigneeEmployeeID` INT NULL COMMENT 'FK to people',
    `estimatedDuration` DECIMAL(10,2) NULL COMMENT 'Estimated hours',
    `isMandatory` ENUM('Y','N') DEFAULT 'Y',
    `stepConfig` JSON NULL COMMENT 'Step-specific configuration',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_workflow` (`workflowID`),
    INDEX `idx_stepOrder` (`stepOrder`),
    INDEX `idx_assigneeRole` (`assigneeRoleID`),
    INDEX `idx_assigneeEmployee` (`assigneeEmployeeID`),
    FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows`(`workflowID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual steps in workflow';

-- Table: tija_workflow_transitions
-- Transitions between steps
CREATE TABLE IF NOT EXISTS `tija_workflow_transitions` (
    `transitionID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflowID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
    `fromStepID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_workflow_steps',
    `toStepID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_workflow_steps',
    `conditionType` ENUM('always','conditional','time_based','event_based') DEFAULT 'always',
    `conditionExpression` JSON NULL COMMENT 'Condition logic',
    `transitionLabel` VARCHAR(255) NULL,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_workflow` (`workflowID`),
    INDEX `idx_fromStep` (`fromStepID`),
    INDEX `idx_toStep` (`toStepID`),
    FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows`(`workflowID`) ON DELETE CASCADE,
    FOREIGN KEY (`fromStepID`) REFERENCES `tija_workflow_steps`(`workflowStepID`) ON DELETE CASCADE,
    FOREIGN KEY (`toStepID`) REFERENCES `tija_workflow_steps`(`workflowStepID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Transitions between workflow steps';

-- Table: tija_workflow_instances
-- Active workflow executions
CREATE TABLE IF NOT EXISTS `tija_workflow_instances` (
    `instanceID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflowID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
    `operationalTaskID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_tasks',
    `currentStepID` INT UNSIGNED NULL COMMENT 'FK to tija_workflow_steps',
    `status` ENUM('pending','in_progress','completed','cancelled','error') DEFAULT 'pending',
    `startedDate` DATETIME NULL,
    `completedDate` DATETIME NULL,
    `instanceData` JSON NULL COMMENT 'Runtime data',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_workflow` (`workflowID`),
    INDEX `idx_operationalTask` (`operationalTaskID`),
    INDEX `idx_currentStep` (`currentStepID`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows`(`workflowID`) ON DELETE RESTRICT,
    FOREIGN KEY (`currentStepID`) REFERENCES `tija_workflow_steps`(`workflowStepID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Active workflow executions';

