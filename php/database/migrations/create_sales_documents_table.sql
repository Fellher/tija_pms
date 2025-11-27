-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create tija_sales_documents table
-- Purpose: Sales document and file management for sales cases
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tija_sales_documents` (
    `documentID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `salesCaseID` INT NOT NULL COMMENT 'FK to tija_sales_cases',
    `proposalID` INT NULL COMMENT 'Optional FK to tija_proposals if document is proposal-related',
    `documentName` VARCHAR(255) NOT NULL COMMENT 'Display name for the document',
    `fileName` VARCHAR(255) NOT NULL COMMENT 'Stored filename',
    `fileOriginalName` VARCHAR(255) NOT NULL COMMENT 'Original filename from upload',
    `fileURL` VARCHAR(500) NOT NULL COMMENT 'Path to stored file',
    `fileType` VARCHAR(50) COMMENT 'File extension: pdf, docx, xlsx, etc.',
    `fileSize` BIGINT COMMENT 'File size in bytes',
    `fileMimeType` VARCHAR(100) COMMENT 'MIME type',
    `documentCategory` VARCHAR(100) NOT NULL COMMENT 'Category: sales_agreement, tor, proposal, engagement_letter, confidentiality_agreement, expense_document, other',
    `documentType` VARCHAR(100) NULL COMMENT 'Sub-type or specific document type',
    `version` VARCHAR(20) DEFAULT '1.0' COMMENT 'Document version',
    `uploadedBy` INT NOT NULL COMMENT 'FK to tija_users',
    `description` TEXT COMMENT 'Document description or notes',
    `expenseID` INT NULL COMMENT 'Optional FK to expense if this is an expense document',
    `isConfidential` ENUM('Y','N') DEFAULT 'N' COMMENT 'Confidential document flag',
    `isPublic` ENUM('Y','N') DEFAULT 'N' COMMENT 'Accessible to client',
    `requiresApproval` ENUM('Y','N') DEFAULT 'N' COMMENT 'Requires management/finance approval',
    `approvalStatus` ENUM('pending','approved','rejected') NULL COMMENT 'Approval status if requiresApproval=Y',
    `approvedBy` INT NULL COMMENT 'FK to tija_users - who approved',
    `approvedDate` DATETIME NULL COMMENT 'Approval date',
    `downloadCount` INT DEFAULT 0 COMMENT 'Number of times downloaded',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL COMMENT 'FK to tija_users',
    `Suspended` ENUM('Y','N') DEFAULT 'N' COMMENT 'Soft delete flag',
    INDEX `idx_sales_case` (`salesCaseID`),
    INDEX `idx_proposal` (`proposalID`),
    INDEX `idx_category` (`documentCategory`),
    INDEX `idx_uploader` (`uploadedBy`),
    INDEX `idx_expense` (`expenseID`),
    INDEX `idx_approval` (`requiresApproval`, `approvalStatus`),
    INDEX `idx_confidential` (`isConfidential`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sales documents and files management for sales cases';

-- ────────────────────────────────────────────────────────────────────────────
-- Optional: Add Foreign Key Constraints (run separately if tables exist)
-- ────────────────────────────────────────────────────────────────────────────
-- Uncomment and run these separately if you want foreign key constraints:
--
-- ALTER TABLE `tija_sales_documents`
--     ADD CONSTRAINT `fk_sales_documents_sales_case`
--     FOREIGN KEY (`salesCaseID`) REFERENCES `tija_sales_cases`(`salesCaseID`)
--     ON DELETE CASCADE;
--
-- ALTER TABLE `tija_sales_documents`
--     ADD CONSTRAINT `fk_sales_documents_uploaded_by`
--     FOREIGN KEY (`uploadedBy`) REFERENCES `people`(`ID`)
--     ON DELETE RESTRICT;

-- ────────────────────────────────────────────────────────────────────────────
-- Document Categories Reference
-- ────────────────────────────────────────────────────────────────────────────
-- sales_agreement: Sales agreements and contracts
-- tor: Terms of Reference
-- proposal: Proposals and quotes
-- engagement_letter: Engagement letters
-- confidentiality_agreement: NDA and confidentiality agreements
-- expense_document: Expense receipts, invoices, etc.
-- correspondence: Email correspondence, letters
-- meeting_notes: Meeting minutes and notes
-- other: Other documents

