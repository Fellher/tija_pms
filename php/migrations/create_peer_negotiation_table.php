<?php
/**
 * Database Migration: Leave Handover Peer Negotiations Table
 *
 * Creates the peer negotiation table for the enterprise handover protocol.
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
    // Create Peer Negotiation Table
    // ---------------------------------------------------------------------
    $createTable("
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
    ", 'tija_leave_handover_peer_negotiations');

    $success = empty($errors);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'Peer negotiation table migration completed successfully.'
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

