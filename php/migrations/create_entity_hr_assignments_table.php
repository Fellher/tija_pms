<?php
/**
 * Migration: Create HR assignment table for entities
 *
 * Provides storage for primary and substitute HR managers per entity.
 */

session_start();
$base = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
set_include_path($base);

$includesPath = $base . 'php' . DIRECTORY_SEPARATOR . 'includes.php';
if (file_exists($includesPath)) {
    include $includesPath;
} else {
    set_include_path('../../');
    include 'php/includes.php';
}

header('Content-Type: application/json');

if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin && !$isTenantAdmin) {
    echo json_encode([
        'success' => false,
        'message' => 'Administrator privileges required to run this migration.'
    ]);
    exit;
}

$messages = [];

try {
    $createSql = "
        CREATE TABLE IF NOT EXISTS `tija_entity_hr_assignments` (
            `assignmentID` INT NOT NULL AUTO_INCREMENT,
            `entityID` INT NOT NULL,
            `userID` INT NOT NULL,
            `roleType` ENUM('primary','substitute') NOT NULL DEFAULT 'primary',
            `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `LastUpdate` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `LastUpdateByID` INT DEFAULT NULL,
            `Lapsed` ENUM('N','Y') NOT NULL DEFAULT 'N',
            `Suspended` ENUM('N','Y') NOT NULL DEFAULT 'N',
            PRIMARY KEY (`assignmentID`),
            UNIQUE KEY `unique_entity_role` (`entityID`, `roleType`),
            UNIQUE KEY `unique_entity_user` (`entityID`, `userID`),
            KEY `idx_assignment_entity` (`entityID`),
            KEY `idx_assignment_user` (`userID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $DBConn->query($createSql);
    $DBConn->execute();
    $messages[] = '✅ Ensured tija_entity_hr_assignments table exists.';

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
} catch (Exception $e) {
    error_log('create_entity_hr_assignments_table migration failed: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage()
    ]);
}

