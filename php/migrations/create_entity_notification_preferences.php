<?php
/**
 * Database Migration: Entity Notification Preferences
 *
 * Creates the `tija_notification_entity_preferences` table that lets each entity
 * configure which notification channels should be used for every leave event.
 */

session_start();

// Determine base path (script lives in php/migrations/)
$base = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
set_include_path($base);

// Include the main bootstrap file
$includesPath = $base . 'php' . DIRECTORY_SEPARATOR . 'includes.php';
if (file_exists($includesPath)) {
    include $includesPath;
} else {
    $fallbackBase = '../../';
    set_include_path($fallbackBase);
    include 'php/includes.php';
}

header('Content-Type: application/json');

if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be an administrator to run this migration.'
    ]);
    exit;
}

$responses = [];

/**
 * Helper: Check if a table exists in the active schema.
 */
function sanitize_identifier($name) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
}

function table_exists($tableName, $DBConn) {
    $tableName = sanitize_identifier($tableName);
    if (!$tableName) {
        return false;
    }
    $rows = $DBConn->fetch_all_rows(
        "SHOW TABLES LIKE '{$tableName}'",
        array()
    );
    return $rows && count($rows) > 0;
}

/**
 * Helper: Check if a table supports foreign keys (InnoDB).
 */
function table_supports_fk($tableName, $DBConn) {
    $tableName = sanitize_identifier($tableName);
    if (!$tableName) {
        return false;
    }
    $rows = $DBConn->fetch_all_rows(
        "SELECT ENGINE FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tableName}'",
        array()
    );
    if ($rows && count($rows) > 0) {
        $row = is_object($rows[0]) ? (array)$rows[0] : $rows[0];
        return isset($row['ENGINE']) && strtoupper($row['ENGINE']) === 'INNODB';
    }
    return false;
}

/**
 * Helper: Add a foreign key constraint when both tables exist and support FK.
 */
function add_fk_if_possible($constraintName, $column, $refTable, $refColumn, &$responses, $DBConn) {
    $constraintName = sanitize_identifier($constraintName);
    $column = sanitize_identifier($column);
    $refTable = sanitize_identifier($refTable);
    $refColumn = sanitize_identifier($refColumn);

    // Avoid duplicate constraints
    $existing = $DBConn->fetch_all_rows(
        "SELECT CONSTRAINT_NAME
         FROM information_schema.REFERENTIAL_CONSTRAINTS
         WHERE CONSTRAINT_SCHEMA = DATABASE()
           AND CONSTRAINT_NAME = '{$constraintName}'",
        array()
    );
    if ($existing && count($existing) > 0) {
        $responses[] = "ℹ️  Foreign key {$constraintName} already exists";
        return;
    }

    if (!table_exists($refTable, $DBConn)) {
        $responses[] = "⚠️  Skipped {$constraintName}: referenced table {$refTable} not found";
        return;
    }

    if (!table_supports_fk($refTable, $DBConn)) {
        $responses[] = "⚠️  Skipped {$constraintName}: referenced table {$refTable} does not support foreign keys";
        return;
    }

    try {
        $sql = "ALTER TABLE `tija_notification_entity_preferences`
                ADD CONSTRAINT `{$constraintName}`
                FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`)
                ON DELETE CASCADE";
        $DBConn->query($sql);
        $DBConn->execute();
        $responses[] = "✅ Added foreign key {$constraintName}";
    } catch (Exception $e) {
        $responses[] = "⚠️  Failed to add {$constraintName}: " . $e->getMessage();
    }
}

try {
    // Create table if it does not already exist (without FK constraints)
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS `tija_notification_entity_preferences` (
            `entityPreferenceID` INT NOT NULL AUTO_INCREMENT,
            `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `entityID` INT NOT NULL,
            `eventID` INT NOT NULL,
            `channelID` INT NOT NULL,
            `isEnabled` ENUM('Y','N') NOT NULL DEFAULT 'Y',
            `enforceForAllUsers` ENUM('Y','N') NOT NULL DEFAULT 'N',
            `notifyImmediately` ENUM('Y','N') NOT NULL DEFAULT 'Y',
            `notifyDigest` ENUM('Y','N') NOT NULL DEFAULT 'N',
            `digestFrequency` ENUM('none','daily','weekly') NOT NULL DEFAULT 'none',
            `LastUpdate` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `Lapsed` ENUM('N','Y') NOT NULL DEFAULT 'N',
            `Suspended` ENUM('N','Y') NOT NULL DEFAULT 'N',
            PRIMARY KEY (`entityPreferenceID`),
            UNIQUE KEY `unique_entity_event_channel` (`entityID`, `eventID`, `channelID`),
            KEY `idx_entity_pref_entity` (`entityID`),
            KEY `idx_entity_pref_event` (`eventID`),
            KEY `idx_entity_pref_channel` (`channelID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $DBConn->query($createTableSql);
    $DBConn->execute();
    $responses[] = '✅ Ensured tija_notification_entity_preferences table exists';

    // Attempt to add FKs where possible
    add_fk_if_possible('fk_entity_pref_entity', 'entityID', 'tija_entities', 'entityID', $responses, $DBConn);
    add_fk_if_possible('fk_entity_pref_event', 'eventID', 'tija_notification_events', 'eventID', $responses, $DBConn);
    add_fk_if_possible('fk_entity_pref_channel', 'channelID', 'tija_notification_channels', 'channelID', $responses, $DBConn);

    echo json_encode([
        'success' => true,
        'steps' => $responses
    ]);
} catch (Exception $e) {
    error_log('Entity notification preferences migration failed: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage()
    ]);
}

