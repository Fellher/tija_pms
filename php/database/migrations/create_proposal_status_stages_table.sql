-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create/Update Proposal Status Stages System
-- Purpose: Enterprise-level proposal status tracking with stages
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Add status stage fields to proposals table if they don't exist
-- Note: MySQL doesn't support IF NOT EXISTS for ADD COLUMN, so we check first

-- Check and add completionPercentage
SET @dbname = DATABASE();
SET @tablename = 'tija_proposals';
SET @columnname = 'completionPercentage';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(5,2) DEFAULT 0.00 COMMENT ''Total completion percentage'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add mandatoryCompletionPercentage
SET @columnname = 'mandatoryCompletionPercentage';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(5,2) DEFAULT 0.00 COMMENT ''Mandatory items completion percentage'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add statusStage
SET @columnname = 'statusStage';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` VARCHAR(50) DEFAULT ''draft'' COMMENT ''Current stage: draft, in_review, submitted, won, lost, archived'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add statusStageOrder
SET @columnname = 'statusStageOrder';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` INT DEFAULT 1 COMMENT ''Order of current stage'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add lastStatusChangeDate
SET @columnname = 'lastStatusChangeDate';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DATETIME NULL COMMENT ''Date of last status change'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add lastStatusChangedBy
SET @columnname = 'lastStatusChangedBy';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` INT NULL COMMENT ''User who changed status last'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Create proposal status stages reference table
CREATE TABLE IF NOT EXISTS `tija_proposal_status_stages` (
    `stageID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stageCode` VARCHAR(50) NOT NULL UNIQUE COMMENT 'draft, in_review, submitted, won, lost, archived',
    `stageName` VARCHAR(100) NOT NULL COMMENT 'Display name',
    `stageDescription` TEXT COMMENT 'Stage description',
    `stageOrder` INT NOT NULL COMMENT 'Order for display',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `requiresApproval` ENUM('Y','N') DEFAULT 'N' COMMENT 'Requires approval to move to this stage',
    `canEdit` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Can edit proposal in this stage',
    `colorCode` VARCHAR(20) DEFAULT '#007bff' COMMENT 'Color for UI display',
    `iconClass` VARCHAR(50) DEFAULT 'ri-file-line' COMMENT 'Icon class',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_stage_code` (`stageCode`),
    INDEX `idx_stage_order` (`stageOrder`),
    INDEX `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Proposal status stages reference table';

-- Insert default proposal stages
INSERT INTO `tija_proposal_status_stages` (`stageCode`, `stageName`, `stageDescription`, `stageOrder`, `isActive`, `requiresApproval`, `canEdit`, `colorCode`, `iconClass`) VALUES
('draft', 'Draft', 'Proposal is being prepared and edited', 1, 'Y', 'N', 'Y', '#6c757d', 'ri-edit-box-line'),
('in_review', 'In Review', 'Proposal is under internal review', 2, 'Y', 'N', 'Y', '#0dcaf0', 'ri-eye-line'),
('submitted', 'Submitted', 'Proposal has been submitted to client', 3, 'Y', 'Y', 'N', '#0d6efd', 'ri-send-plane-line'),
('won', 'Won', 'Proposal was accepted by client', 4, 'Y', 'Y', 'N', '#198754', 'ri-checkbox-circle-line'),
('lost', 'Lost', 'Proposal was rejected or lost', 5, 'Y', 'Y', 'N', '#dc3545', 'ri-close-circle-line'),
('archived', 'Archived', 'Proposal has been archived', 6, 'Y', 'N', 'N', '#6c757d', 'ri-archive-line')
ON DUPLICATE KEY UPDATE `stageName`=VALUES(`stageName`);

-- Create proposal tasks table (if not exists)
CREATE TABLE IF NOT EXISTS `tija_proposal_tasks` (
    `proposalTaskID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `proposalID` INT NOT NULL COMMENT 'FK to tija_proposals',
    `taskName` VARCHAR(255) NOT NULL COMMENT 'Task name',
    `taskDescription` TEXT COMMENT 'Task description',
    `assignedTo` INT NOT NULL COMMENT 'FK to people - assigned user',
    `assignedBy` INT NOT NULL COMMENT 'FK to people - who assigned',
    `dueDate` DATETIME NOT NULL COMMENT 'Task due date',
    `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `status` ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
    `completionPercentage` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Task completion percentage',
    `isMandatory` ENUM('Y','N') DEFAULT 'N' COMMENT 'Is this a mandatory task',
    `completedDate` DATETIME NULL COMMENT 'Date when task was completed',
    `completedBy` INT NULL COMMENT 'FK to people - who completed',
    `notificationSent` ENUM('Y','N') DEFAULT 'N' COMMENT 'Notification sent flag',
    `notificationSentDate` DATETIME NULL COMMENT 'When notification was sent',
    `orgDataID` INT NULL,
    `entityID` INT NULL,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_proposal` (`proposalID`),
    INDEX `idx_assigned_to` (`assignedTo`),
    INDEX `idx_status` (`status`),
    INDEX `idx_mandatory` (`isMandatory`),
    INDEX `idx_due_date` (`dueDate`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Proposal tasks for tracking individual tasks within proposals';

-- Create proposal checklist item submissions table (enhancement)
CREATE TABLE IF NOT EXISTS `tija_proposal_checklist_item_submissions` (
    `submissionID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `proposalChecklistItemAssignmentID` INT NOT NULL COMMENT 'FK to tija_proposal_checklist_item_assignment',
    `submittedBy` INT NOT NULL COMMENT 'FK to people - who submitted',
    `submissionDate` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'When submitted',
    `submissionStatus` ENUM('draft','submitted','approved','rejected','revision_requested') DEFAULT 'submitted',
    `submissionNotes` TEXT COMMENT 'Submission notes or comments',
    `reviewedBy` INT NULL COMMENT 'FK to people - who reviewed',
    `reviewedDate` DATETIME NULL COMMENT 'When reviewed',
    `reviewNotes` TEXT COMMENT 'Review comments',
    `submissionFiles` TEXT COMMENT 'JSON array of submitted file paths',
    `orgDataID` INT NULL,
    `entityID` INT NULL,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_assignment` (`proposalChecklistItemAssignmentID`),
    INDEX `idx_submitted_by` (`submittedBy`),
    INDEX `idx_status` (`submissionStatus`),
    INDEX `idx_reviewed_by` (`reviewedBy`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Submissions for proposal checklist item assignments';

-- Add mandatory flag to checklist items if not exists
SET @tablename = 'tija_proposal_checklist_items';
SET @columnname = 'isMandatory';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` ENUM(''Y'',''N'') DEFAULT ''N'' COMMENT ''Is this a mandatory checklist item'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add mandatory flag and other fields to checklist item assignments if not exists
SET @tablename = 'tija_proposal_checklist_item_assignment';

-- Add isMandatory
SET @columnname = 'isMandatory';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` ENUM(''Y'',''N'') DEFAULT ''N'' COMMENT ''Is this assignment mandatory'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add completionPercentage
SET @columnname = 'completionPercentage';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(5,2) DEFAULT 0.00 COMMENT ''Assignment completion percentage'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add submittedDate
SET @columnname = 'submittedDate';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DATETIME NULL COMMENT ''When assignment was submitted'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add approvedDate
SET @columnname = 'approvedDate';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DATETIME NULL COMMENT ''When assignment was approved'';')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

