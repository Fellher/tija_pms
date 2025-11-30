-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create BAU Taxonomy Tables (APQC Process Classification Framework)
-- Purpose: Support APQC taxonomy for operational work classification
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_bau_categories
-- Top-level domains (e.g., 7.0 Develop and Manage Human Capital)
CREATE TABLE IF NOT EXISTS `tija_bau_categories` (
    `categoryID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `categoryCode` VARCHAR(20) NOT NULL COMMENT 'APQC code (e.g., 7.0)',
    `categoryName` VARCHAR(255) NOT NULL,
    `categoryDescription` TEXT,
    `displayOrder` INT DEFAULT 0,
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_categoryCode` (`categoryCode`),
    INDEX `idx_isActive` (`isActive`),
    INDEX `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='APQC Categories - Top-level domains';

-- Table: tija_bau_process_groups
-- Functional areas within categories (e.g., 7.3 Reward and Retain Employees)
CREATE TABLE IF NOT EXISTS `tija_bau_process_groups` (
    `processGroupID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `categoryID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_bau_categories',
    `processGroupCode` VARCHAR(20) NOT NULL COMMENT 'APQC code (e.g., 7.3)',
    `processGroupName` VARCHAR(255) NOT NULL,
    `processGroupDescription` TEXT,
    `displayOrder` INT DEFAULT 0,
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_processGroupCode` (`processGroupCode`),
    INDEX `idx_category` (`categoryID`),
    INDEX `idx_isActive` (`isActive`),
    FOREIGN KEY (`categoryID`) REFERENCES `tija_bau_categories`(`categoryID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='APQC Process Groups - Functional areas within categories';

-- Table: tija_bau_processes
-- Specific workflows (e.g., 7.3.1 Manage Payroll)
CREATE TABLE IF NOT EXISTS `tija_bau_processes` (
    `processID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `processGroupID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_bau_process_groups',
    `processCode` VARCHAR(20) NOT NULL COMMENT 'APQC code (e.g., 7.3.1)',
    `processName` VARCHAR(255) NOT NULL,
    `processDescription` TEXT,
    `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') NOT NULL,
    `functionalAreaOwnerID` INT NULL COMMENT 'FK to people - Function head',
    `isCustom` ENUM('Y','N') DEFAULT 'N' COMMENT 'Custom vs standard APQC process',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `createdByID` INT NULL COMMENT 'FK to people',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_processCode` (`processCode`),
    INDEX `idx_processGroup` (`processGroupID`),
    INDEX `idx_functionalArea` (`functionalArea`),
    INDEX `idx_functionalAreaOwner` (`functionalAreaOwnerID`),
    INDEX `idx_isActive` (`isActive`),
    FOREIGN KEY (`processGroupID`) REFERENCES `tija_bau_process_groups`(`processGroupID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='APQC Processes - Specific workflows';

-- Table: tija_bau_activities
-- Actionable units of work (e.g., Run Gross-to-Net Calculation)
CREATE TABLE IF NOT EXISTS `tija_bau_activities` (
    `activityID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `processID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
    `activityCode` VARCHAR(50) NULL COMMENT 'Optional activity code',
    `activityName` VARCHAR(255) NOT NULL,
    `activityDescription` TEXT,
    `estimatedDuration` DECIMAL(10,2) NULL COMMENT 'Estimated hours',
    `displayOrder` INT DEFAULT 0,
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_process` (`processID`),
    INDEX `idx_isActive` (`isActive`),
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='APQC Activities - Actionable units of work';

