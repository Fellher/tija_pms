-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Goals Core Tables
-- Purpose: Core goal management tables supporting Strategic Goals, OKRs, and KPIs
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_goals
-- Main goals table supporting polymorphic goal types (Strategic, OKR, KPI)
CREATE TABLE IF NOT EXISTS `tija_goals` (
    `goalUUID` CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID v4 for global uniqueness and sharding support',
    `parentGoalUUID` CHAR(36) NULL COMMENT 'Self-referencing FK for cascading goals',
    `ownerEntityID` INT UNSIGNED NULL COMMENT 'FK to tija_entities.entityID for entity-level goals',
    `ownerUserID` INT UNSIGNED NULL COMMENT 'FK to people.ID for individual-level goals',
    `libraryRefID` INT UNSIGNED NULL COMMENT 'FK to tija_goal_library.libraryID if created from template',
    `goalType` ENUM('Strategic', 'OKR', 'KPI') NOT NULL COMMENT 'Type of goal',
    `goalTitle` VARCHAR(500) NOT NULL COMMENT 'Goal title/name',
    `goalDescription` TEXT NULL COMMENT 'Detailed description',
    `propriety` ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium' COMMENT 'Criticality level',
    `weight` DECIMAL(5,4) NOT NULL DEFAULT 0.0000 COMMENT 'Weight percentage (0.0000-1.0000)',
    `progressMetric` JSON NULL COMMENT 'Progress tracking: {"current": 80, "target": 100, "unit": "USD", "currency": "USD"}',
    `evaluatorConfig` JSON NULL COMMENT 'Multi-rater configuration: {"manager_weight": 0.5, "peer_weight": 0.3, "self_weight": 0.2}',
    `jurisdictionID` INT UNSIGNED NULL COMMENT 'FK to tija_entities.entityID for L3 compliance rules',
    `visibility` ENUM('Global', 'Public', 'Private') NOT NULL DEFAULT 'Private' COMMENT 'Visibility scope',
    `cascadeMode` ENUM('Strict', 'Aligned', 'Hybrid', 'None') NOT NULL DEFAULT 'None' COMMENT 'Cascade mode if this is a parent goal',
    `startDate` DATE NOT NULL COMMENT 'Goal start date',
    `endDate` DATE NOT NULL COMMENT 'Goal end date',
    `status` ENUM('Draft', 'Active', 'Completed', 'Cancelled', 'OnHold') NOT NULL DEFAULT 'Draft' COMMENT 'Goal status',
    `completionPercentage` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Calculated completion percentage',
    `sysStartTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Temporal versioning start',
    `sysEndTime` DATETIME NULL COMMENT 'Temporal versioning end (NULL = current version)',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL COMMENT 'FK to people.ID',
    `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
    INDEX `idx_parentGoal` (`parentGoalUUID`),
    INDEX `idx_ownerEntity` (`ownerEntityID`),
    INDEX `idx_ownerUser` (`ownerUserID`),
    INDEX `idx_libraryRef` (`libraryRefID`),
    INDEX `idx_goalType` (`goalType`),
    INDEX `idx_status` (`status`),
    INDEX `idx_propriety` (`propriety`),
    INDEX `idx_dates` (`startDate`, `endDate`),
    INDEX `idx_jurisdiction` (`jurisdictionID`),
    INDEX `idx_temporal` (`sysStartTime`, `sysEndTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Main goals table - supports Strategic Goals, OKRs, and KPIs';

-- Add foreign key constraints after table creation
-- Note: These will only work if the referenced tables exist
-- Self-referencing FK for parent goals (add this after table is created)
-- ALTER TABLE `tija_goals`
--     ADD CONSTRAINT `fk_goals_parent`
--     FOREIGN KEY (`parentGoalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE RESTRICT;

-- Note: CHECK constraints removed for compatibility with MySQL < 8.0.16
-- For data validation, consider:
-- 1. Application-level validation
-- 2. Triggers (BEFORE INSERT/UPDATE)
-- 3. Or upgrade to MySQL 8.0.16+ and add:
--    CHECK (`weight` >= 0.0000 AND `weight` <= 1.0000),
--    CHECK (`completionPercentage` >= 0.00 AND `completionPercentage` <= 100.00),
--    CHECK (`endDate` >= `startDate`)

-- FK to entities (if tija_entities exists)
-- ALTER TABLE `tija_goals`
--     ADD CONSTRAINT `fk_goals_ownerEntity`
--     FOREIGN KEY (`ownerEntityID`) REFERENCES `tija_entities`(`entityID`) ON DELETE RESTRICT;

-- FK to people (if people table exists)
-- ALTER TABLE `tija_goals`
--     ADD CONSTRAINT `fk_goals_ownerUser`
--     FOREIGN KEY (`ownerUserID`) REFERENCES `people`(`ID`) ON DELETE RESTRICT;

-- FK to goal library (if tija_goal_library exists)
-- ALTER TABLE `tija_goals`
--     ADD CONSTRAINT `fk_goals_library`
--     FOREIGN KEY (`libraryRefID`) REFERENCES `tija_goal_library`(`libraryID`) ON DELETE SET NULL;

-- FK to jurisdiction entity (if tija_entities exists)
-- ALTER TABLE `tija_goals`
--     ADD CONSTRAINT `fk_goals_jurisdiction`
--     FOREIGN KEY (`jurisdictionID`) REFERENCES `tija_entities`(`entityID`) ON DELETE SET NULL;

-- Table: tija_goal_okrs
-- OKR-specific data (Objectives and Key Results)
CREATE TABLE IF NOT EXISTS `tija_goal_okrs` (
    `okrID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID',
    `objective` TEXT NOT NULL COMMENT 'Qualitative Objective (the O in OKR)',
    `keyResults` JSON NOT NULL COMMENT 'Array of Key Results: [{"kr": "Reduce carbon by 20%", "target": 20, "current": 15, "unit": "percent"}, ...]',
    `alignmentDirection` ENUM('TopDown', 'BottomUp', 'Bidirectional') NOT NULL DEFAULT 'TopDown' COMMENT 'How this OKR aligns',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL,
    UNIQUE KEY `unique_goalUUID` (`goalUUID`),
    FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='OKR-specific data - Objectives and Key Results';

-- Table: tija_goal_kpis
-- KPI-specific data (Key Performance Indicators)
CREATE TABLE IF NOT EXISTS `tija_goal_kpis` (
    `kpiID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID',
    `kpiName` VARCHAR(255) NOT NULL COMMENT 'KPI name',
    `kpiDescription` TEXT NULL COMMENT 'KPI description',
    `measurementFrequency` ENUM('Daily', 'Weekly', 'Monthly', 'Quarterly', 'Annual', 'Continuous') NOT NULL DEFAULT 'Monthly' COMMENT 'How often this KPI is measured',
    `baselineValue` DECIMAL(15,2) NULL COMMENT 'Baseline value at start',
    `targetValue` DECIMAL(15,2) NOT NULL COMMENT 'Target value to achieve',
    `currentValue` DECIMAL(15,2) NULL COMMENT 'Current value',
    `unit` VARCHAR(50) NULL COMMENT 'Unit of measurement (e.g., USD, %, hours)',
    `currencyCode` VARCHAR(3) NULL COMMENT 'ISO currency code if monetary KPI',
    `reportingRate` DECIMAL(15,6) NULL COMMENT 'Exchange rate for multi-currency normalization',
    `isPerpetual` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this a perpetual/continuous KPI',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL,
    UNIQUE KEY `unique_goalUUID` (`goalUUID`),
    INDEX `idx_currency` (`currencyCode`),
    FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='KPI-specific data - Key Performance Indicators';

