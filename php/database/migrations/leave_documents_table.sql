-- ============================================================================
-- Leave Documents Table Migration
-- ============================================================================
-- Purpose: Create table for storing supporting documents for leave applications
-- Created: November 6, 2025
-- Safe to run multiple times (uses IF NOT EXISTS)
-- ============================================================================

-- Create leave documents table
CREATE TABLE IF NOT EXISTS `tija_leave_documents` (
    `documentID` int(11) NOT NULL AUTO_INCREMENT,
    `leaveApplicationID` int(11) NOT NULL COMMENT 'FK to tija_leave_applications',
    `fileName` varchar(255) NOT NULL COMMENT 'Original filename',
    `filePath` varchar(500) NOT NULL COMMENT 'Path to stored file',
    `fileSize` int(11) DEFAULT NULL COMMENT 'File size in bytes',
    `fileType` varchar(100) DEFAULT NULL COMMENT 'MIME type',
    `uploadDate` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Upload timestamp',
    `uploadedByID` int(11) DEFAULT NULL COMMENT 'User who uploaded',
    `documentType` varchar(50) DEFAULT 'supporting' COMMENT 'Type: supporting, medical, travel, etc.',
    `documentNotes` text DEFAULT NULL COMMENT 'Optional notes about document',
    `Lapsed` char(1) DEFAULT 'N' COMMENT 'Soft delete flag',
    `Suspended` char(1) DEFAULT 'N' COMMENT 'Temporarily disabled',
    `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdateByID` int(11) DEFAULT NULL,
    PRIMARY KEY (`documentID`),
    KEY `idx_leave_application` (`leaveApplicationID`),
    KEY `idx_uploaded_by` (`uploadedByID`),
    KEY `idx_document_type` (`documentType`),
    KEY `idx_lapsed` (`Lapsed`),
    KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Supporting documents for leave applications';

-- ============================================================================
-- Create leave approval comments table (if not exists)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_leave_approval_comments` (
    `commentID` int(11) NOT NULL AUTO_INCREMENT,
    `leaveApplicationID` int(11) NOT NULL COMMENT 'FK to tija_leave_applications',
    `approverID` int(11) NOT NULL COMMENT 'User who made the comment',
    `approvalLevel` varchar(50) DEFAULT NULL COMMENT 'Supervisor, HR, etc.',
    `comment` text NOT NULL COMMENT 'Comment text',
    `commentDate` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Comment timestamp',
    `commentType` varchar(50) DEFAULT 'approval' COMMENT 'approval, rejection, question, cancellation',
    `Lapsed` char(1) DEFAULT 'N',
    `Suspended` char(1) DEFAULT 'N',
    `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`commentID`),
    KEY `idx_leave_application` (`leaveApplicationID`),
    KEY `idx_approver` (`approverID`),
    KEY `idx_comment_date` (`commentDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comments and notes during leave approval process';

-- ============================================================================
-- Add sample comment for testing
-- ============================================================================

-- Note: This is just a structure. Actual comments will be added through the application.

-- ============================================================================
-- Verification Queries
-- ============================================================================

-- Check if tables were created successfully
SELECT 'tija_leave_documents table created' AS Status
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_name = 'tija_leave_documents';

SELECT 'tija_leave_approval_comments table created' AS Status
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_name = 'tija_leave_approval_comments';

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================
-- Tables created:
--   1. tija_leave_documents
--   2. tija_leave_approval_comments
--
-- Next Steps:
--   1. Test document upload functionality
--   2. Test comment/approval workflow
--   3. Verify file paths are correct
-- ============================================================================

