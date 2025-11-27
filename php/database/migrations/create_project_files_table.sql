-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create tija_project_files table
-- Purpose: Project file and document management
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_project_files` (
    `fileID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `projectID` INT NOT NULL,
    `taskID` INT NULL COMMENT 'Optional task linkage',
    `fileName` VARCHAR(255) NOT NULL,
    `fileOriginalName` VARCHAR(255) NOT NULL,
    `fileURL` VARCHAR(500) NOT NULL,
    `fileType` VARCHAR(50) COMMENT 'pdf, docx, xlsx, image, etc.',
    `fileSize` BIGINT COMMENT 'File size in bytes',
    `fileMimeType` VARCHAR(100),
    `category` VARCHAR(100) COMMENT 'contract, design, report, etc.',
    `version` VARCHAR(20) DEFAULT '1.0',
    `uploadedBy` INT NOT NULL,
    `description` TEXT,
    `isPublic` ENUM('Y','N') DEFAULT 'N' COMMENT 'Accessible to client',
    `downloadCount` INT DEFAULT 0,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_project` (`projectID`),
    INDEX `idx_category` (`category`),
    INDEX `idx_uploader` (`uploadedBy`),
    INDEX `idx_task` (`taskID`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Project file and document management';

