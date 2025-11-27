<?php
/**
 * Migration Helper: Seed tija_entity_hr_assignments with existing HR managers
 *
 * Finds all employees flagged with user_details.isHRManager = 'Y' and inserts
 * them into the new assignment table (primary by default, substitute when
 * primary already exists). Keeps a dry-run option for safety.
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin && !$isTenantAdmin) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin-level access required.'
    ]);
    exit;
}

$dryRun = isset($_POST['dryRun']) && $_POST['dryRun'] === '1';
$confirmToken = isset($_POST['confirmMigrate']) ? trim($_POST['confirmMigrate']) : '';

if ($confirmToken !== 'MIGRATE_HR_MANAGERS') {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid confirmation token (expected confirmMigrate=MIGRATE_HR_MANAGERS).'
    ]);
    exit;
}

function table_exists_local($tableName, $DBConn) {
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tableName);
    if (!$tableName) {
        return false;
    }
    $rows = $DBConn->fetch_all_rows("SHOW TABLES LIKE '{$tableName}'", array());
    return $rows && count($rows) > 0;
}

$messages = [];
$warnings = [];

if (!table_exists_local('tija_entity_hr_assignments', $DBConn)) {
    echo json_encode([
        'success' => false,
        'message' => 'Target table tija_entity_hr_assignments not found. Run create_entity_hr_assignments_table migration first.'
    ]);
    exit;
}

try {
    $legacyRows = $DBConn->fetch_all_rows(
        "SELECT ud.ID, ud.entityID, ud.orgDataID,
                CONCAT(p.FirstName, ' ', p.Surname) AS employeeName,
                p.Email
         FROM user_details ud
         LEFT JOIN people p ON ud.ID = p.ID
         WHERE ud.isHRManager = 'Y'
           AND ud.entityID IS NOT NULL
           AND ud.entityID <> ''
           AND ud.Lapsed = 'N'
           AND ud.Suspended = 'N'",
        array()
    );

    if (!$legacyRows || count($legacyRows) === 0) {
        echo json_encode([
            'success' => true,
            'dryRun' => $dryRun,
            'messages' => ['No legacy HR managers found. Nothing to migrate.']
        ]);
        exit;
    }

    $insertCount = 0;
    $updateCount = 0;
    $skippedCount = 0;

    foreach ($legacyRows as $legacy) {
        $legacy = is_object($legacy) ? (array)$legacy : $legacy;
        $userID = (int)($legacy['ID'] ?? 0);
        $entityID = (int)($legacy['entityID'] ?? 0);

        if ($userID === 0 || $entityID === 0) {
            $warnings[] = "Skipping user with invalid entity reference: userID {$legacy['ID']}";
            $skippedCount++;
            continue;
        }

        $existingRole = $DBConn->fetch_all_rows(
            "SELECT roleType FROM tija_entity_hr_assignments WHERE entityID = ? AND userID = ? LIMIT 1",
            array(array($entityID, 'i'), array($userID, 'i'))
        );

        if ($existingRole && count($existingRole) > 0) {
            $warnings[] = "User {$userID} already has HR assignment ({$existingRole[0]->roleType}). Skipping.";
            $skippedCount++;
            continue;
        }

        $currentPrimary = $DBConn->fetch_all_rows(
            "SELECT userID FROM tija_entity_hr_assignments
             WHERE entityID = ? AND roleType = 'primary'
             LIMIT 1",
            array(array($entityID, 'i'))
        );

        $roleToAssign = $currentPrimary && count($currentPrimary) > 0 ? 'substitute' : 'primary';

        if ($dryRun) {
            $messages[] = "[Dry Run] Would assign user {$userID} as {$roleToAssign} HR manager for entity {$entityID}.";
            $insertCount++;
        } else {
            $insertResult = $DBConn->insert_data('tija_entity_hr_assignments', array(
                'entityID' => $entityID,
                'userID' => $userID,
                'roleType' => $roleToAssign,
                'DateAdded' => date('Y-m-d H:i:s'),
                'LastUpdateByID' => $userDetails->ID ?? null,
                'Lapsed' => 'N',
                'Suspended' => 'N'
            ));

            if ($insertResult) {
                $insertCount++;
                $messages[] = "Assigned user {$userID} as {$roleToAssign} HR manager for entity {$entityID}.";
            } else {
                $warnings[] = "Failed to insert HR assignment for user {$userID} / entity {$entityID}.";
                $skippedCount++;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'dryRun' => $dryRun,
        'messages' => $messages,
        'warnings' => $warnings,
        'inserted' => $insertCount,
        'updated' => $updateCount,
        'skipped' => $skippedCount,
        'totalLegacyUsers' => count($legacyRows)
    ]);
} catch (Exception $e) {
    error_log('HR manager migration failed: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage()
    ]);
}

