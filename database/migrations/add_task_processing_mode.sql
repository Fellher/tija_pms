-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Add Task Processing Mode Support
-- Purpose: Support both cron-based and manual (notification-based) task processing
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Add processing mode to operational task templates
ALTER TABLE `tija_operational_task_templates`
    ADD COLUMN `processingMode` ENUM('cron','manual','both') DEFAULT 'cron'
    COMMENT 'cron=automatic via cron, manual=user notification on login, both=both methods'
    AFTER `isActive`;

-- Add notification sent flag to track manual processing notifications
ALTER TABLE `tija_operational_task_templates`
    ADD COLUMN `lastNotificationSent` DATETIME NULL
    COMMENT 'Last time notification was sent for manual processing'
    AFTER `processingMode`;

-- Add pending task notifications table
CREATE TABLE IF NOT EXISTS `tija_operational_task_notifications` (
    `notificationID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `templateID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_operational_task_templates',
    `employeeID` INT NOT NULL COMMENT 'FK to people - User to notify',
    `dueDate` DATE NOT NULL COMMENT 'Task due date',
    `notificationType` ENUM('scheduled_task_ready','task_overdue','task_due_soon') DEFAULT 'scheduled_task_ready',
    `status` ENUM('pending','sent','acknowledged','processed','dismissed') DEFAULT 'pending',
    `sentDate` DATETIME NULL,
    `acknowledgedDate` DATETIME NULL,
    `processedDate` DATETIME NULL,
    `taskInstanceID` INT UNSIGNED NULL COMMENT 'FK to tija_operational_tasks - Created when processed',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_template` (`templateID`),
    INDEX `idx_employee` (`employeeID`),
    INDEX `idx_status` (`status`),
    INDEX `idx_dueDate` (`dueDate`),
    FOREIGN KEY (`templateID`) REFERENCES `tija_operational_task_templates`(`templateID`) ON DELETE CASCADE,
    FOREIGN KEY (`taskInstanceID`) REFERENCES `tija_operational_tasks`(`operationalTaskID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Notifications for manual task processing';

