-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Operational Task Templates Table
-- Purpose: Store recurring operational task templates
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_operational_task_templates` (
    `templateID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `templateCode` VARCHAR(50) NOT NULL,
    `templateName` VARCHAR(255) NOT NULL,
    `templateDescription` TEXT,
    `processID` INT UNSIGNED NULL COMMENT 'FK to tija_bau_processes',
    `workflowID` INT UNSIGNED NULL COMMENT 'FK to tija_workflows - Optional workflow',
    `sopID` INT UNSIGNED NULL COMMENT 'FK to tija_sops - Linked SOP',
    `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') NOT NULL,
    `frequencyType` ENUM('daily','weekly','monthly','quarterly','annually','custom','event_driven') NOT NULL,
    `frequencyInterval` INT DEFAULT 1 COMMENT 'e.g., every 2 weeks',
    `frequencyDayOfWeek` INT NULL COMMENT '1-7 for weekly',
    `frequencyDayOfMonth` INT NULL COMMENT '1-31 for monthly/quarterly',
    `frequencyMonthOfYear` INT NULL COMMENT '1-12 for annually',
    `triggerEvent` VARCHAR(100) NULL COMMENT 'Event name for event-driven tasks',
    `estimatedDuration` DECIMAL(10,2) NULL COMMENT 'Estimated hours',
    `assignmentRule` JSON NULL COMMENT 'Auto-assignment logic (role-based, employee-specific, round-robin, etc.)',
    `requiresApproval` ENUM('Y','N') DEFAULT 'N',
    `approverRoleID` INT NULL COMMENT 'FK to permission roles',
    `requiresSOPReview` ENUM('Y','N') DEFAULT 'N' COMMENT 'Must review SOP before starting',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `processingMode` ENUM('cron','manual','both') DEFAULT 'cron' COMMENT 'cron=automatic via cron, manual=user notification on login, both=both methods',
    `lastNotificationSent` DATETIME NULL COMMENT 'Last time notification was sent for manual processing',
    `createdByID` INT NULL COMMENT 'FK to people',
    `functionalAreaOwnerID` INT NULL COMMENT 'FK to people - Function head',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_templateCode` (`templateCode`),
    INDEX `idx_process` (`processID`),
    INDEX `idx_workflow` (`workflowID`),
    INDEX `idx_sop` (`sopID`),
    INDEX `idx_functionalArea` (`functionalArea`),
    INDEX `idx_frequencyType` (`frequencyType`),
    INDEX `idx_isActive` (`isActive`),
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE SET NULL,
    FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows`(`workflowID`) ON DELETE SET NULL,
    FOREIGN KEY (`sopID`) REFERENCES `tija_sops`(`sopID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Operational task templates for recurring tasks';

