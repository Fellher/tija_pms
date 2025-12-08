-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Goal Library Tables
-- Purpose: Centralized goal template repository with taxonomy and versioning
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_goal_library
-- Template repository for standardized goal creation
CREATE TABLE IF NOT EXISTS `tija_goal_library` (
    `libraryID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `templateCode` VARCHAR(50) NOT NULL COMMENT 'Unique template code (e.g., SALE-001)',
    `templateName` VARCHAR(255) NOT NULL COMMENT 'Template name',
    `templateDescription` TEXT NULL COMMENT 'Template description',
    `goalType` ENUM('Strategic', 'OKR', 'KPI') NOT NULL COMMENT 'Type of goal this template creates',
    `variables` JSON NULL COMMENT 'Parameterized fields: ["Product", "Target", "Timeframe"]',
    `defaultKPIs` JSON NULL COMMENT 'Suggested metrics: [{"name": "Revenue Growth", "target": 20}]',
    `jurisdictionDeny` JSON NULL COMMENT 'Array of jurisdiction codes where invalid: ["DE", "FR"]',
    `suggestedWeight` DECIMAL(5,4) DEFAULT 0.2500 COMMENT 'Suggested weight (0.0000-1.0000)',
    `functionalDomain` VARCHAR(100) NULL COMMENT 'Department/job family: Sales, IT, HR, Legal, Operations',
    `competencyLevel` ENUM('Junior', 'Senior', 'Principal', 'Executive', 'All') NOT NULL DEFAULT 'All' COMMENT 'Required seniority level',
    `strategicPillar` VARCHAR(100) NULL COMMENT 'L0 objective it supports: Innovation, Revenue, ESG, Customer Intimacy',
    `timeHorizon` ENUM('5-Year', 'Annual', 'Quarterly', 'Sprint', 'Monthly') NOT NULL DEFAULT 'Annual' COMMENT 'Intended duration',
    `jurisdictionScope` VARCHAR(255) NULL COMMENT 'Where valid: Global, EU-Only, Excludes-California',
    `broaderConceptID` INT UNSIGNED NULL COMMENT 'SKOS: FK to parent concept in taxonomy',
    `narrowerConceptIDs` JSON NULL COMMENT 'SKOS: Array of child concept IDs',
    `relatedConceptIDs` JSON NULL COMMENT 'SKOS: Array of related concept IDs',
    `isActive` ENUM('Y','N') NOT NULL DEFAULT 'Y',
    `usageCount` INT UNSIGNED DEFAULT 0 COMMENT 'Number of times this template has been used',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL COMMENT 'FK to people.ID',
    `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
    UNIQUE KEY `unique_templateCode` (`templateCode`),
    INDEX `idx_goalType` (`goalType`),
    INDEX `idx_functionalDomain` (`functionalDomain`),
    INDEX `idx_competencyLevel` (`competencyLevel`),
    INDEX `idx_strategicPillar` (`strategicPillar`),
    INDEX `idx_timeHorizon` (`timeHorizon`),
    INDEX `idx_isActive` (`isActive`),
    INDEX `idx_broaderConcept` (`broaderConceptID`),
    FOREIGN KEY (`broaderConceptID`) REFERENCES `tija_goal_library`(`libraryID`) ON DELETE SET NULL,
    FOREIGN KEY (`LastUpdatedByID`) REFERENCES `people`(`ID`) ON DELETE SET NULL,
    CHECK (`suggestedWeight` >= 0.0000 AND `suggestedWeight` <= 1.0000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Library - Centralized template repository for standardized goal creation';

-- Table: tija_goal_library_versions
-- Template versioning for tracking changes over time
CREATE TABLE IF NOT EXISTS `tija_goal_library_versions` (
    `versionID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `libraryID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_goal_library.libraryID',
    `versionNumber` INT UNSIGNED NOT NULL COMMENT 'Version number (1, 2, 3, ...)',
    `templateData` JSON NOT NULL COMMENT 'Complete snapshot of template at this version',
    `changeDescription` TEXT NULL COMMENT 'Description of changes in this version',
    `effectiveDate` DATE NOT NULL COMMENT 'Date this version became effective',
    `deprecatedDate` DATE NULL COMMENT 'Date this version was deprecated (NULL = current)',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT UNSIGNED NULL,
    UNIQUE KEY `unique_library_version` (`libraryID`, `versionNumber`),
    INDEX `idx_libraryID` (`libraryID`),
    INDEX `idx_effectiveDate` (`effectiveDate`),
    FOREIGN KEY (`libraryID`) REFERENCES `tija_goal_library`(`libraryID`) ON DELETE CASCADE,
    FOREIGN KEY (`LastUpdatedByID`) REFERENCES `people`(`ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goal Library Versions - Template versioning and change tracking';

