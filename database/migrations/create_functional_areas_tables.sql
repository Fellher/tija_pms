-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Functional Areas Tables
-- Purpose: Allow dynamic definition of functional areas instead of hardcoded ENUM
-- Date: 2025-11-29
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_functional_areas
-- Master functional areas that can be shared across organizations
CREATE TABLE IF NOT EXISTS `tija_functional_areas` (
    `functionalAreaID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `functionalAreaCode` VARCHAR(50) NOT NULL COMMENT 'Unique code (e.g., FIN, HR, IT)',
    `functionalAreaName` VARCHAR(255) NOT NULL COMMENT 'Display name (e.g., Finance, Human Resources)',
    `functionalAreaDescription` TEXT NULL COMMENT 'Description of the functional area',
    `isShared` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Can be shared across organizations',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `displayOrder` INT DEFAULT 0,
    `createdByID` INT NULL COMMENT 'FK to people',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_functionalAreaCode` (`functionalAreaCode`),
    INDEX `idx_isActive` (`isActive`),
    INDEX `idx_isShared` (`isShared`),
    INDEX `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Master functional areas that can be shared across organizations';

-- Table: tija_organization_functional_areas
-- Junction table linking organizations to functional areas
CREATE TABLE IF NOT EXISTS `tija_organization_functional_areas` (
    `linkID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `orgDataID` INT NOT NULL COMMENT 'FK to tija_organisation_data',
    `functionalAreaID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_functional_areas',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    UNIQUE KEY `unique_org_functional_area` (`orgDataID`, `functionalAreaID`),
    INDEX `idx_organization` (`orgDataID`),
    INDEX `idx_functionalArea` (`functionalAreaID`),
    INDEX `idx_isActive` (`isActive`),
    FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas`(`functionalAreaID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Junction table linking organizations to functional areas';

-- Insert default functional areas (migrate from ENUM values)
INSERT INTO tija_functional_areas (functionalAreaCode, functionalAreaName, functionalAreaDescription, isShared, isActive, displayOrder) VALUES
('FIN', 'Finance', 'Financial management, accounting, treasury, and financial planning', 'Y', 'Y', 1),
('HR', 'Human Resources', 'Human capital management, recruitment, payroll, benefits, and employee relations', 'Y', 'Y', 2),
('IT', 'Information Technology', 'IT infrastructure, systems, applications, and technology support', 'Y', 'Y', 3),
('SALES', 'Sales', 'Sales operations, customer acquisition, and revenue generation', 'Y', 'Y', 4),
('MKTG', 'Marketing', 'Marketing strategy, campaigns, branding, and customer engagement', 'Y', 'Y', 5),
('LEGAL', 'Legal', 'Legal affairs, compliance, contracts, and risk management', 'Y', 'Y', 6),
('FAC', 'Facilities', 'Facilities management, property, maintenance, and workplace services', 'Y', 'Y', 7),
('CUSTOM', 'Custom', 'Custom functional area for organization-specific needs', 'Y', 'Y', 8)
ON DUPLICATE KEY UPDATE functionalAreaName = VALUES(functionalAreaName);

