-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Process Modeling & Simulation Tables
-- Purpose: Support process modeling, simulation, and optimization
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_process_models
-- Process model definitions
CREATE TABLE IF NOT EXISTS `tija_process_models` (
    `modelID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `modelName` VARCHAR(255) NOT NULL,
    `modelDescription` TEXT,
    `processID` INT UNSIGNED NULL COMMENT 'FK to tija_bau_processes',
    `modelType` ENUM('as_is','to_be','simulation','optimized') DEFAULT 'as_is',
    `modelDefinition` JSON NULL COMMENT 'Process model (BPMN-like structure)',
    `createdByID` INT NULL COMMENT 'FK to people',
    `createdDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `isBaseline` ENUM('Y','N') DEFAULT 'N' COMMENT 'Baseline for comparison',
    INDEX `idx_process` (`processID`),
    INDEX `idx_modelType` (`modelType`),
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Process model definitions';

-- Table: tija_process_simulations
-- Simulation runs
CREATE TABLE IF NOT EXISTS `tija_process_simulations` (
    `simulationID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `modelID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_process_models',
    `simulationName` VARCHAR(255) NOT NULL,
    `simulationDescription` TEXT,
    `simulationParameters` JSON NULL COMMENT 'Input parameters',
    `simulationResults` JSON NULL COMMENT 'Output metrics',
    `runDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `runByID` INT NULL COMMENT 'FK to people',
    `status` ENUM('pending','running','completed','failed') DEFAULT 'pending',
    INDEX `idx_model` (`modelID`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`modelID`) REFERENCES `tija_process_models`(`modelID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Process simulation runs';

-- Table: tija_process_metrics
-- Process performance metrics
CREATE TABLE IF NOT EXISTS `tija_process_metrics` (
    `metricID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `processID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
    `metricName` VARCHAR(100) NOT NULL COMMENT 'e.g., cycle_time, cost_per_unit, error_rate',
    `metricValue` DECIMAL(15,4) NOT NULL,
    `metricUnit` VARCHAR(20) NULL COMMENT 'e.g., hours, dollars, percentage',
    `measurementDate` DATE NOT NULL,
    `source` ENUM('actual','simulated','target') DEFAULT 'actual',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_process` (`processID`),
    INDEX `idx_metricName` (`metricName`),
    INDEX `idx_measurementDate` (`measurementDate`),
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Process performance metrics';

-- Table: tija_process_optimization_recommendations
-- AI/ML optimization recommendations
CREATE TABLE IF NOT EXISTS `tija_process_optimization_recommendations` (
    `recommendationID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `processID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
    `recommendationType` ENUM('automation','reengineering','resource_allocation','elimination') NOT NULL,
    `recommendationTitle` VARCHAR(255) NOT NULL,
    `recommendationDescription` TEXT,
    `estimatedImpact` JSON NULL COMMENT 'Expected improvements',
    `implementationEffort` ENUM('low','medium','high') DEFAULT 'medium',
    `priority` ENUM('low','medium','high','critical') DEFAULT 'medium',
    `status` ENUM('pending','approved','implemented','rejected') DEFAULT 'pending',
    `createdDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `createdByID` INT NULL COMMENT 'FK to people (system or user)',
    `approvedByID` INT NULL COMMENT 'FK to people',
    `approvedDate` DATETIME NULL,
    INDEX `idx_process` (`processID`),
    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`),
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Process optimization recommendations';

