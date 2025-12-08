-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Goal Evaluation Tables
-- Purpose: Multi-rater evaluation system with weighted scoring
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_goal_evaluations
-- Multi-rater evaluations (360-degree feedback)
CREATE TABLE IF NOT EXISTS `tija_goal_evaluations` (
    `evaluationID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID',
    `evaluatorUserID` INT UNSIGNED NOT NULL COMMENT 'FK to people.ID - who is evaluating',
    `evaluatorRole` ENUM('Manager', 'Self', 'Peer', 'Subordinate', 'Matrix', 'External') NOT NULL COMMENT 'Role of evaluator',
    `score` DECIMAL(5,2) NOT NULL COMMENT 'Score given (0.00-100.00)',
    `comments` TEXT NULL COMMENT 'Evaluation comments/feedback',
    `isAnonymous` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this evaluation anonymous',
    `evaluationDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When evaluation was submitted',
    `status` ENUM('Draft', 'Submitted', 'Approved', 'Rejected') NOT NULL DEFAULT 'Draft' COMMENT 'Evaluation status',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_goalUUID` (`goalUUID`),
    INDEX `idx_evaluator` (`evaluatorUserID`),
    INDEX `idx_evaluatorRole` (`evaluatorRole`),
    INDEX `idx_status` (`status`),
    INDEX `idx_evaluationDate` (`evaluationDate`),
    FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE CASCADE,
    FOREIGN KEY (`evaluatorUserID`) REFERENCES `people`(`ID`) ON DELETE RESTRICT,
    CHECK (`score` >= 0.00 AND `score` <= 100.00)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Evaluations - Multi-rater evaluation submissions';

-- Table: tija_goal_evaluation_weights
-- Configurable weights for each evaluator role per goal
CREATE TABLE IF NOT EXISTS `tija_goal_evaluation_weights` (
    `weightID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID',
    `evaluatorRole` ENUM('Manager', 'Self', 'Peer', 'Subordinate', 'Matrix', 'External') NOT NULL COMMENT 'Role of evaluator',
    `weight` DECIMAL(5,4) NOT NULL COMMENT 'Weight percentage (0.0000-1.0000)',
    `isDefault` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this a default weight (can be overridden)',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_goal_role` (`goalUUID`, `evaluatorRole`),
    INDEX `idx_goalUUID` (`goalUUID`),
    INDEX `idx_evaluatorRole` (`evaluatorRole`),
    FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE CASCADE,
    CHECK (`weight` >= 0.0000 AND `weight` <= 1.0000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Evaluation Weights - Configurable weights for multi-rater scoring';

-- Table: tija_goal_scores
-- Calculated aggregate scores (cached for performance)
CREATE TABLE IF NOT EXISTS `tija_goal_scores` (
    `scoreID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID',
    `calculatedScore` DECIMAL(5,2) NOT NULL COMMENT 'Weighted average score (0.00-100.00)',
    `weightedScore` DECIMAL(5,2) NOT NULL COMMENT 'Score × weight (0.00-100.00)',
    `calculationDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When score was calculated',
    `calculationMethod` VARCHAR(50) NOT NULL DEFAULT 'WeightedAverage' COMMENT 'Method used: WeightedAverage, AHP, etc.',
    `evaluatorCount` INT UNSIGNED DEFAULT 0 COMMENT 'Number of evaluators included',
    `missingEvaluators` JSON NULL COMMENT 'Array of evaluator roles that did not submit',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_goal_latest` (`goalUUID`, `calculationDate`),
    INDEX `idx_goalUUID` (`goalUUID`),
    INDEX `idx_calculationDate` (`calculationDate`),
    FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE CASCADE,
    CHECK (`calculatedScore` >= 0.00 AND `calculatedScore` <= 100.00),
    CHECK (`weightedScore` >= 0.00 AND `weightedScore` <= 100.00)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Scores - Cached calculated aggregate scores for performance';

