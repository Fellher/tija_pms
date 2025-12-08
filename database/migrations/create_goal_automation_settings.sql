-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Goal Automation Settings Table
-- Purpose: User preferences for automation (manual vs cron jobs)
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_goal_automation_settings
-- User preferences for goal automation features
CREATE TABLE IF NOT EXISTS `tija_goal_automation_settings` (
    `settingID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `userID` INT UNSIGNED NOT NULL COMMENT 'FK to people.ID',
    `entityID` INT UNSIGNED NULL COMMENT 'FK to tija_entities.entityID for entity-level settings',
    `automationType` ENUM('score_calculation', 'snapshot_generation', 'evaluation_reminders', 'deadline_alerts', 'cascade_updates') NOT NULL COMMENT 'Type of automation',
    `executionMode` ENUM('automatic', 'manual', 'scheduled') NOT NULL DEFAULT 'automatic' COMMENT 'How automation runs',
    `scheduleFrequency` ENUM('daily', 'weekly', 'monthly', 'never') NULL COMMENT 'Frequency if scheduled',
    `scheduleTime` TIME NULL COMMENT 'Time of day to run (if scheduled)',
    `isEnabled` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this automation enabled',
    `notificationPreference` ENUM('email', 'in_app', 'both', 'none') NOT NULL DEFAULT 'both' COMMENT 'How to notify user',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL,
    UNIQUE KEY `unique_user_automation` (`userID`, `automationType`),
    INDEX `idx_userID` (`userID`),
    INDEX `idx_entityID` (`entityID`),
    INDEX `idx_automationType` (`automationType`),
    INDEX `idx_isEnabled` (`isEnabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Automation Settings - User preferences for automation features';

-- Insert default settings for existing users (all automatic)
INSERT INTO `tija_goal_automation_settings` (`userID`, `automationType`, `executionMode`, `isEnabled`, `notificationPreference`)
SELECT
    ID,
    'score_calculation',
    'automatic',
    'Y',
    'both'
FROM people
WHERE Valid = 'Y'
ON DUPLICATE KEY UPDATE executionMode = 'automatic';

INSERT INTO `tija_goal_automation_settings` (`userID`, `automationType`, `executionMode`, `isEnabled`, `notificationPreference`)
SELECT
    ID,
    'snapshot_generation',
    'automatic',
    'Y',
    'both'
FROM people
WHERE Valid = 'Y'
ON DUPLICATE KEY UPDATE executionMode = 'automatic';

INSERT INTO `tija_goal_automation_settings` (`userID`, `automationType`, `executionMode`, `isEnabled`, `notificationPreference`)
SELECT
    ID,
    'evaluation_reminders',
    'automatic',
    'Y',
    'both'
FROM people
WHERE Valid = 'Y'
ON DUPLICATE KEY UPDATE executionMode = 'automatic';

INSERT INTO `tija_goal_automation_settings` (`userID`, `automationType`, `executionMode`, `isEnabled`, `notificationPreference`)
SELECT
    ID,
    'deadline_alerts',
    'automatic',
    'Y',
    'both'
FROM people
WHERE Valid = 'Y'
ON DUPLICATE KEY UPDATE executionMode = 'automatic';

