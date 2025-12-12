-- Migration: Create Sales Case Notes and Next Steps Tables
-- Description: Creates tables for managing notes and next steps for sales cases
-- Author: System
-- Date: 2025-12-12

-- =====================================================
-- Table: tija_sales_case_notes
-- Description: Stores notes related to sales cases
-- =====================================================
CREATE TABLE IF NOT EXISTS `tija_sales_case_notes` (
    `salesCaseNoteID` INT(11) NOT NULL AUTO_INCREMENT,
    `salesCaseID` INT(11) NOT NULL COMMENT 'Foreign key to tija_sales_cases',
    `saleStatusLevelID` INT(11) DEFAULT NULL COMMENT 'Sales stage when note was created',
    `noteText` TEXT NOT NULL COMMENT 'Content of the note',
    `noteType` ENUM('general', 'private') NOT NULL DEFAULT 'general' COMMENT 'General (visible to all) or Private (visible to specific users)',
    `isPrivate` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Y/N - Quick check if note is private',
    `createdByID` INT(11) NOT NULL COMMENT 'User who created the note',
    `targetUserID` INT(11) DEFAULT NULL COMMENT 'If private, the user this note is for',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT(11) DEFAULT NULL,
    `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
    `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
    PRIMARY KEY (`salesCaseNoteID`),
    KEY `idx_sales_case` (`salesCaseID`),
    KEY `idx_created_by` (`createdByID`),
    KEY `idx_target_user` (`targetUserID`),
    KEY `idx_stage` (`saleStatusLevelID`),
    KEY `idx_note_type` (`noteType`),
    KEY `idx_date_added` (`DateAdded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sales case notes with general/private visibility';

-- =====================================================
-- Table: tija_sales_case_next_steps
-- Description: Stores next steps/action items for sales cases
-- =====================================================
CREATE TABLE IF NOT EXISTS `tija_sales_case_next_steps` (
    `salesCaseNextStepID` INT(11) NOT NULL AUTO_INCREMENT,
    `salesCaseID` INT(11) NOT NULL COMMENT 'Foreign key to tija_sales_cases',
    `saleStatusLevelID` INT(11) DEFAULT NULL COMMENT 'Sales stage when next step was created',
    `nextStepDescription` TEXT NOT NULL COMMENT 'Description of the next step/action',
    `dueDate` DATE DEFAULT NULL COMMENT 'When this step should be completed',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' COMMENT 'Priority level',
    `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Current status',
    `assignedToID` INT(11) DEFAULT NULL COMMENT 'User assigned to complete this step',
    `completedDate` DATETIME DEFAULT NULL COMMENT 'When the step was completed',
    `completedByID` INT(11) DEFAULT NULL COMMENT 'User who completed the step',
    `createdByID` INT(11) NOT NULL COMMENT 'User who created the next step',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT(11) DEFAULT NULL,
    `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
    `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
    PRIMARY KEY (`salesCaseNextStepID`),
    KEY `idx_sales_case` (`salesCaseID`),
    KEY `idx_created_by` (`createdByID`),
    KEY `idx_assigned_to` (`assignedToID`),
    KEY `idx_stage` (`saleStatusLevelID`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_due_date` (`dueDate`),
    KEY `idx_date_added` (`DateAdded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sales case next steps and action items';

-- =====================================================
-- Table: tija_sales_case_note_recipients
-- Description: Links private notes to their intended recipients
-- =====================================================
CREATE TABLE IF NOT EXISTS `tija_sales_case_note_recipients` (
    `salesCaseNoteRecipientID` INT(11) NOT NULL AUTO_INCREMENT,
    `salesCaseNoteID` INT(11) NOT NULL COMMENT 'Foreign key to tija_sales_case_notes',
    `recipientUserID` INT(11) NOT NULL COMMENT 'User who can view this private note',
    `hasRead` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Y/N - Has the recipient read the note',
    `readDate` DATETIME DEFAULT NULL COMMENT 'When the note was read',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`salesCaseNoteRecipientID`),
    UNIQUE KEY `unique_note_recipient` (`salesCaseNoteID`, `recipientUserID`),
    KEY `idx_note` (`salesCaseNoteID`),
    KEY `idx_recipient` (`recipientUserID`),
    KEY `idx_has_read` (`hasRead`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recipients for private sales case notes';

-- =====================================================
-- Add Foreign Key Constraints (Optional - uncomment if needed)
-- =====================================================
/*
ALTER TABLE `tija_sales_case_notes`
    ADD CONSTRAINT `fk_sales_case_notes_case` FOREIGN KEY (`salesCaseID`) REFERENCES `tija_sales_cases` (`salesCaseID`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_sales_case_notes_stage` FOREIGN KEY (`saleStatusLevelID`) REFERENCES `tija_sales_status_levels` (`saleStatusLevelID`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_sales_case_notes_creator` FOREIGN KEY (`createdByID`) REFERENCES `sbsl_users` (`ID`) ON DELETE RESTRICT;

ALTER TABLE `tija_sales_case_next_steps`
    ADD CONSTRAINT `fk_sales_case_steps_case` FOREIGN KEY (`salesCaseID`) REFERENCES `tija_sales_cases` (`salesCaseID`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_sales_case_steps_stage` FOREIGN KEY (`saleStatusLevelID`) REFERENCES `tija_sales_status_levels` (`saleStatusLevelID`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_sales_case_steps_creator` FOREIGN KEY (`createdByID`) REFERENCES `sbsl_users` (`ID`) ON DELETE RESTRICT,
    ADD CONSTRAINT `fk_sales_case_steps_assigned` FOREIGN KEY (`assignedToID`) REFERENCES `sbsl_users` (`ID`) ON DELETE SET NULL;

ALTER TABLE `tija_sales_case_note_recipients`
    ADD CONSTRAINT `fk_note_recipients_note` FOREIGN KEY (`salesCaseNoteID`) REFERENCES `tija_sales_case_notes` (`salesCaseNoteID`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_note_recipients_user` FOREIGN KEY (`recipientUserID`) REFERENCES `sbsl_users` (`ID`) ON DELETE CASCADE;
*/

-- =====================================================
-- Sample Data (Optional - for testing)
-- =====================================================
/*
-- Example: General note
INSERT INTO `tija_sales_case_notes` (`salesCaseID`, `saleStatusLevelID`, `noteText`, `noteType`, `isPrivate`, `createdByID`)
VALUES (1, 1, 'Initial contact made with decision maker. Very interested in our solution.', 'general', 'N', 1);

-- Example: Private note
INSERT INTO `tija_sales_case_notes` (`salesCaseID`, `saleStatusLevelID`, `noteText`, `noteType`, `isPrivate`, `createdByID`, `targetUserID`)
VALUES (1, 2, 'Client mentioned budget concerns. May need to adjust pricing.', 'private', 'Y', 1, 2);

-- Example: Next step
INSERT INTO `tija_sales_case_next_steps` (`salesCaseID`, `saleStatusLevelID`, `nextStepDescription`, `dueDate`, `priority`, `assignedToID`, `createdByID`)
VALUES (1, 1, 'Send proposal document and pricing breakdown', '2025-12-15', 'high', 2, 1);
*/
