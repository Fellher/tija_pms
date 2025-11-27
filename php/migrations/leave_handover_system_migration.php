<?php
/**
 * Database Migration: Leave Handover System
 *
 * Creates the enterprise handover tables and augments leave applications with
 * the metadata required to track handover progress. Safe to run multiple times.
 */

session_start();
$base = dirname(__DIR__, 1) . '/../';
set_include_path($base);

require_once __DIR__ . '/../includes.php';

header('Content-Type: application/json');

if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be an administrator to run this migration.'
    ]);
    exit;
}

$results = [];
$errors = [];

try {
    /**
     * Helper to execute CREATE TABLE statements with IF NOT EXISTS support.
     */
    $createTable = function($sql, $tableName) use ($DBConn, &$results, &$errors) {
        try {
            $DBConn->query($sql);
            $results[] = "✅ Ensured table `{$tableName}` exists";
        } catch (Exception $e) {
            $errors[] = "❌ Failed creating table `{$tableName}`: " . $e->getMessage();
        }
    };

    /**
     * Helper to add missing columns.
     */
    $ensureColumn = function($table, $column, $definition, $position = '') use ($DBConn, &$results, &$errors) {
        try {
            $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND COLUMN_NAME = ?";
            $check = $DBConn->fetch_all_rows($sql, [
                array($table, 's'),
                array($column, 's')
            ]);

            $exists = false;
            if ($check && isset($check[0])) {
                $row = $check[0];
                $count = is_object($row) ? ($row->count ?? 0) : ($row['count'] ?? 0);
                $exists = (int)$count > 0;
            }
            if ($exists) {
                $results[] = "⏭️ Column `{$column}` already exists on `{$table}`";
                return;
            }

            $alter = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition} {$position}";
            $DBConn->query($alter);
            $results[] = "✅ Added column `{$column}` to `{$table}`";
        } catch (Exception $e) {
            $errors[] = "❌ Failed adding column `{$column}` to `{$table}`: " . $e->getMessage();
        }
    };

    /**
     * Helper to ensure indexes exist.
     */
    $ensureIndex = function($table, $indexName, $definition) use ($DBConn, &$results, &$errors) {
        try {
            $sql = "SELECT COUNT(*) AS count FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND INDEX_NAME = ?";
            $check = $DBConn->fetch_all_rows($sql, [
                array($table, 's'),
                array($indexName, 's')
            ]);

            $exists = false;
            if ($check && isset($check[0])) {
                $row = $check[0];
                $count = is_object($row) ? ($row->count ?? 0) : ($row['count'] ?? 0);
                $exists = (int)$count > 0;
            }
            if ($exists) {
                $results[] = "⏭️ Index `{$indexName}` already exists on `{$table}`";
                return;
            }

            $DBConn->query($definition);
            $results[] = "✅ Created index `{$indexName}` on `{$table}`";
        } catch (Exception $e) {
            $errors[] = "❌ Failed creating index `{$indexName}` on `{$table}`: " . $e->getMessage();
        }
    };

    // ---------------------------------------------------------------------
    // 1. Core handover tables
    // ---------------------------------------------------------------------
    $createTable("
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
    ", 'tija_leave_handovers');

    $createTable("
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
    ", 'tija_leave_handover_items');

    $createTable("
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
    ", 'tija_leave_handover_assignments');

    $createTable("
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
    ", 'tija_leave_handover_confirmations');

    $createTable("
        CREATE TABLE IF NOT EXISTS `tija_leave_handover_policies` (
            `policyID` INT NOT NULL AUTO_INCREMENT,
            `entityID` INT NOT NULL,
            `orgDataID` INT DEFAULT NULL,
            `leaveTypeID` INT DEFAULT NULL,
            `isMandatory` ENUM('Y','N') NOT NULL DEFAULT 'N',
            `minHandoverDays` INT NOT NULL DEFAULT 0,
            `requireConfirmation` ENUM('Y','N') NOT NULL DEFAULT 'Y',
            `requireTraining` ENUM('Y','N') NOT NULL DEFAULT 'N',
            `requireCredentials` ENUM('Y','N') NOT NULL DEFAULT 'N',
            `requireTools` ENUM('Y','N') NOT NULL DEFAULT 'N',
            `requireDocuments` ENUM('Y','N') NOT NULL DEFAULT 'N',
            `allowProjectIntegration` ENUM('Y','N') NOT NULL DEFAULT 'N',
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
    ", 'tija_leave_handover_policies');

    // ---------------------------------------------------------------------
    // 1.1 Enhance Policy Table with targeting columns
    // ---------------------------------------------------------------------
    $ensureColumn(
        'tija_leave_handover_policies',
        'policyScope',
        "ENUM('entity_wide', 'role_based', 'job_group', 'job_level', 'job_title') NOT NULL DEFAULT 'entity_wide' COMMENT 'Scope of policy targeting'",
        "AFTER `leaveTypeID`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'targetRoleID',
        "INT DEFAULT NULL COMMENT 'Target role ID for role-based policies'",
        "AFTER `policyScope`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'targetJobCategoryID',
        "INT DEFAULT NULL COMMENT 'Target job category ID for job group policies'",
        "AFTER `targetRoleID`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'targetJobBandID',
        "INT DEFAULT NULL COMMENT 'Target job band ID for job group policies'",
        "AFTER `targetJobCategoryID`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'targetJobLevelID',
        "INT DEFAULT NULL COMMENT 'Target job level ID (FK to tija_role_levels)'",
        "AFTER `targetJobBandID`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'targetJobTitleID',
        "INT DEFAULT NULL COMMENT 'Target job title ID (FK to tija_job_titles)'",
        "AFTER `targetJobLevelID`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'requireNomineeAcceptance',
        "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether nominee acceptance is required'",
        "AFTER `targetJobTitleID`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'nomineeResponseDeadlineHours',
        "INT NOT NULL DEFAULT 48 COMMENT 'Hours for nominee to respond'",
        "AFTER `requireNomineeAcceptance`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'allowPeerRevision',
        "ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether peer can request revisions'",
        "AFTER `nomineeResponseDeadlineHours`"
    );

    $ensureColumn(
        'tija_leave_handover_policies',
        'maxRevisionAttempts',
        "INT NOT NULL DEFAULT 3 COMMENT 'Maximum number of revision attempts allowed'",
        "AFTER `allowPeerRevision`"
    );

    // ---------------------------------------------------------------------
    // 1.4 Add columns to existing handover tables
    // ---------------------------------------------------------------------
    $ensureColumn(
        'tija_leave_handovers',
        'nomineeID',
        "INT DEFAULT NULL COMMENT 'Peer/nominee assigned for handover'",
        "AFTER `policyID`"
    );

    $ensureColumn(
        'tija_leave_handovers',
        'fsmStateID',
        "INT DEFAULT NULL COMMENT 'FK to tija_leave_handover_fsm_states'",
        "AFTER `nomineeID`"
    );

    $ensureColumn(
        'tija_leave_handovers',
        'revisionCount',
        "INT NOT NULL DEFAULT 0 COMMENT 'Number of revision attempts'",
        "AFTER `fsmStateID`"
    );

    $ensureColumn(
        'tija_leave_handover_assignments',
        'negotiationID',
        "INT DEFAULT NULL COMMENT 'FK to tija_leave_handover_peer_negotiations'",
        "AFTER `confirmationComments`"
    );

    $ensureColumn(
        'tija_leave_handover_assignments',
        'revisionRequested',
        "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether revision was requested for this assignment'",
        "AFTER `negotiationID`"
    );

    // ---------------------------------------------------------------------
    // 2. Add columns to tija_leave_applications
    // ---------------------------------------------------------------------
    $ensureColumn(
        'tija_leave_applications',
        'handoverRequired',
        "ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether a structured handover is required'",
        "AFTER `handoverNotes`"
    );

    $ensureColumn(
        'tija_leave_applications',
        'handoverStatus',
        "ENUM('not_required','pending','in_progress','completed','partial') NOT NULL DEFAULT 'not_required'",
        "AFTER `handoverRequired`"
    );

    $ensureColumn(
        'tija_leave_applications',
        'handoverCompletedDate',
        "DATETIME DEFAULT NULL COMMENT 'When the handover was fully confirmed'",
        "AFTER `handoverStatus`"
    );

    // ---------------------------------------------------------------------
    // 3. Supporting indexes
    // ---------------------------------------------------------------------
    $ensureIndex(
        'tija_leave_applications',
        'idx_handover_status',
        "CREATE INDEX `idx_handover_status` ON `tija_leave_applications` (`handoverStatus`, `handoverRequired`)"
    );

    $success = empty($errors);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'Leave handover system migration completed successfully.'
            : 'Migration completed with some issues. Review details.',
        'details' => array_merge($results, $errors)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage(),
        'details' => array_merge($results, $errors)
    ]);
}
?>


