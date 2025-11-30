-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create SOP Management Tables
-- Purpose: Support Standard Operating Procedure (SOP) management
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_sops
-- SOP master records
CREATE TABLE IF NOT EXISTS `tija_sops` (
    `sopID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sopCode` VARCHAR(50) NOT NULL,
    `sopTitle` VARCHAR(255) NOT NULL,
    `sopDescription` TEXT,
    `processID` INT UNSIGNED NULL COMMENT 'FK to tija_bau_processes',
    `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') NOT NULL,
    `sopVersion` VARCHAR(20) DEFAULT '1.0' COMMENT 'Version number',
    `sopDocumentURL` TEXT NULL COMMENT 'Link to document/knowledge base',
    `sopContent` LONGTEXT NULL COMMENT 'Rich text content (HTML/Markdown)',
    `effectiveDate` DATE NULL,
    `expiryDate` DATE NULL,
    `approvalStatus` ENUM('draft','pending_approval','approved','archived') DEFAULT 'draft',
    `approvedByID` INT NULL COMMENT 'FK to people',
    `approvedDate` DATETIME NULL,
    `createdByID` INT NULL COMMENT 'FK to people',
    `functionalAreaOwnerID` INT NULL COMMENT 'FK to people - Function head',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `Lapsed` ENUM('Y','N') DEFAULT 'N',
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    UNIQUE KEY `unique_sopCode_version` (`sopCode`, `sopVersion`),
    INDEX `idx_process` (`processID`),
    INDEX `idx_functionalArea` (`functionalArea`),
    INDEX `idx_approvalStatus` (`approvalStatus`),
    INDEX `idx_isActive` (`isActive`),
    FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes`(`processID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='SOP master records';

-- Table: tija_sop_sections
-- SOP structured sections
CREATE TABLE IF NOT EXISTS `tija_sop_sections` (
    `sectionID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sopID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
    `sectionOrder` INT NOT NULL,
    `sectionTitle` VARCHAR(255) NOT NULL,
    `sectionContent` TEXT,
    `sectionType` ENUM('overview','procedure','checklist','troubleshooting','references') DEFAULT 'procedure',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sop` (`sopID`),
    INDEX `idx_sectionOrder` (`sectionOrder`),
    FOREIGN KEY (`sopID`) REFERENCES `tija_sops`(`sopID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='SOP structured sections';

-- Table: tija_sop_attachments
-- SOP file attachments
CREATE TABLE IF NOT EXISTS `tija_sop_attachments` (
    `attachmentID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sopID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
    `fileName` VARCHAR(255) NOT NULL,
    `fileURL` VARCHAR(500) NOT NULL,
    `fileType` VARCHAR(50) NULL,
    `fileSize` BIGINT NULL COMMENT 'File size in bytes',
    `uploadedByID` INT NULL COMMENT 'FK to people',
    `uploadedDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sop` (`sopID`),
    FOREIGN KEY (`sopID`) REFERENCES `tija_sops`(`sopID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='SOP file attachments';

-- Table: tija_sop_links
-- Links SOPs to tasks/templates
CREATE TABLE IF NOT EXISTS `tija_sop_links` (
    `linkID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sopID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
    `linkType` ENUM('template','task','workflow_step','process') NOT NULL,
    `linkedEntityID` INT UNSIGNED NOT NULL COMMENT 'ID of linked entity',
    `isRequired` ENUM('Y','N') DEFAULT 'N' COMMENT 'Must review before completion',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sop` (`sopID`),
    INDEX `idx_linkType_entity` (`linkType`, `linkedEntityID`),
    FOREIGN KEY (`sopID`) REFERENCES `tija_sops`(`sopID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Links SOPs to tasks/templates/workflows';

