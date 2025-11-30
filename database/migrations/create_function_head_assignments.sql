-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Function Head Assignments Table
-- Purpose: Assign function heads to functional areas
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_function_head_assignments` (
    `assignmentID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employeeID` INT NOT NULL COMMENT 'FK to people - Function head',
    `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') NOT NULL,
    `effectiveDate` DATE NOT NULL,
    `expiryDate` DATE NULL,
    `permissions` JSON NULL COMMENT 'Specific permissions (define_processes, define_workflows, approve_sops, etc.)',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    INDEX `idx_employee` (`employeeID`),
    INDEX `idx_functionalArea` (`functionalArea`),
    INDEX `idx_isActive` (`isActive`),
    INDEX `idx_effectiveDate` (`effectiveDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Function head assignments to functional areas';

