-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Goal Reporting Tables
-- Purpose: Performance snapshots and currency rates for global reporting
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_goal_performance_snapshots
-- Weekly snapshots for data warehouse and reporting
CREATE TABLE IF NOT EXISTS `tija_goal_performance_snapshots` (
    `snapshotID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goalUUID` CHAR(36) NOT NULL COMMENT 'FK to tija_goals.goalUUID',
    `snapshotDate` DATE NOT NULL COMMENT 'Date of snapshot (typically weekly)',
    `currentScore` DECIMAL(5,2) NULL COMMENT 'Current calculated score (0.00-100.00)',
    `targetValue` DECIMAL(15,2) NULL COMMENT 'Target value at snapshot time',
    `actualValue` DECIMAL(15,2) NULL COMMENT 'Actual value at snapshot time',
    `completionPercentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Completion percentage (0.00-100.00)',
    `status` VARCHAR(50) NOT NULL DEFAULT 'Active' COMMENT 'Status: OnTrack, AtRisk, Behind, Completed',
    `trend` ENUM('Improving', 'Stable', 'Declining') NULL COMMENT 'Trend compared to previous snapshot',
    `ownerEntityID` INT UNSIGNED NULL COMMENT 'FK to tija_entities.entityID - for aggregation',
    `ownerUserID` INT UNSIGNED NULL COMMENT 'FK to people.ID - for individual goals',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_goalUUID` (`goalUUID`),
    INDEX `idx_snapshotDate` (`snapshotDate`),
    INDEX `idx_ownerEntity` (`ownerEntityID`),
    INDEX `idx_ownerUser` (`ownerUserID`),
    INDEX `idx_status` (`status`),
    UNIQUE KEY `unique_goal_snapshot` (`goalUUID`, `snapshotDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Performance Snapshots - Weekly snapshots for data warehouse and reporting';

-- Add foreign key constraints after table creation
-- Note: These will only work if the referenced tables exist
-- FK to goals (should exist if goals tables are created first)
-- ALTER TABLE `tija_goal_performance_snapshots`
--     ADD CONSTRAINT `fk_snapshot_goal`
--     FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals`(`goalUUID`) ON DELETE CASCADE;

-- FK to entities (if tija_entities exists)
-- ALTER TABLE `tija_goal_performance_snapshots`
--     ADD CONSTRAINT `fk_snapshot_ownerEntity`
--     FOREIGN KEY (`ownerEntityID`) REFERENCES `tija_entities`(`entityID`) ON DELETE SET NULL;

-- FK to people (if people table exists)
-- ALTER TABLE `tija_goal_performance_snapshots`
--     ADD CONSTRAINT `fk_snapshot_ownerUser`
--     FOREIGN KEY (`ownerUserID`) REFERENCES `people`(`ID`) ON DELETE SET NULL;

-- Note: CHECK constraints removed for compatibility with MySQL < 8.0.16
-- For data validation, consider:
-- 1. Application-level validation
-- 2. Triggers (BEFORE INSERT/UPDATE)
-- 3. Or upgrade to MySQL 8.0.16+ and add:
--    CHECK (`currentScore` IS NULL OR (`currentScore` >= 0.00 AND `currentScore` <= 100.00)),
--    CHECK (`completionPercentage` >= 0.00 AND `completionPercentage` <= 100.00)

-- Table: tija_goal_currency_rates
-- Exchange rates for multi-currency normalization
CREATE TABLE IF NOT EXISTS `tija_goal_currency_rates` (
    `rateID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fromCurrency` VARCHAR(3) NOT NULL COMMENT 'ISO currency code (e.g., USD, EUR, JPY)',
    `toCurrency` VARCHAR(3) NOT NULL COMMENT 'ISO currency code (target currency)',
    `budgetRate` DECIMAL(15,6) NOT NULL COMMENT 'Fixed budget rate (set at fiscal year start)',
    `spotRate` DECIMAL(15,6) NOT NULL COMMENT 'Current spot rate',
    `effectiveDate` DATE NOT NULL COMMENT 'Date rate becomes effective',
    `expiryDate` DATE NULL COMMENT 'Date rate expires (NULL = current)',
    `fiscalYear` YEAR NOT NULL COMMENT 'Fiscal year this rate applies to',
    `rateType` ENUM('Budget', 'Spot', 'Average') NOT NULL DEFAULT 'Spot' COMMENT 'Type of rate',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL,
    INDEX `idx_fromCurrency` (`fromCurrency`),
    INDEX `idx_toCurrency` (`toCurrency`),
    INDEX `idx_effectiveDate` (`effectiveDate`),
    INDEX `idx_fiscalYear` (`fiscalYear`),
    INDEX `idx_rateType` (`rateType`),
    UNIQUE KEY `unique_currency_date` (`fromCurrency`, `toCurrency`, `effectiveDate`, `rateType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Currency Rates - Exchange rates for multi-currency performance normalization';

-- Add foreign key constraints after table creation
-- Note: These will only work if the referenced tables exist
-- FK to people (if people table exists)
-- ALTER TABLE `tija_goal_currency_rates`
--     ADD CONSTRAINT `fk_currency_lastUpdated`
--     FOREIGN KEY (`LastUpdatedByID`) REFERENCES `people`(`ID`) ON DELETE SET NULL;

-- Note: CHECK constraints removed for compatibility with MySQL < 8.0.16
-- For data validation, consider:
-- 1. Application-level validation
-- 2. Triggers (BEFORE INSERT/UPDATE)
-- 3. Or upgrade to MySQL 8.0.16+ and add:
--    CHECK (`budgetRate` > 0),
--    CHECK (`spotRate` > 0),
--    CHECK (`expiryDate` IS NULL OR `expiryDate` >= `effectiveDate`)

