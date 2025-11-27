<?php
/**
 * Database Migration: Leave Handover FSM States Table
 *
 * Creates the FSM state tracking table for the enterprise handover protocol.
 * Safe to run multiple times.
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

    // ---------------------------------------------------------------------
    // Create FSM State Tracking Table
    // ---------------------------------------------------------------------
    $createTable("
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
    ", 'tija_leave_handover_fsm_states');

    $success = empty($errors);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'FSM states table migration completed successfully.'
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

