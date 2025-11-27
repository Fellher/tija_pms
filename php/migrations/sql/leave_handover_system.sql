/*
 * Unified SQL Script: Enterprise Leave Handover System
 *
 * This script consolidates all schema requirements for the handover workflow,
 * including the tables introduced in:
 *   - php/migrations/leave_handover_system_migration.php
 *   - php/migrations/create_handover_fsm_states_table.php
 *   - php/migrations/create_peer_negotiation_table.php
 *
 * Execute on a MySQL 8.x instance with appropriate privileges.
 */

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- Core handover tables
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tija_leave_handovers` (
    `handoverID` INT NOT NULL AUTO_INCREMENT,
    `leaveApplicationID` INT NOT NULL,
    `employeeID` INT NOT NULL,
    `entityID` INT NOT NULL,
    `orgDataID` INT NOT NULL,
    `policyID` INT DEFAULT NULL,
    `handoverStatus` ENUM('pending','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
    `handoverDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completionDate` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
    PRIMARY KEY (`handoverID`),
    KEY `idx_handover_application` (`leaveApplicationID`),
    KEY `idx_handover_employee` (`employeeID`),
    KEY `idx_handover_status` (`handoverStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tija_leave_handover_items` (
    `handoverItemID` INT NOT NULL AUTO_INCREMENT,
    `handoverID` INT NOT NULL,
    `itemType` ENUM('project_task','function','duty','other') NOT NULL DEFAULT 'other',
    `itemTitle` VARCHAR(255) NOT NULL,
    `itemDescription` TEXT DEFAULT NULL,
    `projectID` INT DEFAULT NULL,
    `taskID` INT DEFAULT NULL,
    `priority` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    `dueDate` DATE DEFAULT NULL,
    `instructions` TEXT DEFAULT NULL,
    `isMandatory` ENUM('Y','N') NOT NULL DEFAULT 'Y',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
    PRIMARY KEY (`handoverItemID`),
    KEY `idx_item_handover` (`handoverID`),
    KEY `idx_item_type` (`itemType`),
    KEY `idx_item_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tija_leave_handover_assignments` (
    `assignmentID` INT NOT NULL AUTO_INCREMENT,
    `handoverID` INT NOT NULL,
    `handoverItemID` INT DEFAULT NULL,
    `assignedToID` INT NOT NULL,
    `assignedByID` INT NOT NULL,
    `assignmentDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `confirmationStatus` ENUM('pending','acknowledged','confirmed','rejected') NOT NULL DEFAULT 'pending',
    `confirmedDate` DATETIME DEFAULT NULL,
    `confirmationComments` TEXT DEFAULT NULL,
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
    PRIMARY KEY (`assignmentID`),
    KEY `idx_assignment_handover` (`handoverID`),
    KEY `idx_assignment_item` (`handoverItemID`),
    KEY `idx_assignment_assignee` (`assignedToID`),
    KEY `idx_assignment_status` (`confirmationStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tija_leave_handover_confirmations` (
    `confirmationID` INT NOT NULL AUTO_INCREMENT,
    `assignmentID` INT NOT NULL,
    `handoverItemID` INT DEFAULT NULL,
    `briefed` ENUM('Y','N','not_required') NOT NULL DEFAULT 'Y',
    `briefedDate` DATETIME DEFAULT NULL,
    `trained` ENUM('Y','N','not_required') NOT NULL DEFAULT 'not_required',
    `trainedDate` DATETIME DEFAULT NULL,
    `hasCredentials` ENUM('Y','N','not_required') NOT NULL DEFAULT 'not_required',
    `credentialsDetails` TEXT DEFAULT NULL,
    `hasTools` ENUM('Y','N','not_required') NOT NULL DEFAULT 'not_required',
    `toolsDetails` TEXT DEFAULT NULL,
    `hasDocuments` ENUM('Y','N','not_required') NOT NULL DEFAULT 'not_required',
    `documentsDetails` TEXT DEFAULT NULL,
    `readyToTakeOver` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `additionalNotes` TEXT DEFAULT NULL,
    `confirmedByID` INT NOT NULL,
    `confirmedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
    PRIMARY KEY (`confirmationID`),
    KEY `idx_confirmation_assignment` (`assignmentID`),
    KEY `idx_confirmation_item` (`handoverItemID`),
    KEY `idx_confirmation_ready` (`readyToTakeOver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tija_leave_handover_policies` (
    `policyID` INT NOT NULL AUTO_INCREMENT,
    `entityID` INT NOT NULL,
    `orgDataID` INT DEFAULT NULL,
    `leaveTypeID` INT DEFAULT NULL,
    `policyScope` ENUM('entity_wide','role_based','job_group','job_level','job_title') NOT NULL DEFAULT 'entity_wide',
    `targetRoleID` INT DEFAULT NULL,
    `targetJobCategoryID` INT DEFAULT NULL,
    `targetJobBandID` INT DEFAULT NULL,
    `targetJobLevelID` INT DEFAULT NULL,
    `targetJobTitleID` INT DEFAULT NULL,
    `isMandatory` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `minHandoverDays` INT NOT NULL DEFAULT 0,
    `requireConfirmation` ENUM('Y','N') NOT NULL DEFAULT 'Y',
    `requireTraining` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `requireCredentials` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `requireTools` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `requireDocuments` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `allowProjectIntegration` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `requireNomineeAcceptance` ENUM('Y','N') NOT NULL DEFAULT 'Y',
    `nomineeResponseDeadlineHours` INT NOT NULL DEFAULT 48,
    `allowPeerRevision` ENUM('Y','N') NOT NULL DEFAULT 'Y',
    `maxRevisionAttempts` INT NOT NULL DEFAULT 3,
    `effectiveDate` DATE NOT NULL DEFAULT '1970-01-01',
    `expiryDate` DATE DEFAULT NULL,
    `policyName` VARCHAR(255) DEFAULT NULL,
    `policyDescription` TEXT DEFAULT NULL,
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
    `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
    PRIMARY KEY (`policyID`),
    KEY `idx_policy_entity` (`entityID`),
    KEY `idx_policy_leave_type` (`leaveTypeID`),
    KEY `idx_policy_effective` (`effectiveDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- FSM state tracking table
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tija_leave_handover_fsm_states` (
    `stateID` INT NOT NULL AUTO_INCREMENT,
    `leaveApplicationID` INT NOT NULL,
    `handoverID` INT DEFAULT NULL,
    `currentState` ENUM('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') NOT NULL,
    `previousState` ENUM('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') DEFAULT NULL,
    `stateOwnerID` INT DEFAULT NULL COMMENT 'Employee ID who owns current state',
    `nomineeID` INT DEFAULT NULL COMMENT 'Peer/nominee assigned for handover',
    `stateEnteredAt` DATETIME NOT NULL,
    `stateCompletedAt` DATETIME DEFAULT NULL,
    `timerStartedAt` DATETIME DEFAULT NULL COMMENT 'For peer response deadlines',
    `timerExpiresAt` DATETIME DEFAULT NULL,
    `revisionCount` INT NOT NULL DEFAULT 0,
    `chainOfCustodyLog` TEXT DEFAULT NULL COMMENT 'JSON log of state transitions',
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`stateID`),
    KEY `idx_application` (`leaveApplicationID`),
    KEY `idx_handover` (`handoverID`),
    KEY `idx_current_state` (`currentState`),
    KEY `idx_nominee` (`nomineeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Peer negotiation table
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tija_leave_handover_peer_negotiations` (
    `negotiationID` INT NOT NULL AUTO_INCREMENT,
    `handoverID` INT NOT NULL,
    `assignmentID` INT DEFAULT NULL,
    `nomineeID` INT NOT NULL,
    `requesterID` INT NOT NULL,
    `negotiationType` ENUM('request_change','reject','accept') NOT NULL,
    `requestedChanges` TEXT DEFAULT NULL COMMENT 'Details of what needs to be changed',
    `negotiationStatus` ENUM('pending','resolved','escalated') NOT NULL DEFAULT 'pending',
    `responseDate` DATETIME DEFAULT NULL,
    `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`negotiationID`),
    KEY `idx_handover` (`handoverID`),
    KEY `idx_nominee` (`nomineeID`),
    KEY `idx_assignment` (`assignmentID`),
    KEY `idx_status` (`negotiationStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Alterations to existing tables (idempotent)
-- ---------------------------------------------------------------------------
SET @current_db := DATABASE();

DELIMITER //
DROP PROCEDURE IF EXISTS add_column_if_missing//
CREATE PROCEDURE add_column_if_missing(
    IN tbl VARCHAR(64),
    IN col VARCHAR(64),
    IN definition TEXT,
    IN afterCol VARCHAR(64)
)
BEGIN
    DECLARE cnt INT DEFAULT 0;
    SELECT COUNT(*) INTO cnt
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @current_db
        AND TABLE_NAME = tbl
        AND COLUMN_NAME = col;

    IF cnt = 0 THEN
        IF afterCol IS NULL OR afterCol = '' THEN
            SET @ddl := CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', definition);
        ELSE
            SET @ddl := CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', definition, ' AFTER `', afterCol, '`');
        END IF;
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

DROP PROCEDURE IF EXISTS ensure_index_exists//
CREATE PROCEDURE ensure_index_exists(
    IN tbl VARCHAR(64),
    IN idx VARCHAR(64),
    IN ddl TEXT
)
BEGIN
    DECLARE cnt INT DEFAULT 0;
    SELECT COUNT(*) INTO cnt
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @current_db
        AND TABLE_NAME = tbl
        AND INDEX_NAME = idx;

    IF cnt = 0 THEN
        SET @ddl := ddl;
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//
DELIMITER ;

CALL add_column_if_missing('tija_leave_handovers','nomineeID','INT DEFAULT NULL COMMENT ''Peer/nominee assigned for handover''','policyID');
CALL add_column_if_missing('tija_leave_handovers','fsmStateID','INT DEFAULT NULL COMMENT ''FK to tija_leave_handover_fsm_states''','nomineeID');
CALL add_column_if_missing('tija_leave_handovers','revisionCount','INT NOT NULL DEFAULT 0 COMMENT ''Number of revision attempts''','fsmStateID');

CALL add_column_if_missing('tija_leave_handover_assignments','negotiationID','INT DEFAULT NULL COMMENT ''FK to tija_leave_handover_peer_negotiations''','confirmationComments');
CALL add_column_if_missing('tija_leave_handover_assignments','revisionRequested','ENUM(''Y'',''N'') NOT NULL DEFAULT ''N'' COMMENT ''Whether revision was requested for this assignment''','negotiationID');

CALL add_column_if_missing('tija_leave_applications','handoverRequired','ENUM(''Y'',''N'') NOT NULL DEFAULT ''N'' COMMENT ''Whether a structured handover is required''','handoverNotes');
CALL add_column_if_missing('tija_leave_applications','handoverStatus','ENUM(''not_required'',''pending'',''in_progress'',''completed'',''partial'') NOT NULL DEFAULT ''not_required''','handoverRequired');
CALL add_column_if_missing('tija_leave_applications','handoverCompletedDate','DATETIME DEFAULT NULL COMMENT ''When the handover was fully confirmed''','handoverStatus');

CALL add_column_if_missing('tija_leave_handover_policies','policyScope','ENUM(''entity_wide'',''role_based'',''job_group'',''job_level'',''job_title'') NOT NULL DEFAULT ''entity_wide'' COMMENT ''Scope of policy targeting''','leaveTypeID');
CALL add_column_if_missing('tija_leave_handover_policies','targetRoleID','INT DEFAULT NULL COMMENT ''Target role ID for role-based policies''','policyScope');
CALL add_column_if_missing('tija_leave_handover_policies','targetJobCategoryID','INT DEFAULT NULL COMMENT ''Target job category ID for job group policies''','targetRoleID');
CALL add_column_if_missing('tija_leave_handover_policies','targetJobBandID','INT DEFAULT NULL COMMENT ''Target job band ID for job group policies''','targetJobCategoryID');
CALL add_column_if_missing('tija_leave_handover_policies','targetJobLevelID','INT DEFAULT NULL COMMENT ''Target job level ID (FK to tija_role_levels)''','targetJobBandID');
CALL add_column_if_missing('tija_leave_handover_policies','targetJobTitleID','INT DEFAULT NULL COMMENT ''Target job title ID (FK to tija_job_titles)''','targetJobLevelID');
CALL add_column_if_missing('tija_leave_handover_policies','requireNomineeAcceptance','ENUM(''Y'',''N'') NOT NULL DEFAULT ''Y'' COMMENT ''Whether nominee acceptance is required''','targetJobTitleID');
CALL add_column_if_missing('tija_leave_handover_policies','nomineeResponseDeadlineHours','INT NOT NULL DEFAULT 48 COMMENT ''Hours for nominee to respond''','requireNomineeAcceptance');
CALL add_column_if_missing('tija_leave_handover_policies','allowPeerRevision','ENUM(''Y'',''N'') NOT NULL DEFAULT ''Y'' COMMENT ''Whether peer can request revisions''','nomineeResponseDeadlineHours');
CALL add_column_if_missing('tija_leave_handover_policies','maxRevisionAttempts','INT NOT NULL DEFAULT 3 COMMENT ''Maximum number of revision attempts allowed''','allowPeerRevision');

CALL ensure_index_exists(
    'tija_leave_applications',
    'idx_handover_status',
    'ALTER TABLE `tija_leave_applications` ADD INDEX `idx_handover_status` (`handoverStatus`, `handoverRequired`)'
);

DROP PROCEDURE IF EXISTS add_column_if_missing;
DROP PROCEDURE IF EXISTS ensure_index_exists;

COMMIT;

