-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Operational Projects Table (BAU Buckets)
-- Purpose: Store operational projects for capacity planning
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_operational_projects` (
    `operationalProjectID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `projectCode` VARCHAR(50) NOT NULL,
    `projectName` VARCHAR(255) NOT NULL COMMENT 'e.g., "FY25 HR Operations"',
    `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') NOT NULL,
    `fiscalYear` INT NOT NULL,
    `projectID` INT NULL COMMENT 'FK to tija_projects - Soft booking link',
    `allocatedHours` DECIMAL(10,2) DEFAULT 0 COMMENT 'Planned BAU hours',
    `actualHours` DECIMAL(10,2) DEFAULT 0 COMMENT 'Logged hours',
    `fteRequirement` DECIMAL(5,2) DEFAULT 0 COMMENT 'Calculated FTE',
    `functionalAreaOwnerID` INT NULL COMMENT 'FK to people - Function head responsible',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_projectCode` (`projectCode`),
    INDEX `idx_functionalArea` (`functionalArea`),
    INDEX `idx_fiscalYear` (`fiscalYear`),
    INDEX `idx_project` (`projectID`),
    INDEX `idx_isActive` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Operational projects (BAU buckets) for capacity planning';

